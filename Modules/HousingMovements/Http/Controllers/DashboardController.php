<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    protected $statuses;
    protected $statuses2;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->statuses = [
            'approved' => [
                'name' => __('followup::lang.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name' => __('followup::lang.rejected'),
                'class' => 'bg-red',
            ],
            'pending' => [
                'name' => __('followup::lang.pending'),
                'class' => 'bg-yellow',
            ],
        ];
        $this->statuses2 = [
            'approved' => [
                'name' => __('followup::lang.approved'),
                'class' => 'bg-green',
            ],

            'pending' => [
                'name' => __('followup::lang.pending'),
                'class' => 'bg-yellow',
            ],
        ];
    }
    
    public function index()
    {
        
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housing_move_dashbord = auth()->user()->can('housingmovements.housing_move_dashbord');
        if (!($is_admin || $can_housing_move_dashbord)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
      
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $EssentailsEmployeeOperation_emplyeeIds = EssentailsEmployeeOperation::whereIn('employee_id', $userIds)->where('operation_type', 'final_visa')->pluck('employee_id');
        $final_exit_count = User::whereIn('id', $userIds)->whereIn('id', $EssentailsEmployeeOperation_emplyeeIds)->where('user_type', 'worker')->where('status', 'inactive')->count();
      
        
        $reserved_shopping_count = HousingMovementsWorkerBooking::whereIn('user_id',$userIds)->count();
        $bookedWorker_ids = HousingMovementsWorkerBooking::whereIn('user_id',$userIds)->pluck('user_id');
       
        $HtrRoomsWorkersHistory_roomIds= HtrRoomsWorkersHistory::all()->pluck('room_id');
     
        $empty_rooms_count = HtrRoom::whereNotIn('id',$HtrRoomsWorkersHistory_roomIds)->count();
        
        $available_shopping_count = User::whereIn('id',$userIds)->where('user_type', 'worker')->whereNull('assigned_to')->whereNotIn('id', $bookedWorker_ids)->count();
     
        $leaves_count =FollowupWorkerRequest::whereIn('worker_id',$userIds)->where('type','leaves')->count();
        
            $business_id = request()->session()->get('user.business_id');
    
    
            $ContactsLocation = SalesProject::all()->pluck('name', 'id');
         
           
            $departmentIds = EssentialsDepartment::where('business_id', $business_id)
                ->where('name', 'LIKE', '%سكن%')
                ->pluck('id')->toArray();
           
            $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
            $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
    
            $requestsProcess = null;
         
            if (!empty($departmentIds)) {
    
                $requestsProcess = FollowupWorkerRequest::select([
                    'followup_worker_requests.request_no',
                    'followup_worker_requests_process.id as process_id',
                    'followup_worker_requests.id',
                    'followup_worker_requests.type as type',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.created_at',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.status_note as note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.department_id as department_id',
                    'users.id_proof_number',
                    'essentials_wk_procedures.can_return',
                    'users.assigned_to'
    
                ])
                    ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                    ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->whereIn('followup_worker_requests.worker_id',$userIds)
                    ->whereIn('department_id', $departmentIds)->where('followup_worker_requests_process.sub_status', null);
            }
            else {
                $output = ['success' => false,
                'msg' => __('housingmovements::lang.please_add_the_HousingMovements_department'),
                    ];
                return redirect()->action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])->with('status', $output);
            }
           
            if (request()->ajax()) {
    
    
                return DataTables::of($requestsProcess ?? [])
    
                    ->editColumn('created_at', function ($row) {
    
                        return Carbon::parse($row->created_at);
                    })
                    ->editColumn('assigned_to', function ($row) use ($ContactsLocation) {
                        $item = $ContactsLocation[$row->assigned_to] ?? '';
    
                        return $item;
                    })
                    ->editColumn('status', function ($row) {
                        $status = '';
                    
                        if ($row->status == 'pending') {
                            $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                                . $this->statuses[$row->status]['name'] . '</span>';
                            
                            if (auth()->user()->can('crudExitRequests')) {
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                            }
                        } elseif (in_array($row->status, ['approved', 'rejected'])) {
                            $status = trans('followup::lang.' . $row->status);
                        }
                    
                        return $status;
                    })
    
                    ->rawColumns(['status'])
    
    
                    ->make(true);
            }
            $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
          
    
            $statuses = $this->statuses;
    
    
        return view('housingmovements::dashboard.hm_dashboard',compact('empty_rooms_count','leaves_count','available_shopping_count','reserved_shopping_count','final_exit_count', 'statuses', 'main_reasons', 'classes', 'leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::create');
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

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('housingmovements::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('housingmovements::edit');
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