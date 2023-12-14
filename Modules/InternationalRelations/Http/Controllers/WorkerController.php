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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_view_proposed_labors = auth()->user()->can('internationalrelations.view_proposed_labors');
        if (!($isSuperAdmin || $can_view_proposed_labors)) {
            abort(403, 'Unauthorized action.');
        }

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('interviewStatus', null)->select([
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
     //  dd(  $workers->get());
       
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
                    $item = $professions[$row->transactionSellLine?->service?->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->transactionSellLine?->service?->specialization_id] ?? '';

                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine?->service?->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id] ?? '';
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

                ->addColumn('action', function ($row) {

                    $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';

                    return $html;
                })


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })


                ->rawColumns(['action', 'profession_id', 'interviewStatus', 'specialization_id', 'nationality_id'])
                ->make(true);
        }

        $interview_status = $this->statuses;
        return view('internationalrelations::worker.proposed_laborIndex')->with(compact('interview_status', 'nationalities', 'specializations', 'professions', 'agencys'));
    }


    public function showWorker($id)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_view_worker_info = auth()->user()->can('internationalrelations.view_worker_info');
        if (!($isSuperAdmin || $can_view_worker_info)) {
            abort(403, 'Unauthorized action.');
        }



        $user = IrProposedLabor::select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"))
            ->find($id);

        $dataArray = [];

        return view('internationalrelations::worker.show')->with(compact('user'));
    }
    public function accepted_workers(Request $request)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_view_accepted_workers = auth()->user()->can('internationalrelations.view_accepted_workers');
        if (!($isSuperAdmin || $can_view_accepted_workers)) {
            abort(403, 'Unauthorized action.');
        }


        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('interviewStatus', 'acceptable')->where('arrival_status', '!=', 1)->select([
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
                    $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->transactionSellLine->service->specialization_id] ?? '';

                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id];
                })
                ->editColumn('is_price_offer_sent', function ($row) {
                    $text = $row->is_price_offer_sent;
                    return  $text;
                })
                ->editColumn('is_accepted_by_worker', function ($row) {
                    $text = $row->is_accepted_by_worker;
                    return  $text;
                })

                ->addColumn('action', function ($row) {

                    $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';


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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_view_unaccepted_workers = auth()->user()->can('internationalrelations.view_unaccepted_workers');
        if (!($isSuperAdmin || $can_view_unaccepted_workers)) {
            abort(403, 'Unauthorized action.');
        }


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
                    $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->transactionSellLine->service->specialization_id] ?? '';

                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id];
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
                ->addColumn('action', function ($row) {

                    $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';


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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_view_proposed_labors = auth()->user()->can('internationalrelations.view_proposed_labors');
        if (!($isSuperAdmin || $can_view_proposed_labors)) {
            abort(403, 'Unauthorized action.');
        }
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
                    $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->transactionSellLine->service->specialization_id] ?? '';

                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id];
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

                ->addColumn('action', function ($row) {

                    $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';

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


                if ($request->hasFile('files.' . $row->id)) {
                    $files = $request->file('files.' . $row->id);

                    foreach ($files as $file) {

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
            $validatedData = $request->validate([
                'arrival_dates.*' => 'required|date'
            ]);
   
            $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
            
    
            $business_id = request()->session()->get('user.business_id');
          
    
            if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
                abort(403, 'Unauthorized action.');
            }
    
            $can_passport_stamped = auth()->user()->can('internationalrelations.passport_stamped');
    
            if (!($isSuperAdmin || $can_passport_stamped)) {
                abort(403, 'Unauthorized action.');
            }
        
            error_log('444444444444');
            $selectedRowsData = json_decode($request->input('selectedRowsData'));
           

            foreach ($selectedRowsData as $row) {
 
    
                IrProposedLabor::where('id', $row->id)->update([
                    'is_passport_stamped' => 1,
                ]);
                error_log('66666666666666');
    
                $arrivalDates = $validatedData['arrival_dates'];
                foreach ($arrivalDates as $date) {
                    error_log($date);
                    IrProposedLabor::where('id',$row->id)->update([
                        'arrival_date' => $date
                    ]);
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_fingerprinting = auth()->user()->can('internationalrelations.fingerprinting');
        if (!($isSuperAdmin || $can_fingerprinting)) {
            abort(403, 'Unauthorized action.');
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_medical_examination = auth()->user()->can('internationalrelations.medical_examination');
        if (!($isSuperAdmin || $can_medical_examination)) {
            abort(403, 'Unauthorized action.');
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_accepted_by_worker = auth()->user()->can('internationalrelations.accepted_by_worker');
        if (!($isSuperAdmin || $can_accepted_by_worker)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));
            $currentDate = now();


            foreach ($selectedRowsData as $row) {


                IrProposedLabor::where('id', $row->id)->update([
                    'is_accepted_by_worker' => 1
                ]);


                if ($request->hasFile('files.' . $row->id)) {
                    $files = $request->file('files.' . $row->id);

                    foreach ($files as $file) {

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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_store_visa_worker = auth()->user()->can('internationalrelations.store_visa_worker');
        if (!($isSuperAdmin || $can_store_visa_worker)) {
            abort(403, 'Unauthorized action.');
        }
        try {
            foreach ($request->worker_id as $workerId) {
                if ($workerId !== null) {
                    IrProposedLabor::where('id', $workerId)->update(['visa_id' => $request->visaId]);
                }
            }


            $output = [
                'success' => true,
                'msg' => __('lang_v1.accepted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        $visa_id = $request->visaId;
        return redirect()->route('viewVisaWorkers', ['id' => $visa_id])->withErrors([$output['msg']]);
    }
    public function storeVisaForWorkers(Request $request)
    {
        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));
            $currentDate = now();

            foreach ($selectedRowsData as $row) {

                IrProposedLabor::where('id', $row->id)->update([
                    'has_single_visa' => 1,

                ]);


                if ($request->hasFile('files.' . $row->id)) {
                    $files = $request->file('files.' . $row->id);

                    foreach ($files as $file) {

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
    public function createProposed_labor($delegation_id, $agency_id, $transaction_sell_line_id)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_store_proposed_labor = auth()->user()->can('internationalrelations.store_proposed_labor');
        if (!($isSuperAdmin || $can_store_proposed_labor)) {
            abort(403, 'Unauthorized action.');
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
                'delegation_id'
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
                'current_address', 'transaction_sell_line_id', 'agency_id',
                'profile_picture', 'delegation_id', 'passport_number'
            ]);

            $passport_number = IrProposedLabor::where('passport_number', $request->input('passport_number'))->first();
            if ($passport_number) {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.the_passport_number_already_exists'),
                ];
                return redirect()->route('createProposed_labor', [
                    'delegation_id' => $request->input('delegation_id'),
                    'agency_id' => $request->input('agency_id'),
                    'transaction_sell_line_id' => $request->input('transaction_sell_line_id'),
                ])->with('status', $output);
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_change_worker_status = auth()->user()->can('internationalrelations.change_worker_status');
        if (!($isSuperAdmin || $can_change_worker_status)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['status', 'request_id', 'note']);
            $worker = IrProposedLabor::where('id', $input['request_id'])->first();
            $worker->interviewStatus = $input['status'];
            $worker->interviewNotes = $input['note'] ?? null;
            $worker->updated_by = auth()->user()->id;

            $worker->save();
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


    public function importWorkers($delegation_id,$agency_id,$transaction_sell_line_id)
    {
        return view('internationalrelations::worker.import')->with(compact('delegation_id','agency_id','transaction_sell_line_id'));
    }

    public function postImportWorkers(Request $request)
    {
        $delegation_id=$request->input('delegation_id');
        $agency_id=$request->input('agency_id');
        $transaction_sell_line_id=$request->input('transaction_sell_line_id');
      //  dd(  $delegation_id);
        try {

            if ($request->hasFile('workers_csv')) {
               
                $file = $request->file('workers_csv');
              
                $parsed_array = Excel::toArray([], $file);
               // dd(  $parsed_array );
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
                   // $worker_array['age'] = $value[3];
                    
                    if (!empty($value[3])) 
                    {
                        $worker_array['age'] = $value[3];
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.age_required') .$row_no;
                        break;
                    }
                  //  $worker_array['gender'] = $value[4];

                    if (!empty($value[4])) 
                    {
                        $worker_array['gender'] = $value[4];
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.gender_required') .$row_no;
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

                    $passport_number = IrProposedLabor::where('passport_number', $worker_array['passport_number'])
                    ->first();
                    if ($passport_number) {
                        $is_valid = false;
                        $error_msg = __('lang_v1.the_passport_number_already_exists').$row_no;
                        break;
                       
                       
                    }

                    $worker_array['agency_id'] = $agency_id;
                   
                    if ($worker_array['agency_id'] !== null) {
                                        
                        $business = Contact::find($worker_array['agency_id']);
                        if (!$business) {
                        
                            $is_valid = false;
                            $error_msg = __('essentials::lang.contact_not_found').$row_no;
                            break;
                        }
                    }
                    else
                    {
                        $worker_array['agency_id']=null;
                    } 


                    $worker_array['transaction_sell_line_id'] =   $transaction_sell_line_id;
                    if ($worker_array['transaction_sell_line_id'] !== null) {
                                        
                        $business = TransactionSellLine::find($worker_array['transaction_sell_line_id']);
                        if (!$business) {
                        
                            $is_valid = false;
                            $error_msg = __('essentials::lang.contact_not_found').$row_no;
                            break;
                        }
                    }
                    else
                    {
                        $is_valid = false;
                            $error_msg = __('essentials::lang.contact_not_found').$row_no;
                            break;
                    } 
                    $formated_data[] = $worker_array;
                 
                  //  dd(   $formated_data );
                  
                  

                   
                }

                   
                if (!$is_valid) 
                {
                    throw new \Exception($error_msg);
                }

                if (! empty($formated_data)) 
                {
                 
                    foreach ($formated_data as $worker_data) {
                       
                        $worker = IrProposedLabor::create($worker_data);
                        IrDelegation::where('id', $request->input('delegation_id'))->increment('proposed_labors_quantity', 1);

                        if (in_array($worker_data['passport_number'], $passport_numbers)) {
                            throw new \Exception(__('lang_v1.the_passport_number_already_exists',
                            
                            ['passport_number' => $worker_data['passport_number']]));
                        }
                    
                      $passport_numbers[] = $worker_data['passport_number'];             

                     
                      
                    }
                }
                
      
               
                $output = ['success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                ];

                DB::commit();


            }
            else{
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
                'msg' => $e->getMessage(),
            ];
           
            return redirect()->route('importWorkers' ,['delegation_id'=>$delegation_id ,'agency_id'=>$agency_id,'transaction_sell_line_id'=>$transaction_sell_line_id])->with('notification', $output);
        }
      
       //return $output;
       return redirect()->route('proposed_laborIndex')->with('notification', 'success insert');
    }
}
