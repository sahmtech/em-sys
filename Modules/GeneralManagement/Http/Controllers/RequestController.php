<?php

namespace Modules\GeneralManagement\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;;

use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use App\ContactLocation;
use Exception;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Sales\Entities\SalesProject;
class RequestController extends Controller
{
    protected $moduleUtil;
    protected $statuses;
    protected $statuses2;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    
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
 
         $business_id = request()->session()->get('user.business_id');
         $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
 
 
         $crud_requests = auth()->user()->can('followup.crud_requests');
         if (!($isSuperAdmin || $crud_requests)) {
            //temp  abort(403, 'Unauthorized action.');
         }
 
         $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
         $ContactsLocation = SalesProject::all()->pluck('name', 'id');
     
 
 
         $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
         $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
 
         $requestsProcess = null;
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
             ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id');
 
            $user_businesses_ids = Business::pluck('id')->unique()->toArray();
            $user_projects_ids = SalesProject::all('id')->unique()->toArray();
             if (!$is_admin) {
                 $userProjects = [];
                 $userBusinesses = [];
                 $roles = auth()->user()->roles;
                 foreach ($roles as $role) {
     
                     $accessRole = AccessRole::where('role_id', $role->id)->first();
     
                     if ($accessRole) {
                         $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                         $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();
     
                         $userProjects = array_merge($userProjects, $userProjectsForRole);
                         $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                     }
                    
                 }
                 $user_projects_ids = array_unique($userProjects);
                 $user_businesses_ids = array_unique($userBusinesses);
             }
         
         if (request()->ajax()) {
 
 
             return DataTables::of($requestsProcess ?? [])
 
                 ->editColumn('created_at', function ($row) {
 
 
                     return Carbon::parse($row->created_at);
                 })
              
                 ->editColumn('status', function ($row) use ($user_businesses_ids) {
                     try {
                         $statusClass = $this->statuses[$row->status]['class'];
                         $statusName = $this->statuses[$row->status]['name'];
                         $status = $row->status;
                       
 
                         $departmentIds = EssentialsDepartment::whereIn('business_id', $user_businesses_ids)
                         ->where(function ($query) {
                             $query->where('name', 'like', '%تنفيذ%')
                                 ->orWhere('name', 'like', '%مجلس%')
                                 ->orWhere('name', 'like', '%عليا%');

                         })
                         ->pluck('id')->toArray();
                         
                         if ($departmentIds) {
                           
                            if (in_array($row->status, ['approved', 'rejected'])) {
                                $status = trans('followup::lang.' . $row->status);
                            } elseif ($row->status == 'pending' && in_array($row->department_id, $departmentIds)) {
                                $status = '<span class="label ' . $statusClass . '">' . $statusName . '</span>';
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->process_id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statusName . '"> ' . $status . '</a>';
                            } elseif ($row->status == 'pending' && !in_array($row->department_id, $departmentIds)) {
                                $status = trans('followup::lang.under_process');
                            }
                            
                         } else {
                            $status = trans('followup::lang.' . $row->status);

                         }
 
                         return $status;
                     } catch (\Exception $e) {
                         return '';
                     }
                 })
 
                 ->rawColumns(['status'])
 
 
                 ->make(true);
         }
         $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
         $query = User::where('business_id', $business_id)->where('users.user_type', '=', 'worker');
         $all_users = $query->select(
             'id',
             DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
          ' - ',COALESCE(id_proof_number,'')) as full_name")
         )->get();
 
         $workers = $all_users->pluck('full_name', 'id');
         $statuses = $this->statuses;
 
        
        $departmentIds = EssentialsDepartment::whereIn('business_id', $user_businesses_ids)
                         ->where(function ($query) {
                             $query->where('name', 'like', '%تنفيذ%')
                                 ->orWhere('name', 'like', '%مجلس%')
                                 ->orWhere('name', 'like', '%عليا%');

                         })
                         ->pluck('id')->toArray();
                    
 
         return view('generalmanagement::requests.allRequest')->with(compact('workers', 'statuses', 'departmentIds', 'main_reasons', 'classes', 'leaveTypes'));
     }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('generalmanagement::create');
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
        return view('generalmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('generalmanagement::edit');
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
