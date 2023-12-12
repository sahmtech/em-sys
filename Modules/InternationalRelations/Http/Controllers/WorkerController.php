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
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsCountry;
use DB;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\InternationalRelations\Entities\IrDelegation;
use Modules\InternationalRelations\Entities\IrProposedLabor;

class WorkerController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;
    protected $statuses;
    protected $notificationUtil;


    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
         ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
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
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
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
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->select([
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


                ->rawColumns(['action', 'profession_id','interviewStatus', 'specialization_id', 'nationality_id'])
                ->make(true);
        }

        $interview_status=$this->statuses;
        return view('internationalrelations::worker.proposed_laborIndex')->with(compact('interview_status','nationalities', 'specializations', 'professions', 'agencys'));
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
     
        $dataArray=[];
       
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
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('interviewStatus','acceptable')->select([
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
            'is_price_offer_sent',
            'is_accepted_by_worker',
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
                ->editColumn('is_price_offer_sent', function ($row) {
                    $text = $row->is_price_offer_sent == 1
                        ? __('lang_v1.send')
                        : __('lang_v1.not_sent_yet');
                
                    $color = $row->is_price_offer_sent == 1
                        ? 'green'
                        : 'red';
                
                    return '<span style="color: ' . $color . ';">' . $text . '</span>';
                })
                ->editColumn('is_accepted_by_worker', function ($row) {
                    $text = $row->is_accepted_by_worker == 1
                        ? __('lang_v1.accepted')
                        : __('lang_v1.not_yet');
                
                    $color = $row->is_accepted_by_worker == 1
                        ? 'green'
                        : 'red';
                
                    return '<span style="color: ' . $color . ';">' . $text . '</span>';
                })
                
                ->addColumn('action', function ($row) {

                    $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';


                    return $html;
                })


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })


                ->rawColumns(['action','is_price_offer_sent','is_accepted_by_worker', 'profession_id', 'specialization_id', 'nationality_id'])
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
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->whereIn('interviewStatus',['unacceptable','not_attend'])->select([
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


                ->rawColumns(['action', 'profession_id','interviewStatus', 'specialization_id', 'nationality_id'])
                ->make(true);
        }


        return view('internationalrelations::worker.unaccepted_workers')->with(compact('nationalities', 'specializations', 'professions', 'agencys'));
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function send_offer_price(Request $request)
    {  
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_send_offer_price = auth()->user()->can('internationalrelations.send_offer_price');
        if (!($isSuperAdmin || $can_send_offer_price)) {
            abort(403, 'Unauthorized action.');
        }
     
        try {
            $selectedRows = $request->input('selectedRows');
            $currentDate = Carbon::now();
           

            IrProposedLabor::whereIn('id', $selectedRows)
            ->where('is_price_offer_sent', 0)
            ->update([
                'is_price_offer_sent' => 1,
                'date_of_offer' => $currentDate,
            ]);
    
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_passport_stamped = auth()->user()->can('internationalrelations.passport_stamped');
        if (!($isSuperAdmin || $can_passport_stamped)) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $selectedRows = $request->input('selectedRows');
        
            IrProposedLabor::whereIn('id', $selectedRows)->update(['is_passport_stamped' => 1]);
        
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
            $selectedRows = $request->input('selectedRows');
        
            IrProposedLabor::whereIn('id', $selectedRows)->update(['is_accepted_by_worker' => 1]);
        
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
                IrProposedLabor::where('id', $workerId)->update(['visa_id' => $request->visaId ]);
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
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
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
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function storeProposed_labor(Request $request)
    {
       
        try {
            $input = $request->only([
                'first_name', 'mid_name', 'last_name',
                'email', 'dob', 'gender',
                'marital_status', 'blood_group', 'age',
                'contact_number', 'alt_number', 'family_number', 'permanent_address',
                'current_address', 'transaction_sell_line_id', 'agency_id',
                'profile_picture', 'delegation_id','passport_number'
            ]);


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
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
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
            $input = $request->only(['status', 'request_id']);
            $worker = IrProposedLabor::where('id',$input['request_id'])->first();
            $worker->interviewStatus = $input['status'];
            $worker->updated_by =auth()->user()->id;
    
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

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
