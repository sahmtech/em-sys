<?php

namespace Modules\Superadmin\Http\Controllers;

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
use Modules\FollowUp\Entities\followupWorkerRequest;
use Modules\FollowUp\Entities\followupWorkerRequestProcess;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use App\ContactLocation;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
class SuperadminRequestController extends Controller
{
    
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
               'name' =>__('followup::lang.pending'),
               'class' => 'bg-yellow',
           ],
       ];
       $this->statuses2 = [
           'approved' => [
               'name' => __('followup::lang.approved'),
               'class' => 'bg-green',
           ],
         
           'pending' => [
               'name' =>__('followup::lang.pending'),
               'class' => 'bg-yellow',
           ],
       ];

   }
    public function requests()
    {
        
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'superadmin'))) {
            abort(403, 'Unauthorized action.');
        }

        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
       
     
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
      
      

        if (request()->ajax()) {

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
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->where('user_type', 'worker');
          
           
            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })
                ->editColumn('assigned_to', function ($row) use ($ContactsLocation) {
                    $item = $ContactsLocation[$row->assigned_to] ?? '';

                    return $item;
                })
                ->editColumn('status', function ($row) {
                    
                    $statusClass = $this->statuses[$row->status]['class'];
                    $statusName = $this->statuses[$row->status]['name'];
                    $status = $row->status;
                    $business_id = request()->session()->get('user.business_id');
                
                    $department1 = EssentialsDepartment::where('business_id', $business_id)
                        ->where(function ($query) {
                            $query->where('name', 'LIKE', '%تنفيذ%');
                        })
                        ->first();
                
                    $department2 = EssentialsDepartment::where('business_id', $business_id)
                        ->where(function ($query) {
                            $query->where('name', 'LIKE', '%مجلس%');
                           
                        })
                        ->first();
                
                    if ($department1 && $department2) {
                        $departmentId1 = $department1->id;
                        $departmentId2 = $department2->id;
                
                        if ($row->status == 'pending' && ($row->department_id == $departmentId1 || $row->department_id == $departmentId2)) {
                            $status = '<span class="label ' . $statusClass . '">' . $statusName . '</span>';
                            $status = '<a href="#" class="change_status" data-request-id="' . $row->process_id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statusName . '"> ' . $status . '</a>';
                        } elseif (in_array($row->status, ['approved', 'rejected'])) {
                            $status = trans('followup::lang.' . $row->status);
                        } elseif ($row->status == 'pending' && !($row->department_id == $departmentId1 || $row->department_id == $departmentId2)) {
                            $status = trans('followup::lang.under_process');
                        }
                    } else {
                        $status = trans('followup::lang.' . $row->status);
                    }
                 
                    return $status;
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
       
        $department1 = EssentialsDepartment::where('business_id', $business_id)
        ->where(function ($query) {
            $query->where('name', 'LIKE', '%تنفيذ%');
        })
        ->first();

        $department2 = EssentialsDepartment::where('business_id', $business_id)
        ->where(function ($query) {
            $query->where('name', 'LIKE', '%مجلس%');
           
        })
        ->first();

        return view('superadmin::requests.allRequest')->with(compact('workers','statuses','department1','department2' ,'main_reasons', 'classes', 'leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('superadmin::create');
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
        return view('superadmin::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('superadmin::edit');
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
