<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\Contact;
use App\User;
use Excel;
use App\Category;
use App\Business;
use DateTime;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsCountry;
use DB;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\InternationalRelations\Entities\IrDelegation;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Modules\InternationalRelations\Entities\IrWorkersDocument;
use App\TransactionSellLine;
use Modules\InternationalRelations\Entities\IrVisaCard;
use Modules\Sales\Entities\SalesUnSupportedWorker;

class WorkerController extends Controller
{

    protected $moduleUtil;
    protected $statuses;


    public function __construct(ModuleUtil $moduleUtil)
    {

        $this->moduleUtil = $moduleUtil;

        $this->statuses = [
            'acceptable' => [
                'name' => __('internationalrelations::lang.acceptable'),
                'class' => 'bg-green',
            ],
            'unacceptable' => [
                'name' => __('internationalrelations::lang.unacceptable'),
                'class' => 'bg-red',
            ],
            'not_attend' => [
                'name' => __('internationalrelations::lang.not_attend'),
                'class' => 'bg-yellow',
            ],
        ];
    }

    public function proposed_laborIndex(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_worker_profile = auth()->user()->can('internationalrelations.view_worker_profile');

        if (!($is_admin)) {
        }

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency', 'unSupportedworker_order')->where('interviewStatus', null)->select([
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            'age',
            'gender',
            'email',
            'profile_image',
            'dob',
            'marital_status',
            'blood_group',
            'contact_number',
            'permanent_address',
            'current_address',
            'interviewStatus',
            'agency_id', 'transaction_sell_line_id', 'unSupportedworker_order_id'
        ]);



        if (!empty($request->input('specialization'))) {
            $workers->where(function ($query) use ($request) {
                $query->whereHas('transactionSellLine.service', function ($subQuery) use ($request) {
                    $subQuery->where('specialization_id', $request->input('specialization'));
                })
                    ->orWhereHas('unSupportedworker_order', function ($subQuery) use ($request) {
                        $subQuery->where('specialization_id', $request->input('specialization'));
                    });
            });
        }
        if (!empty($request->input('profession'))) {
            $workers->where(function ($query) use ($request) {
                $query->whereHas('transactionSellLine.service', function ($subQuery) use ($request) {
                    $subQuery->where('profession_id', $request->input('profession'));
                })
                    ->orWhereHas('unSupportedworker_order', function ($subQuery) use ($request) {
                        $subQuery->where('profession_id', $request->input('profession'));
                    });
            });
        }


        if (!empty($request->input('agency'))) {
            $workers->where('agency_id', $request->input('agency'));
        }


        if (request()->ajax()) {

            return Datatables::of($workers)


                ->addColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->transactionSellLine?->service?->profession_id] ?? $professions[$row->unSupportedworker_order?->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->transactionSellLine?->service?->specialization_id] ?? $specializations[$row->unSupportedworker_order?->specialization_id] ?? '';

                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine?->service?->nationality_id] ?? $nationalities[$row->unSupportedworker_order?->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id] ?? '';
                })




                ->addColumn('action', function ($row) use ($is_admin, $can_view_worker_profile) {
                    $html = '';
                    if ($is_admin || $can_view_worker_profile) {
                        $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                    }
                    return $html;
                })


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })


                ->rawColumns(['action', 'profession_id', 'specialization_id', 'nationality_id'])
                ->make(true);
        }

        $interview_status = $this->statuses;
        return view('internationalrelations::worker.proposed_laborIndex')->with(compact('interview_status', 'nationalities', 'specializations', 'professions', 'agencys'));
    }


    public function showWorker($id)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_worker_info = auth()->user()->can('internationalrelations.view_worker_info');
        if (!($is_admin || $can_view_worker_info)) {
        }



        $user = IrProposedLabor::select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"))
            ->find($id);

        $dataArray = [];

        return view('internationalrelations::worker.show')->with(compact('user'));
    }
    public function accepted_workers(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_accepted_workers = auth()->user()->can('internationalrelations.view_accepted_workers');
        if (!($is_admin || $can_view_accepted_workers)) {
        }
        $can_view_worker_profile = auth()->user()->can('internationalrelations.view_worker_profile');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')
            ->where('interviewStatus', 'acceptable')->where('arrival_status', '!=', 1)->select([
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
                'age', 'gender', 'email', 'profile_image', 'dob', 'marital_status', 'blood_group',
                'contact_number', 'permanent_address', 'current_address', 'is_price_offer_sent',
                'is_accepted_by_worker', 'agency_id', 'transaction_sell_line_id'
            ]);

        if (!empty($request->input('specialization'))) {
            $workers->whereHas('transactionSellLine.service', function ($query) use ($request) {
                $query->where('specialization_id', $request->input('specialization'));
            });
        }

        if (!empty($request->input('profession'))) {
            $workers->whereHas('transactionSellLine.service', function ($query) use ($request) {
                $query->where('profession_id', $request->input('profession'));
            });
        }

        if (!empty($request->input('agency'))) {
            $workers->where('agency_id', $request->input('agency'));
        }


        if (request()->ajax()) {

            return Datatables::of($workers)


                ->addColumn('profession_id', function ($row) use ($professions) {

                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';
                    }

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $specializations[$row->transactionSellLine->service->specialization_id] ?? '';
                    }
                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';
                    }
                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {
                    if ($row->agency_id) {
                        return $agencys[$row->agency_id];
                    } else {
                        return '';
                    }
                })
                ->editColumn('is_price_offer_sent', function ($row) {
                    $text = $row->is_price_offer_sent;
                    return  $text;
                })
                ->editColumn('is_accepted_by_worker', function ($row) {
                    $text = $row->is_accepted_by_worker;
                    return  $text;
                })

                ->addColumn('action', function ($row) use ($is_admin, $can_view_worker_profile) {
                    $html = '';
                    if ($is_admin || $can_view_worker_profile) {
                        $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                    }
                    return $html;
                })


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })


                ->rawColumns(['action', 'is_price_offer_sent', 'is_accepted_by_worker', 'profession_id', 'specialization_id', 'nationality_id'])
                ->make(true);
        }


        return view('internationalrelations::worker.accepted_workers')->with(compact('nationalities', 'specializations', 'professions', 'agencys'));
    }
    public function unaccepted_workers(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_unaccepted_workers = auth()->user()->can('internationalrelations.view_unaccepted_workers');
        if (!($is_admin || $can_view_unaccepted_workers)) {
        }

        $can_view_worker_profile = auth()->user()->can('internationalrelations.view_worker_profile');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->whereIn('interviewStatus', ['unacceptable', 'not_attend'])->select([
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            'age',
            'gender',
            'email',
            'profile_image',
            'dob',
            'marital_status',
            'blood_group',
            'contact_number',
            'permanent_address',
            'current_address',
            'interviewStatus',
            'agency_id', 'transaction_sell_line_id'
        ]);

        if (!empty($request->input('specialization'))) {

            $workers->whereHas('transactionSellLine.service', function ($query) use ($request) {
                $query->where('specialization_id', $request->input('specialization'));
            });
        }

        if (!empty($request->input('profession'))) {
            $workers->whereHas('transactionSellLine.service', function ($query) use ($request) {
                $query->where('profession_id', $request->input('profession'));
            });
        }

        if (!empty($request->input('agency'))) {
            $workers->where('agency_id', $request->input('agency'));
        }


        if (request()->ajax()) {

            return Datatables::of($workers)


                ->addColumn('profession_id', function ($row) use ($professions) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';
                    }
                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $specializations[$row->transactionSellLine->service->specialization_id] ?? '';
                    }
                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';
                    }
                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {
                    if ($row->agency_id) {
                        return $agencys[$row->agency_id];
                    } else {
                        return '';
                    }
                })
                ->editColumn('interviewStatus', function ($row) {
                    $status = __('internationalrelations::lang.' . $row->interviewStatus);

                    switch ($row->interviewStatus) {
                        case 'not_attend':
                            $status = '<span style="color: orange;">' . $status . '</span>';
                            break;
                        case 'unacceptable':
                            $status = '<span style="color: red;">' . $status . '</span>';
                            break;
                    }

                    return $status;
                })
                ->addColumn('action', function ($row) use ($is_admin, $can_view_worker_profile) {
                    $html = '';
                    if ($is_admin || $can_view_worker_profile) {
                        $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                    }
                    return $html;
                })


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })


                ->rawColumns(['action', 'profession_id', 'interviewStatus', 'specialization_id', 'nationality_id'])
                ->make(true);
        }


        return view('internationalrelations::worker.unaccepted_workers')->with(compact('nationalities', 'specializations', 'professions', 'agencys'));
    }
    public function workers_under_trialPeriod(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_proposed_labors = auth()->user()->can('internationalrelations.view_proposed_labors');
        if (!($is_admin || $can_view_proposed_labors)) {
        }
        $can_view_worker_profile = auth()->user()->can('internationalrelations.view_worker_profile');
        $currentDate = now();
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('interviewStatus', 'acceptable')
            ->where('arrival_status', 1)
            ->whereDate('arrival_date', '>', $currentDate->subMonths(3))->select([
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
                'age',
                'gender',
                'email',
                'profile_image',
                'dob',
                'marital_status',
                'blood_group',
                'contact_number',
                'permanent_address',
                'current_address',
                'interviewStatus',
                'agency_id', 'transaction_sell_line_id'
            ]);

        if (!empty($request->input('specialization'))) {
            $workers->whereHas('transactionSellLine.service', function ($query) use ($request) {
                $query->where('specialization_id', $request->input('specialization'));
            });
        }

        if (!empty($request->input('profession'))) {
            $workers->whereHas('transactionSellLine.service', function ($query) use ($request) {
                $query->where('profession_id', $request->input('profession'));
            });
        }

        if (!empty($request->input('agency'))) {
            $workers->where('agency_id', $request->input('agency'));
        }


        if (request()->ajax()) {

            return Datatables::of($workers)


                ->addColumn('profession_id', function ($row) use ($professions) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';
                    }
                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $specializations[$row->transactionSellLine->service->specialization_id] ?? '';
                    }
                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = '';
                    if ($row->transactionSellLine) {
                        $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';
                    }
                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {
                    if ($row->agency_id) {
                        return $agencys[$row->agency_id];
                    } else {
                        return '';
                    }
                })
                ->addColumn('interviewStatus', function ($row) {
                    if ($row->interviewStatus === null) {
                        $html = '<button class="btn btn-xs btn-success change_status_modal" data-employee-id="' . $row->id . '"><i class="glyphicon glyphicon-eye"></i> ' . __('internationalrelations::lang.change_interview_status') . '</button>';
                    } else {
                        $html = __('internationalrelations::lang.' . $row->interviewStatus);

                        switch ($row->interviewStatus) {
                            case 'not_attend':
                                $html = '<span style="color: orange;">' . $html . '</span>';
                                break;
                            case 'unacceptable':
                                $html = '<span style="color: red;">' . $html . '</span>';
                                break;
                            case 'acceptable':
                                $html = '<span style="color: green;">' . $html . '</span>';
                                break;
                        }
                    }

                    return $html;
                })

                ->addColumn('action', function ($row)  use ($is_admin, $can_view_worker_profile) {
                    $html = '';
                    if ($is_admin || $can_view_worker_profile) {
                        $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                    }
                    return $html;
                })


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })


                ->rawColumns(['action', 'profession_id', 'interviewStatus', 'specialization_id', 'nationality_id'])
                ->make(true);
        }

        $interview_status = $this->statuses;
        return view('internationalrelations::worker.workers_under_trialPeriod')->with(compact('interview_status', 'nationalities', 'specializations', 'professions', 'agencys'));
    }

    public function send_offer_price(Request $request)
    {
        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));
            $currentDate = now();

            foreach ($selectedRowsData as $row) {

                IrProposedLabor::where('id', $row->id)->update([
                    'is_price_offer_sent' => 1,
                    'date_of_offer' => $currentDate,
                ]);


                if ($request->hasFile('file')) {
                    $file = $request->file('file');



                    $path = $file->store('/workers_documents');

                    $uploadedFile = new IrWorkersDocument();
                    $uploadedFile->worker_id = $row->id;
                    $uploadedFile->type = 'offer_price';
                    $uploadedFile->uploaded_by = auth()->user()->id;
                    $uploadedFile->uploaded_at = $currentDate;
                    $uploadedFile->attachment = $path;

                    $uploadedFile->save();
                }
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.send_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
    public function passport_stamped(Request $request)
    {


        try {


            $business_id = request()->session()->get('user.business_id');




            $can_passport_stamped = auth()->user()->can('internationalrelations.passport_stamped');
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            if (!($is_admin || $can_passport_stamped)) {
            }

            $selectedRowsData = json_decode($request->input('selectedRowsData2'));
            $currentDate = now();

            foreach ($selectedRowsData as $row) {


                IrProposedLabor::where('id', $row->id)->update([
                    'has_single_visa' => 1,
                    'is_passport_stamped' => 1,
                    'arrival_date' => $request->arrival_date
                ]);


                if ($request->hasFile('file')) {

                    $file = $request->file('file');
                    $path = $file->store('/workers_documents');
                    $uploadedFile = new IrWorkersDocument();
                    $uploadedFile->worker_id = $row->id;
                    $uploadedFile->type = 'visa';
                    $uploadedFile->uploaded_by = auth()->user()->id;
                    $uploadedFile->uploaded_at = $currentDate;
                    $uploadedFile->attachment = $path;

                    $uploadedFile->save();
                }
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.send_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function fingerprinting(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_fingerprinting = auth()->user()->can('internationalrelations.fingerprinting');
        if (!($is_admin || $can_fingerprinting)) {
        }
        try {
            $selectedRows = $request->input('selectedRows');

            IrProposedLabor::whereIn('id', $selectedRows)->update(['fingerprinting' => 1]);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.send_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
    public function medical_examination(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_medical_examination = auth()->user()->can('internationalrelations.medical_examination');
        if (!($is_admin || $can_medical_examination)) {
        }
        try {
            $selectedRows = $request->input('selectedRows');

            IrProposedLabor::whereIn('id', $selectedRows)->update(['medical_examination' => 1]);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.send_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function accepted_by_worker(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_accepted_by_worker = auth()->user()->can('internationalrelations.accepted_by_worker');
        if (!($is_admin || $can_accepted_by_worker)) {
        }

        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));
            $currentDate = now();


            foreach ($selectedRowsData as $row) {


                IrProposedLabor::where('id', $row->id)->update([
                    'is_accepted_by_worker' => 1
                ]);


                if ($request->hasFile('file')) {
                    $file = $request->file('file');



                    $path = $file->store('/workers_documents');

                    $uploadedFile = new IrWorkersDocument();
                    $uploadedFile->worker_id = $row->id;
                    $uploadedFile->type = 'acceptance_offer';
                    $uploadedFile->uploaded_by = auth()->user()->id;
                    $uploadedFile->uploaded_at = $currentDate;
                    $uploadedFile->attachment = $path;

                    $uploadedFile->save();
                }
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.send_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function storeVisaWorker(Request $request)
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_store_visa_worker = auth()->user()->can('internationalrelations.store_visa_worker');
        $visa_id = $request->visaId;
        // if (!($is_admin || $can_store_visa_worker)) {
        // }
        try {

            error_log("888888888888888");
            $selectedWorkersCount = count($request->worker_id);
            error_log($selectedWorkersCount);

            $visaCard = IrVisaCard::where('id', $visa_id)
                ->with('operationOrder.salesContract.transaction.sell_lines', 'delegation', 'unSupported_operation')
                ->first();
            error_log($visaCard);
            if ($visaCard->operationOrder) {
                error_log("11111111111111");
                $orderQuantity = $visaCard->operationOrder->orderQuantity;
            }
            if ($visaCard->unSupported_operation) {
                error_log("2222222222");
                $orderQuantity = $visaCard->unSupported_operation->orderQuantity;
                error_log($orderQuantity);
            }

            $proposed_workers_number = $visaCard->proposed_workers_number;
            $delegation_agency_targeted_count = null;


            if ($selectedWorkersCount > $orderQuantity) {
                $output = [
                    'success' => false,
                    'msg' => __('internationalrelations::lang.number_of_added_workers_more_than_target'),
                ];
            } else {
                $exceededAgencies = [];

                error_log("33333333333");
                $groupedWorkers = collect($request->worker_id)->groupBy(function ($workerId) {
                    return IrProposedLabor::where('id', $workerId)->first()->agency_id;
                });

                foreach ($groupedWorkers as $agencyId => $workers) {
                    error_log($visaCard->unSupporteddelegation);
                    if ($visaCard->delegation) {
                        if ($visaCard->delegation->agency()->whereIn('id', [$agencyId])->exists()) {
                            error_log("4444444444444");

                            $delegation_agency_targeted_count = $visaCard->delegation->where('agency_id', [$agencyId])->first()->targeted_quantity;
                            error_log($delegation_agency_targeted_count);

                            $workersCount = $workers->count();

                            if ($workersCount > $delegation_agency_targeted_count) {
                                $exceededAgencies[] = $agencyId;
                            }
                        }
                    }
                    if ($visaCard->unSupporteddelegation) {
                        if ($visaCard->unSupporteddelegation->agency()->whereIn('id', [$agencyId])->exists()) {
                            error_log("4444444444444");

                            $delegation_agency_targeted_count = $visaCard->unSupporteddelegation->where('agency_id', [$agencyId])->first()->targeted_quantity;
                            error_log($delegation_agency_targeted_count);

                            $workersCount = $workers->count();

                            if ($workersCount > $delegation_agency_targeted_count) {
                                $exceededAgencies[] = $agencyId;
                            }
                        }
                    }
                }
            }

            error_log("4444444444444");

            if (!empty($exceededAgencies)) {
                $output = [
                    'success' => false,
                    'msg' => __('internationalrelations::lang.workers_exceed_target_number'),
                ];
            } else if ($proposed_workers_number + $selectedWorkersCount <=  $orderQuantity) {
                error_log("5555555555555");
                foreach ($request->worker_id as $workerId) {
                    if ($workerId !== null) {
                        IrProposedLabor::where('id', $workerId)
                            ->update([
                                'visa_id' => $request->visaId,

                            ]);


                        IrVisaCard::where('id', $visa_id)->increment('proposed_workers_number');
                    }
                }

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.added_success'),

                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => __('internationalrelations::lang.can_not_add_more_workers'),

                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log($e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }



    public function cancelVisaWorker(Request $request)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_cancel_proposal_worker = auth()->user()->can('internationalrelations.cancel_proposal_worker');
        $visa_id = $request->visaId;
        if (!($is_admin || $can_cancel_proposal_worker)) {
        }

        try {
            $selectedRows = $request->input('selectedRows');

            $incompleteWorkers = IrProposedLabor::whereIn('id', $selectedRows)
                ->where(function ($query) {

                    $query->Where('arrival_status', '=', 1);
                })
                ->get();


            if ($incompleteWorkers->isNotEmpty()) {

                $hasArrivalStatusZero = $incompleteWorkers->contains('arrival_status', 1);

                if ($hasArrivalStatusZero) {
                    $output = [
                        'success' => false,
                        'msg' => __('internationalrelations::lang.cancel_visaworker_arrival_status_zero'),
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __('internationalrelations::lang.cancel_visaworker_incomplete'),
                    ];
                }
            } else {

                IrProposedLabor::whereIn('id', $selectedRows)
                    ->update([
                        'visa_id' => null,
                        'medical_examination' => 0,
                        'fingerprinting' => 0,
                        'is_passport_stamped' => 0,
                        'arrival_date' => null
                    ]);


                foreach ($selectedRows as $workerId) {






                    IrVisaCard::where('id', $visa_id)->decrement('proposed_workers_number');
                }

                $output = [
                    'success' => true,
                    'msg' => __('internationalrelations::lang.worker_canceled'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }


    public function createProposed_labor($delegation_id, $agency_id, $transaction_sell_line_id)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_store_proposed_labor = auth()->user()->can('internationalrelations.store_proposed_labor');

        if (!($is_admin || $can_store_proposed_labor)) {
        }


        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $contacts = Contact::where('type', 'customer')->pluck('supplier_business_name', 'id');

        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];
        $irDelegations = IrDelegation::query()
            ->where(function ($query) {
                $query->whereNotNull('operation_order_id')
                    ->orWhereNotNull('unSupported_operation_id');
            })
            ->where('id', $delegation_id)
            ->with(['agency', 'transactionSellLine.service', 'unSupported_operation.unSupported_worker'])
            ->first();



        $worker_gender = $irDelegations->transactionSellLine->service->gender;

        $resident_doc = null;
        $user = null;
        return view('internationalrelations::worker.proposed_laborCreate')
            ->with(compact(
                'nationalities',
                'blood_types',
                'contacts',
                "specializations",
                'professions',
                'resident_doc',
                'user',
                'agency_id',
                'transaction_sell_line_id',
                'delegation_id',
                'worker_gender'
            ));
    }

    public function createProposed_labor_unSupported($delegation_id, $agency_id, $unSupportedworker_order_id)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_store_proposed_labor = auth()->user()->can('internationalrelations.store_proposed_labor');

        if (!($is_admin || $can_store_proposed_labor)) {
        }


        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $contacts = Contact::where('type', 'customer')->pluck('supplier_business_name', 'id');

        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];




        $resident_doc = null;
        $user = null;
        return view('internationalrelations::worker.un_supported_proposed_laborCreate')
            ->with(compact(
                'nationalities',
                'blood_types',
                'contacts',
                "specializations",
                'professions',
                'resident_doc',
                'user',
                'agency_id',
                'unSupportedworker_order_id',
                'delegation_id'
            ));
    }
    public function create_worker_without_project()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_store_proposed_labor = auth()->user()->can('internationalrelations.store_proposed_labor');
        if (!($is_admin || $can_store_proposed_labor)) {
        }

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $contacts = Contact::where('type', 'customer')->pluck('supplier_business_name', 'id');

        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];

        $resident_doc = null;
        $user = null;
        return view('internationalrelations::worker.create_worker_without_project')
            ->with(compact(
                'nationalities',
                'blood_types',
                'contacts',
                "specializations",
                'professions',
                'resident_doc',
                'user',

            ));
    }
    public function storeProposed_labor(Request $request)
    {

        try {

            $input = $request->only([
                'first_name', 'mid_name', 'last_name',
                'email', 'dob', 'gender',
                'marital_status', 'blood_group', 'age',
                'contact_number', 'alt_number', 'family_number', 'permanent_address',
                'current_address', 'agency_id',
                'profile_picture', 'delegation_id', 'passport_number'
            ]);
            if ($request->input('transaction_sell_line_id')) {
                $input['transaction_sell_line_id'] = $request->input('transaction_sell_line_id');
            }
            if ($request->input('unSupportedworker_order_id')) {


                $input['unSupportedworker_order_id'] = $request->input('unSupportedworker_order_id');
            }

            $passport_number = IrProposedLabor::where('passport_number', $request->input('passport_number'))->first();


            if ($passport_number) {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.the_passport_number_already_exists'),
                ];
                if ($request->input('transaction_sell_line_id')) {
                    return redirect()->route('createProposed_labor', [
                        'delegation_id' => $request->input('delegation_id'),
                        'agency_id' => $request->input('agency_id'),
                        'transaction_sell_line_id' => $request->input('transaction_sell_line_id'),
                    ])->with('status', $output);
                }
                if ($request->input('unSupportedworker_order_id')) {
                    return redirect()->route('createProposed_labor', [
                        'delegation_id' => $request->input('delegation_id'),
                        'agency_id' => $request->input('agency_id'),
                        'unSupportedworker_order_id' => $request->input('unSupportedworker_order_id'),
                    ])->with('status', $output);
                }
            }
            if ($request->hasFile('profile_picture')) {
                $input['profile_image'] = $request->file('profile_picture')->store('/proposedLaborPicture');
            }


            IrProposedLabor::create($input);


            IrDelegation::where('id', $request->input('delegation_id'))->increment('proposed_labors_quantity', 1);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('delegations')->with('status', $output);
    }
    public function storeWorkerWithoutProject(Request $request)
    {

        try {
            $input = $request->only([
                'first_name', 'mid_name', 'last_name',
                'email', 'dob', 'gender',
                'marital_status', 'blood_group', 'age',
                'contact_number', 'alt_number', 'family_number', 'permanent_address',
                'current_address',  'profile_picture', 'passport_number'
            ]);

            $passport_number = IrProposedLabor::where('passport_number', $request->input('passport_number'))->first();
            if ($passport_number) {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.the_passport_number_already_exists'),
                ];
                return redirect()->route('create_worker_without_project')->with('status', $output);
            }
            if ($request->hasFile('profile_picture')) {
                $input['profile_image'] = $request->file('profile_picture')->store('/proposedLaborPicture');
            }

            IrProposedLabor::create($input);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log(print_r('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage()));
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('delegations')->with('status', $output);
    }
    public function changeStatus(Request $request)
    {
        $user = auth()->user();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $canChangeWorkerStatus = $user->can('internationalrelations.change_worker_status');
        if (!($is_admin || $canChangeWorkerStatus)) {
        }

        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));

            foreach ($selectedRowsData as $row) {
                $worker = IrProposedLabor::find($row->id);

                if (!$worker) {

                    continue;
                }

                $worker->interviewStatus = $request->status;
                $worker->interviewNotes = $request->note ?? null;
                $worker->updated_by = $user->id;
                $worker->save();
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }


    public function importWorkers($delegation_id, $agency_id, $transaction_sell_line_id)
    {
        return view('internationalrelations::worker.import')->with(compact('delegation_id', 'agency_id', 'transaction_sell_line_id'));
    }
    public function importWorkers_unSupported($delegation_id, $agency_id, $unSupportedworker_order_id)
    {
        return view('internationalrelations::worker.un_supported_import')->with(compact('delegation_id', 'agency_id', 'unSupportedworker_order_id'));
    }
    public function postImportWorkers(Request $request)
    {
        $delegation_id = $request->input('delegation_id');
        $agency_id = $request->input('agency_id');
        $transaction_sell_line_id = $request->input('transaction_sell_line_id');
        $unSupportedworker_order_id = $request->input('unSupportedworker_order_id');

        try {

            if ($request->hasFile('workers_csv')) {

                $file = $request->file('workers_csv');

                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1);
                $passport_numbers = [];
                $formated_data = [];
                $is_valid = true;
                $error_msg = '';



                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    $row_no = $key + 1;
                    $worker_array = [];

                    if (!empty($value[0])) {
                        $worker_array['first_name'] = $value[0];
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.first_name_required') . $row_no;
                        break;
                    }

                    $worker_array['mid_name'] = $value[1];

                    if (!empty($value[2])) {
                        $worker_array['last_name'] = $value[2];
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.last_name_required') . $row_no;
                        break;
                    }


                    if (!empty($value[3])) {
                        $worker_array['age'] = $value[3];
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.age_required') . $row_no;
                        break;
                    }


                    if (!empty($value[4])) {
                        $worker_array['gender'] = $value[4];
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.gender_required') . $row_no;
                        break;
                    }
                    $worker_array['email'] = $value[5];

                    if (!empty($value[6])) {
                        if (is_numeric($value[6])) {

                            $excelDateValue = (float)$value[6];
                            $unixTimestamp = ($excelDateValue - 25569) * 86400;
                            $date = date('Y-m-d', $unixTimestamp);
                            $worker_array['dob'] = $date;
                        } else {

                            $date = DateTime::createFromFormat('d/m/Y', $value[6]);
                            if ($date) {
                                $dob = $date->format('Y-m-d');
                                $worker_array['dob'] = $dob;
                            }
                        }
                    } else {
                        $worker_array['dob'] = null;
                    }

                    $worker_array['marital_status'] = $value[7];
                    $worker_array['blood_group'] = $value[8];

                    if (!empty(trim($value[9]))) {
                        $worker_array['contact_number'] = $value[9];
                    }

                    $worker_array['current_address'] = $value[10];
                    $worker_array['permanent_address'] = $value[11];


                    $worker_array['passport_number'] = $value[12];

                    if (in_array($worker_array['passport_number'], $passport_numbers)) {
                        $is_valid = false;
                        $error_msg = __('lang_v1.the_passport_number_already_exists') . $row_no;
                        break;
                    }


                    $passport_number = IrProposedLabor::where('passport_number', $worker_array['passport_number'])
                        ->first();
                    if ($passport_number) {
                        $is_valid = false;
                        $error_msg = __('lang_v1.the_passport_number_already_exists') . $row_no;
                        break;
                    }
                    $passport_numbers[] = $worker_array['passport_number'];

                    $worker_array['agency_id'] = $agency_id;

                    if ($worker_array['agency_id'] !== null) {

                        $business = Contact::find($worker_array['agency_id']);
                        if (!$business) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.contact_not_found') . $row_no;
                            break;
                        }
                    } else {
                        $worker_array['agency_id'] = null;
                    }

                    if ($transaction_sell_line_id) {
                        $worker_array['transaction_sell_line_id'] =   $transaction_sell_line_id;
                        if ($worker_array['transaction_sell_line_id'] !== null) {

                            $business = TransactionSellLine::find($worker_array['transaction_sell_line_id']);
                            if (!$business) {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.contact_not_found') . $row_no;
                                break;
                            }
                        } else {
                            $is_valid = false;
                            $error_msg = __('essentials::lang.contact_not_found') . $row_no;
                            break;
                        }
                    }

                    if ($unSupportedworker_order_id) {
                        $worker_array['unSupportedworker_order_id'] =   $unSupportedworker_order_id;
                        if ($worker_array['unSupportedworker_order_id'] !== null) {

                            $business = SalesUnSupportedWorker::find($worker_array['unSupportedworker_order_id']);
                            if (!$business) {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.contact_not_found') . $row_no;
                                break;
                            }
                        } else {
                            $is_valid = false;
                            $error_msg = __('essentials::lang.contact_not_found') . $row_no;
                            break;
                        }
                    }
                    $formated_data[] = $worker_array;
                }


                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {

                    foreach ($formated_data as $worker_data) {

                        $worker = IrProposedLabor::create($worker_data);
                        IrDelegation::where('id', $request->input('delegation_id'))->increment('proposed_labors_quantity', 1);
                    }
                }



                $output = [
                    'success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                ];

                DB::commit();
            } else {
                $output = [
                    'success' => 0,
                    'msg' => 'no file',
                ];
            }
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];

            return redirect()->route('importWorkers', ['delegation_id' => $delegation_id, 'agency_id' => $agency_id, 'transaction_sell_line_id' => $transaction_sell_line_id])->with('notification', $output);
        }


        return redirect()->route('proposed_laborIndex')->with('notification', 'success insert');
    }
}
