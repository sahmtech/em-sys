<?php

namespace Modules\FollowUp\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\followupWorkerRequest;
use Modules\FollowUp\Entities\followupWorkerRequestProcess;
use Carbon\Carbon;

class FollowUpRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    protected $statuses;
    protected $statuses2;

     
    public function __construct(ModuleUtil $moduleUtil){
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
  
    public function changeStatus(Request $request)
    {
        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);
    
            $requestProcess = FollowupWorkerRequestProcess::find($input['request_id']);
    
            $requestProcess->status = $input['status'];
            $requestProcess->reason = $input['reason'] ?? null;
            $requestProcess->status_note = $input['note'] ?? null;
            $requestProcess->updated_by =auth()->user()->id;
    
            $requestProcess->save();
    
            if ($input['status'] == 'approved') {
                $procedure = EssentialsWkProcedure::find($requestProcess->procedure_id);
    
                
                if ($procedure && $procedure->end == 1) {
                    $requestProcess->followupWorkerRequest->status = 'approved';
                    $requestProcess->followupWorkerRequest->save();

                } else {
                    $nextDepartmentId = $procedure->next_department_id;
                    $nextProcedure = EssentialsWkProcedure::where('department_id', $nextDepartmentId)
                        ->where('type', $requestProcess->followupWorkerRequest->type)
                        ->first();
        
                    if ($nextProcedure) {
                        $newRequestProcess = new FollowupWorkerRequestProcess();
                        $newRequestProcess->worker_request_id = $requestProcess->worker_request_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
                        $newRequestProcess->save();
                    }
                }
            }
            if ($input['status'] == 'rejected'){
                $requestProcess->followupWorkerRequest->status = 'rejected';
                $requestProcess->followupWorkerRequest->save();
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
  
  
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module')) && ! $is_admin) {
                     abort(403, 'Unauthorized action.');
            }
        $leaveTypes=EssentialsLeaveType::all()->pluck('leave_type','id');
        $query = User::where('business_id', $business_id)->where('users.user_type','=' ,'worker');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $workers = $all_users->pluck('full_name', 'id');
        return view('followup::requests.create')->with(compact('workers','leaveTypes'));
    }

    public function store(Request $request)
    {
 
            $validatedData = $request->validate([
                'worker_id' => 'required|exists:users,id',
                'type' => 'required|in:exitRequest,returnRequest,escapeRequest,advanceSalary,leavesAndDepartures,atmCard,residenceRenewal,residenceCard,workerTransfer',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'escape_date'=>'nullable|date',
                'note' => 'nullable|string',
                'reason' => 'nullable|string',
                'leaveType' => 'sometimes',
                'escape_time' => 'sometimes', 
                'attachment' => 'sometimes',
                'amount' => 'sometimes', 
                'attachment' => 'sometimes|file',
                'installmentsNumber' => 'sometimes', 
                'monthlyInstallment' => 'sometimes',
            
            ]);
            $attachmentPath = null;
           
           
            if (isset($validatedData['attachment']) && !empty($validatedData['attachment'])) {
                $attachmentPath = $validatedData['attachment']->store('/requests_attachments');
            }

            if (is_null($validatedData['start_date']) && !empty($validatedData['escape_date'])) {
                $startDate = $validatedData['escape_date'];
            } else {
                $startDate = $validatedData['start_date'];
            }
            $procedure=EssentialsWkProcedure::where('type',$validatedData['type'])->get();
            if($procedure->count() == 0){
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.this_type_has_not_procedure'),
                ];
              return redirect()->route('createRequest')->withErrors([$output['msg']]);
            
            }
            $workerRequest = followupWorkerRequest::create([
                'request_no' => $this->generateRequestNo($validatedData['type']),
                'worker_id' => $validatedData['worker_id'],
                'type' => $validatedData['type'],
                'start_date' => $startDate,
                'end_date' => $validatedData['end_date'],
                'reason' => $validatedData['reason'],
                'note' => $validatedData['note'],
                'attachment' => $attachmentPath,
                'essentials_leave_type_id' => $validatedData['leaveType'],
                'escape_time' => $validatedData['escape_time'],
                'installmentsNumber' => $validatedData['installmentsNumber'],
                'monthlyInstallment' => $validatedData['monthlyInstallment'],
                'advSalaryAmount'=>$validatedData['amount'],
                'advSalaryAmount'=>$validatedData['amount'],
                'updated_by'=>auth()->user()->id

                

            ]);

            
            if ($workerRequest) {
                $process = followupWorkerRequestProcess::create([
                    'worker_request_id' => $workerRequest->id,
                    'procedure_id' => $this->getProcedureIdForType($validatedData['type']),
                    'status' => 'pending',
                    'reason' => null,
                    'status_note' => null,
                ]);

                if ($process) {
                    $output = [
                        'success' => true,
                        'msg' => __('followup::lang.stored_successfully'),
                    ];
                   return redirect()->route('createRequest')->with('success', $output['msg']);
                } else {
                
                    $workerRequest->delete();
                    $output = [
                        'success' => false,
                        'msg' => __('messages.something_went_wrong'),
                    ];
                    return redirect()->route('createRequest')->withErrors([$output['msg']]);
                }
            } else {
            
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
                return redirect()->route('createRequest')->withErrors([$output['msg']]);
            }
            
    }


    private function getProcedureIdForType($type)
    {
    
        $procedure = EssentialsWkProcedure::where('type', $type)->where('start', 1)->first();

        return $procedure ? $procedure->id : null;
    }

    private function generateRequestNo($type)
    {
        $latestRecord = FollowupWorkerRequest::where('type',$type)->orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $prefix = $this->getTypePrefix($type);
            $numericPart = (int)substr($latestRefNo, strlen($prefix));
            $numericPart++;
            $input['request_no'] = $prefix . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $input['request_no'] = $this->getTypePrefix($type) . '0001';
        }

        return $input['request_no'];
    }

    private function getTypePrefix($type)
    {
    
        $typePrefixMap = [
            'exitRequest' => 'ex',
            'returnRequest' => 'ret',
            'leavesAndDepartures' => 'lev',
            'residenceRenewal' => 'resRe',
            'escapeRequest'=>'escRe',
            'advanceSalary'=>'advRe', 
            'atmCard'=>'atm',
            'residenceCard'=>'res',
            'workerTransfer'=>'wT',
        
        ];

        return $typePrefixMap[$type];
    }

    public function exitRequestIndex()
    {
        
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
      if (request()->ajax()) {
        $requestsProcess = null;
  
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department)->where('followup_worker_requests.type','exitRequest');
           
            }
            
            return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
        }
   
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','exitRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.exitRequestIndex')->with(compact('statuses'));
    }

    public function returnRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','returnRequest');
            }
           
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
     }
       
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','returnRequest')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.returnRequestIndex')->with(compact('statuses'));
    }

    public function escapeRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.created_at',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.escape_time',
                'followup_worker_requests.advSalaryAmount',
                'followup_worker_requests.monthlyInstallment',
                'followup_worker_requests.installmentsNumber',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',      
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','escapeRequest');
            }
        
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
     }
      
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','escapeRequest')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.escapeRequestIndex')->with(compact('statuses'));
    }

    public function advanceSalaryIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at as created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.advSalaryAmount',
                'followup_worker_requests.monthlyInstallment',
                'followup_worker_requests.installmentsNumber',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department)->where('followup_worker_requests.type','advanceSalary');
            }
          
                 return DataTables::of($requestsProcess ?? [])
            ->editColumn('status', function ($row) {

                $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
                .$this->statuses[$row->status]['name'].'</span>';
                $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
                
                return $status;
            })
            ->editColumn('created_at', function ($row) {

               
                return Carbon::parse($row->created_at);
            })
        ->rawColumns(['status'])
            ->make(true);
  
     }
     
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','advanceSalary')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.advanceSalaryIndex')->with(compact('statuses'));
    }
    public function leavesAndDeparturesIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',

    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','leavesAndDepartures');
            }
         
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
       
        ->rawColumns(['status'])
            ->make(true);
  
     }
      
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','leavesAndDepartures')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.leavesAndDeparturesIndex')->with(compact('statuses'));
    }

    public function atmCardIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','atmCard');
            }
          
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
     }

     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','atmCard')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.atmCardIndex')->with(compact('statuses'));
    }

    public function residenceRenewalIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','residenceRenewal');
            }
        
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
     }
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','residenceRenewal')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.residenceRenewalIndex')->with(compact('statuses'));
    }

    public function residenceCardIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','residenceCard');
            }
      
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
     }
     
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','residenceCard')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        return view('followup::requests.residenceCardIndex')->with(compact('statuses'));
    }

    public function workerTransferIndex()
    {
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests= auth()->user()->can('followup.crud_requests');
        if (! $crud_requests) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $department = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%متابعة%')
        ->first();

       
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;
            
            $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status','pending')->select([
                'followup_worker_requests_process.id as id',
                'followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.reason',
                'followup_worker_requests_process.status_note',
                'followup_worker_requests_process.created_at',
                'followup_worker_requests_process.updated_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.id as request_id',
                'followup_worker_requests.request_no',
                'followup_worker_requests.worker_id',
                'followup_worker_requests.type',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.id as procedure_id',
                'essentials_wk_procedures.type as procedure_type',
                'essentials_wk_procedures.department_id as department_id',
                'essentials_wk_procedures.can_return',
                'essentials_wk_procedures.start as start',
    
            ])
            ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
            ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('department_id', $department);
            $requestsProcess=$requestsProcess->where('followup_worker_requests.type','workerTransfer');
            }
          
             return DataTables::of($requestsProcess ?? [])
        ->editColumn('status', function ($row) {

            $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
            .$this->statuses[$row->status]['name'].'</span>';
            $status = '<a href="#" class="change_status" data-request-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
            
            return $status;
        })
        ->rawColumns(['status'])
            ->make(true);
  
     }
     if ($department) {
        $department = $department->id;
        $pros = EssentialsWkProcedure::where('department_id', $department)->where('type','workerTransfer')->first();
        if ($pros) {
            $can_reject = $pros->can_reject;
            $can_reject = $can_reject ?? 0;
            $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
        } else {
            $statuses = $this->statuses;
        }
    }
        
        return view('followup::requests.workerTransferIndex')->with(compact('statuses'));
    }

    public function returnReq(Request $request)
    {
        try {
            
            $requestId = $request->input('requestId');
    
            $requestProcess = FollowupWorkerRequestProcess::find($requestId);
    
            if ($requestProcess) {
              
                $procedure = EssentialsWkProcedure::find($requestProcess->procedure_id);
         
                if ($procedure) {
               
                    $departmentId = $procedure->department_id;
                  
                    $nameDepartment=EssentialsDepartment::where('id', $departmentId)->first()->name;
                   $newProcedure = EssentialsWkProcedure::where('next_department_id', $departmentId)
                        ->where('type', $procedure->type)
                        ->first();
                  
                    
                    if ($newProcedure) {
                            
                            $requestProcess->update([
                                'procedure_id' => $newProcedure->id,
                                'status' => 'pending',
                                'is_returned' => 1,
                                'updated_by'=>auth()->user()->id,
                                'status_note' => __('followup::lang.returned_by') . " " . $nameDepartment,


                            
                            ]);
        
                        //  return response()->json(['success' => true, 'msg' => 'Request returned successfully']);
                        $output = [
                            'success' => true,
                            'msg' => __('followup::lang.returned_successfully'),
                        ];
                    }
                    else {
                        $output = [
                            'success' => false,
                            'msg' => __('followup::lang.there_is_no_department_to_return_for'),
                        ];
                    }
                }
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
}
