<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\AccessRoleCompany;
use App\Company;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\FollowUp\Entities\FollowupRequestsAttachment;

use App\Utils\ModuleUtil;

use App\Business;

use App\User;
use Carbon\Carbon;
use App\ContactLocation;
use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsResidencyHistory;
use Modules\Essentials\Entities\EssentialsWorkCard;
use Modules\Essentials\Entities\EssentialsOfficialDocument;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use Spatie\Permission\Models\Permission;

use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Sales\Entities\salesContractItem;

use Modules\Essentials\Http\RequestsempRequest;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use App\Category;
use App\Transaction;




class EssentialsCardsController extends Controller
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

    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $responsible_client = null;
      
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }
        $card = EssentialsWorkCard::whereIn('employee_id',$userIds)->with([
            'user',
            'user.OfficialDocument'
        ])
            ->select('id', 'employee_id', 'work_card_no as card_no', 'fees as fees', 'Payment_number as Payment_number');


        if (!empty($request->input('project'))) {
            $card->whereHas('user.assignedTo', function ($query) use ($request) {
                $query->where('id', $request->input('project'));
            });
        }

        if (!empty($request->input('proof_numbers')) &&  $request->input('proof_numbers') != "all") {
            $card->whereHas('user', function ($query) use ($request) {
                $query->whereIn('id', $request->input('proof_numbers'));
            });
        }


        $query = User::whereIn('id',$userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');

        if (request()->ajax()) {




            return Datatables::of($card)


                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })



                ->editColumn('company_name', function ($row) {
                    return $row->user->business?->name ?? '';
                })



                ->editColumn('fixnumber', function ($row) {
                    return $row->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')->first()->unified_number ?? '';
                })

                ->editColumn('user', function ($row) {
                    return $row->user->first_name . ' ' . $row->user->mid_name . ' ' . $row->user->last_name ?? '';
                })

                ->editColumn('project', function ($row) {
                    return $row->user->assignedTo?->name ?? '';
                })

                ->addColumn(
                    'responsible_client',
                    function ($row) use ($name_in_charge_choices) {
                        $names = "";

                        $userIds = json_decode($row->user->assignedTo->assigned_to, true);

                        if ($userIds) {
                            $lastUserId = end($userIds);

                            foreach ($userIds as $user_id) {
                                $names .= $name_in_charge_choices[$user_id];

                                if ($user_id !== $lastUserId) {
                                    $names .= ', ';
                                }
                            }
                        }

                        return $names;
                    }
                )
          

                ->editColumn('proof_number', function ($row) {
                    $residencePermitDocument = $row->user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();

                    return $residencePermitDocument ? $residencePermitDocument->number : '';
                })

                ->editColumn('expiration_date', function ($row) {
                    $residencePermitDocument = $row->user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();

                    return $residencePermitDocument ? $residencePermitDocument->expiration_date : '';
                })

                ->editColumn('nationality', function ($row) {
                    return $row->user->country?->nationality ?? '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    return $html;
                })
                ->filter(function ($query) use ($request) {

                    if (!empty($request->input('full_name'))) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
                    }
                })



                ->rawColumns(['action', 'profession', 'nationality', 'checkbox'])
                ->make(true);
        }


        $sales_projects = SalesProject::pluck('name', 'id');

        $proof_numbers = User::whereIn('users.id',$userIds)->where('users.user_type', 'worker')
            ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"), 'users.id')->get();


        return view('essentials::cards.index')->with(compact('sales_projects', 'proof_numbers'));
    }


    public function work_cards_all_requests()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    
        $can_change_status = auth()->user()->can('essentials.workcards_requests_change_status');

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%حكومية%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_governmental_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $requestTypes = EssentialsWkProcedure::whereIn('department_id', $departmentIds)
            ->where('start', '1')
            ->pluck('type')
            ->mapWithKeys(function ($type) {
                return [$type => __("essentials::lang.$type")];
            })->toArray();


        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
        $users = User::whereIn('id', $userIds)->where('status', '!=', 'inactive')->where('id_proof_name', '!=', 'national_id')->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');
        $statuses = $this->statuses;

        $requestsProcess = null;


        $requestsProcess = FollowupWorkerRequest::select([
            'followup_worker_requests.request_no','followup_worker_requests_process.id as process_id','followup_worker_requests.id',
            'followup_worker_requests.type as type','followup_worker_requests.created_at','followup_worker_requests_process.status',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'followup_worker_requests_process.status_note as note','followup_worker_requests.reason','essentials_wk_procedures.department_id as department_id',
            'users.id_proof_number','essentials_wk_procedures.can_return','users.assigned_to','followup_worker_requests_process.procedure_id as procedure_id',
        ])
        ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
        ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
        ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->whereIn('department_id', $departmentIds)
        ->whereIn('followup_worker_requests.worker_id', $userIds)->where('users.status', '!=', 'inactive')->where('followup_worker_requests_process.sub_status', null);


        if (request()->ajax()) {

            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                // ->editColumn('assigned_to', function ($row) use ($ContactsLocation) {
                //     $item = $ContactsLocation[$row->assigned_to] ?? '';

                //     return $item;
                // })
                ->editColumn('status', function ($row)  use ($is_admin, $can_change_status) {
                    $status = '';
                    $procedureStart=EssentialsWkProcedure::where('id',$row->procedure_id)->first();
                    if($procedureStart->start != 1){
                        if ($is_admin || $can_change_status) {
                        if ($row->status == 'pending') {
                            $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                                . __($this->statuses[$row->status]['name']) . '</span>';
                            $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                        } elseif (in_array($row->status, ['approved', 'rejected'])) {
                            $status = trans('essentials::lang.' . $row->status);
                        }
                    } else {
                        $status = trans('essentials::lang.' . $row->status);
                    }
                }
                else {
                    $status = trans('essentials::lang.' . $row->status);
                }
                        
                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }

        return view('essentials::cards.allrequest')->with(compact('users', 'requestTypes','statuses', 'main_reasons', 'classes', 'leaveTypes'));
    }
    private function getFullName($userId)
    {
        $user = User::find($userId);

        if ($user) {
            return $user->first_name . ' ' . $user->last_name;
        }

        return null;
    }
    private function getContactLocation($id)
    {
        $contact = SalesProject::find($id);

        if ($contact) {
            return $contact->name;
        }

        return null;
    } 
    private function getProcessIdForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->id;
    }
    private function getProcessStatusForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->status;
    }
    public function storeRequest(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $attachmentPath = null;
        if (isset($request->attachment) && !empty($request->attachment)) {
            $attachmentPath = $request->attachment->store('/requests_attachments');
        }

        if (is_null($request->start_date) && !empty($request->escape_date)) {
            $startDate = $request->escape_date;
        } elseif (is_null($request->start_date) && !empty($request->exit_date)) {
            $startDate = $request->exit_date;
        } else {
            $startDate = $request->startDate;
        }
        if (is_null($request->end_date) && !empty($request->return_date)) {
            $end_date = $request->return_date;
        } else {
            $end_date = $request->end_date;
        }
        if ($request->type == 'cancleContractRequest' && !empty($request->main_reason)) {

            $contract = EssentialsEmployeesContract::where('employee_id', $request->worker_id)->first();

            if (!$contract) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.no_contract_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }


            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.no_wishes_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }

            $contractEndDate = Carbon::parse($contract->contract_end_date);
            $todayDate = Carbon::now();

            if ($todayDate->diffInMonths($contractEndDate) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.contract_expired'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }
        }

        $success = 1;
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%حكومية%')
        ->pluck('id')->toArray();
        foreach ($request->worker_id as $workerId) {

            if ($workerId !== null) {
                $business_id = User::where('id', $workerId)->first()->business_id;

                if ($request->type == "exitRequest") {
                    $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $workerId)->first()->contract_end_date ?? null;
                }

                $workerRequest = new FollowupWorkerRequest;

                $workerRequest->request_no = $this->generateRequestNo($request->type);
                $workerRequest->worker_id = $workerId;
                $workerRequest->type = $request->type;
                $workerRequest->start_date = $startDate;
                $workerRequest->end_date = $end_date;
                $workerRequest->reason = $request->reason;
                $workerRequest->note = $request->note;
                $workerRequest->attachment = $attachmentPath;
                $workerRequest->essentials_leave_type_id = $request->leaveType;
                $workerRequest->escape_time = $request->escape_time;
                $workerRequest->installmentsNumber = $request->installmentsNumber;
                $workerRequest->monthlyInstallment = $request->monthlyInstallment;
                $workerRequest->advSalaryAmount = $request->amount;
                $workerRequest->updated_by = auth()->user()->id;
                $workerRequest->insurance_classes_id = $request->ins_class;
                $workerRequest->baladyCardType = $request->baladyType;
                $workerRequest->resCardEditType = $request->resEditType;
                $workerRequest->workInjuriesDate = $request->workInjuriesDate;
                $workerRequest->contract_main_reason_id = $request->main_reason;
                $workerRequest->contract_sub_reason_id = $request->sub_reason;
                $workerRequest->visa_number = $request->visa_number;
                $workerRequest->atmCardType = $request->atmType;
                $workerRequest->save();
                if(isset($request->attachment) && !empty($request->attachment)){
                    FollowupRequestsAttachment::create([
                        'request_id' => $workerRequest->id,
                        'file_path' => $attachmentPath,
            
                    ]);}
                if ($workerRequest) {
                    $procedure =EssentialsWkProcedure::where('business_id', $business_id)
                    ->where('type', $request->type)->where('start', 1)->whereIn('department_id', $departmentIds)->first();
                 

                        $process = FollowupWorkerRequestProcess::create([
                            'worker_request_id' => $workerRequest->id,
                            'procedure_id' => $procedure ? $procedure->id : null,
                            'status' => 'pending',
                            'reason' => null,
                            'status_note' => null,
                        ]);

                        if (!$process) {

                            $workerRequest->delete();

                            $success = 0;
                        }
                    
                    $nextProcedure=EssentialsWkProcedure::where('business_id', $business_id)->where('type', $request->type)
                    ->where('department_id',$procedure->next_department_id)->first()->id;

                    FollowupWorkerRequestProcess::create([
                        'worker_request_id' => $workerRequest->id,
                        'procedure_id' => $nextProcedure ? $nextProcedure : null,
                        'status' => 'pending',
                        'reason' => null,
                        'status_note' => null,
                    ]);
                } else {

                    $success = 0;
                }
            }
        }
        if ($success) {
            $output = [
                'success' => 1,
                'msg' => __('messages.added_success'),
            ];
            return redirect()->back()->with('success', $output['msg']);
        } else {
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->back()->withErrors([$output['msg']]);
        }
    }
   
    public function viewRequest($id)
    {

        $request = FollowupWorkerRequest::with([
            'user', 'createdUser', 'followupWorkerRequestProcess.procedure.department', 'attachments'
        ])->where('id', $id)->first();

        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        }


        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'status' => trans("followup::lang.{$request->status}"),
            'type' => trans("followup::lang.{$request->type}"),
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];
        $currentStep = EssentialsWkProcedure::where('id', $request->followupWorkerRequestProcess[0]->procedure_id)->first();

        while ($currentStep && !$currentStep->end ) {

            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $currentStep->next_department_id)->first())->name,
            ];

            $currentStep = EssentialsWkProcedure::where('type', $request->type)
                ->where('department_id', $currentStep->next_department_id)
                ->first();
        }

        if ($currentStep && $currentStep->end == 1) {
            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => null,
            ];
        };

        $attachments = null;
        if ($request->attachments) {

            $attachments = $request->attachments->map(function ($attachments) {
                return [
                    'request_id' => $attachments->request_id,
                    'file_path' => $attachments->file_path,
                    'created_at' => $attachments->created_at,
                ];
            });
        }

        $userInfo = [
            'worker_id' => $request->user->id,
            'user_type' => trans("followup::lang.{$request->user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->user->nationality_id)->first())->nationality,
            'assigned_to' =>  $this->getContactLocation($request->user->assigned_to),
            'worker_full_name' => $request->user->first_name . ' ' . $request->user->last_name,
            'id_proof_number' => $request->user->id_proof_number,
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->user->id)->where('type', 'residence_permit')->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->user->id)->where('type', 'passport')->first())->number,

        ];

        $createdUserInfo = [
            'created_user_id' => $request->createdUser->id,
            'user_type' => $request->createdUser->user_type,
            'nationality_id' => $request->createdUser->nationality_id,
            'created_user_full_name' => $request->createdUser->first_name . ' ' . $request->createdUser->last_name,
            'id_proof_number' => $request->createdUser->id_proof_number,

        ];

        $followupProcesses = [];
        foreach ($request->followupWorkerRequestProcess as $process) {
            $processInfo = [
                'id' => $process->id,
                'status' => trans("followup::lang.{$process->status}"),
                'procedure_id' => $process->procedure_id,
                'is_returned' => $process->is_returned,
                'updated_by' =>  $this->getFullName($process->updated_by),
                'reason' => $process->reason,
                'status_note' => $process->status_note,
                'department' => [
                    'id' => $process->procedure->department->id,
                    'name' => $process->procedure->department->name,
                ],

            ];
            $followupProcesses[] = $processInfo;
        }

        $result = [

            'request_info' => $requestInfo,
            'user_info' => $userInfo,
            'created_user_info' => $createdUserInfo,
            'followup_processes' => $followupProcesses,
            'workflow' => $workflow,
            'attachments' => $attachments,
        ];

        return response()->json($result);
    }

    private function generateRequestNo($type)
    {
        $latestRecord = FollowupWorkerRequest::where('type', $type)->orderBy('request_no', 'desc')->first();

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
            'escapeRequest' => 'escRe',
            'advanceSalary' => 'advRe',
            'atmCard' => 'atm',
            'residenceCard' => 'res',
            'workerTransfer' => 'wT',
            'workInjuriesRequest' => 'wIng',
            'residenceEditRequest' => 'resEd',
            'baladyCardRequest' => 'bal',
            'insuranceUpgradeRequest' => 'insUp',
            'mofaRequest' => 'mofa',
            'chamberRequest' => 'ch',
            'cancleContractRequest' => 'con',
            'WarningRequest' => 'WR'
        ];

        return $typePrefixMap[$type];
    }

    public function work_cards_operation(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
   
        $can_show_employee_profile= auth()->user()->can('essentials.show_employee_profile') ;
        $permissionName = 'essentials.view_profile_picture';
  
        if (!Permission::where('name', $permissionName)->exists()) {
            $permission = new Permission(['name' => $permissionName]);
            $permission->save();
        } else {

            $permission = Permission::where('name', $permissionName)->first();
        }

        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
       
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
      
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
    
        $professions = EssentialsProfession::all()->pluck('name', 'id');

        $contract = EssentialsEmployeesContract::all()->pluck('contract_end_date', 'id');
       
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }

       
        $users = User::whereIn('users.id', $userIds)->with(['userAllowancesAndDeductions'])->where('users.is_cmmsn_agnt', 0)
            ->where('nationality_id', '!=', 5)
            ->leftjoin('essentials_employee_appointmets', 'essentials_employee_appointmets.employee_id', 'users.id')
            ->leftjoin('essentials_admission_to_works', 'essentials_admission_to_works.employee_id', 'users.id')
            ->leftjoin('essentials_employees_contracts', 'essentials_employees_contracts.employee_id', 'users.id')
            ->leftJoin('essentials_countries', 'essentials_countries.id', '=', 'users.nationality_id')
            ->select([
                'users.id as id',
                'users.emp_number',
                'users.profile_image',
                'users.username',
                'users.business_id',
                'users.user_type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''),' ', COALESCE(users.last_name, '')) as full_name"),
                'users.id_proof_number',
                DB::raw("COALESCE(essentials_countries.nationality, '') as nationality"),

                'essentials_admission_to_works.admissions_date as admissions_date',
                'essentials_employees_contracts.contract_end_date as contract_end_date',
                'users.email',
                'users.allow_login',
                'users.contact_number',
                'users.essentials_department_id',
                'users.status',
                'users.essentials_salary',
                'users.total_salary',
                'essentials_employee_appointmets.profession_id as profession_id'
            ])->orderby('id', 'desc');


        
        if (!empty($request->input('status-select'))) {
            $users->where('users.status', $request->input('status'));
        }

        if (!empty($request->input('business'))) {

            $users->where('users.business_id', $request->input('business'));
        }

        if (!empty($request->input('nationality'))) {

            $users->where('users.nationality_id', $request->input('nationality'));
            error_log("111");
        }
        if (request()->ajax()) {


            return Datatables::of($users)

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })

                ->addColumn('total_salary', function ($row) {
                    return $row->calculateTotalSalary();
                })

                ->editColumn('essentials_department_id', function ($row) use ($departments) {
                    $item = $departments[$row->essentials_department_id] ?? '';

                    return $item;
                })


                ->addColumn('profession', function ($row) use ($appointments, $professions) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $professions[$professionId] ?? '';

                    return $professionName;
                })


                ->addColumn('view', function ($row) use($is_admin ,$can_show_employee_profile){
                    $html ='';
                    if($is_admin || $can_show_employee_profile){
                        $html = '<a href="' . route('operations_show_employee', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';
                    }
                   

                    return $html;
                })

                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->where('first_name', $keyword)->orWhere('last_name', $keyword);
                })

                ->filterColumn('nationality', function ($query, $keyword) {
                    $query->whereRaw("COALESCE(essentials_countries.nationality, '')  like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('admissions_date', function ($query, $keyword) {
                    $query->whereRaw("admissions_date  like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('contract_end_date', function ($query, $keyword) {
                    $query->whereRaw("contract_end_date  like ?", ["%{$keyword}%"]);
                })


                ->filterColumn('profession', function ($query, $keyword) {
                    $query->whereHas('appointment.profession', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                })
                //->removecolumn('id')
                ->rawColumns(['user_type', 'business_id', 'action', 'profession', 'view', 'checkbox'])
                ->make(true);
        }
        $companies = Company::whereIn('id', $companies_ids)->pluck('name', 'id');
       
        $countries = EssentialsCountry::forDropdown();
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');

 
        $status = [
            'active' => 'active',
            'inactive' => 'inactive',
            'terminated' => 'terminated',
            'vecation' => 'vecation',
        ];



        $offer_prices = Transaction::where([['transactions.type', '=', 'sell'], ['transactions.status', '=', 'approved']])
            ->leftJoin('sales_contracts', 'transactions.id', '=', 'sales_contracts.offer_price_id')
            ->whereNull('sales_contracts.offer_price_id')->pluck('transactions.ref_no', 'transactions.id');
        $items = salesContractItem::pluck('name_of_item', 'id');


        return view('essentials::cards.operations')
            ->with(compact(
                'contract_types',
                'nationalities',        
                'professions',           
                'countries',
                'spacializations',
                'status',
                'offer_prices',
                'items',
                'companies'
            ));
    }
    

    public function operations_show_employee($id ,Request $request)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_show_employee = auth()->user()->can('essentials.show_employee_operation');
        $business_id = request()->session()->get('user.business_id');
        $documents = null;


        if (!($is_admin || $can_show_employee)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        
        if (!$is_admin) 
        {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

        }

      
        if (!in_array($id , $userIds)) {
            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => __('essentials::lang.user_not_found'),
            ]);
        }


        $user = User::with(['contactAccess', 'OfficialDocument', 'proposal_worker'])
        ->select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))
        ->find($id);
    
    
        if ($user->user_type == 'employee') {

                $documents = $user->OfficialDocument;
            } 
        else if ($user->user_type == 'worker') {


                if (!empty($user->proposal_worker_id)) {


                    $officialDocuments = $user->OfficialDocument;
                    $workerDocuments = $user->proposal_worker?->worker_documents;

                    $documents = $officialDocuments->merge($workerDocuments);
                } else {
                    $documents = $user->OfficialDocument;
                }
            }



        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)
        ->where('is_active',1)
        ->latest('created_at')
        ->first();

        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)
        ->first();
        
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)
        ->where('is_active',1)
        ->latest('created_at')
        ->first();

        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)
        ->where('is_active',1)
        ->latest('created_at')
        ->value('profession_id');
     
        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)?->name ?? " ";
        } 
        else {$profession = "";}
       
        $user->profession = $profession;
        $view_partials = $this->moduleUtil->getModuleData(
            'moduleViewPartials',
            ['view' => 'manage_user.show', 'user' => $user]
        );


        $query = User::whereIn('id', $userIds);
        $all_users =$query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $activities = Activity::forSubject($user)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();


        $nationalities = EssentialsCountry::nationalityForDropdown();
        $nationality_id = $user->nationality_id;
        $nationality = "";
        if (!empty($nationality_id)) {
            $nationality = EssentialsCountry::select('nationality')->where('id', '=', $nationality_id)->first();
        }



        return view('essentials::cards.show_emp')->with(compact(
            'user',
            'view_partials',
            'users',
            'activities',
            'bank_name',
            'admissions_to_work',
            'Qualification',
            'Contract',
            'nationalities',
            'nationality',
            'documents'
        ));


    }

    public function expired_residencies()
    {


        $residencies = EssentialsOfficialDocument::where('type', 'residence_permit')
            ->whereDate('expiration_date', '>=', now())
            ->whereDate('expiration_date', '<=', now()->addDays(15))
            ->get();

        if (request()->ajax()) {

            return DataTables::of($residencies)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->employee->first_name . ' ' . $row->employee->last_name;
                    }
                )
                ->addColumn(
                    'residency',
                    function ($row) {
                        return $row->number;
                    }
                )
                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->employee->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->employee->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->expiration_date;
                    }
                )
                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }

        return view('essentials::cards.expired_residencies');
    }


     public function all_expired_residencies()
     {
       
        $today = today()->format('Y-m-d');
       
        $residencies = EssentialsOfficialDocument::with(['employee'])->where('type', 'residence_permit')
        ->whereDate('expiration_date', '<=', Carbon::now() )->orderby('id','desc')->get(); 
      
       
       

       //dd( $residencies->first());

        if (request()->ajax()) {

        return DataTables::of($residencies)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->employee?->first_name . ' ' . $row->employee?->last_name;
                }
            )
            ->addColumn(
                'residency',
                function ($row) {
                    return $row->number;
                }
            )
            ->addColumn(
                'project',
                function ($row) {
                    return $row->employee?->assignedTo?->contact->supplier_business_name ?? null;
                }
            )
            ->addColumn(
                'customer_name',
                function ($row) {
                    return $row->employee->assignedTo?->contact->supplier_business_name ?? null;
                }
            )
            ->addColumn(
                'end_date',
                function ($row) {
                    return $row->expiration_date;
                }
            )
            ->addColumn(
                'action',
                ''
                // function ($row) {
                //     $html = '';
                //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }

        return view('essentials::cards.all_expired_residencies');
    }

    public function late_for_vacation()
    {

        $late_vacation = FollowupWorkerRequest::with(['user'])
            ->where('type', 'leavesAndDepartures')
            ->where('type', 'returnRequest')
            ->whereHas('user', function ($query) {

                $query->where('status', 'vecation');
            })
            ->where('end_date', '<', now())
            ->select('end_date');




        // dd( $residencies->first());

        if (request()->ajax()) {

            return DataTables::of($late_vacation)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user->first_name . ' ' . $row->user->last_name;
                    }
                )

                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->user->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->end_date;
                    }
                )
                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }

        return view('essentials::cards.late_for_vacation');
    }

    public function final_visa()
    {
        $final_visa = EssentailsEmployeeOperation::with('user')
            ->where('operation_type', 'final_visa')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->select('end_date', 'employee_id');



        // dd( $residencies->first());

        if (request()->ajax()) {

            return DataTables::of($final_visa)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user?->first_name . ' ' . $row->user?->last_name ?? '';
                    }
                )

                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->user?->assignedTo?->contact?->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user?->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->end_date;
                    }
                )
                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }

        return view('essentials::cards.final_visa_index');
    }


    public function post_return_visa_data(Request $request)
    {
        try {
            $requestData = $request->only(['start_date', 'end_date', 'worker_id']);

            $commonStartDate = $requestData['start_date'];
            $commonEndDate = $requestData['end_date'];

            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'employee_id' => $workerId,
                    'start_date' => $commonStartDate,
                    'end_date' => $commonEndDate,
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            \Log::info('JSON Data: ' . $jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();

                foreach ($selectedData as $data) {
                    $operation = DB::table('essentails_employee_operations')->insert([
                        'operation_type' => 'return_visa',
                        'employee_id' =>  $data['employee_id'],
                        'start_date' =>  $data['start_date'],
                        'end_date' =>  $data['end_date'],
                    ]);
                }

                DB::commit();

                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }


        return response()->json($output);
    }

    public function post_final_visa_data(Request $request)
    {
        try {
            $requestData = $request->only(['end_date', 'worker_id']);

            $commonEndDate = $requestData['end_date'];


            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'employee_id' => $workerId,

                    'end_date' => $commonEndDate,
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            \Log::info('JSON Data: ' . $jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();

                foreach ($selectedData as $data) {
                  
                    $operation = DB::table('essentails_employee_operations')->insert([
                        'operation_type' => 'final_visa',
                        'employee_id' =>  $data['employee_id'],
                        'end_date' =>  $data['end_date'],
                    ]);

                    $user=User::where('id', $data['employee_id'])->first();
                    $user->update(['status' ,'inactive']);
                }


                DB::commit();

                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        // return  $requestData;
        return response()->json($output);
    }

    public function post_absent_report_data(Request $request)
    {
        try {
            $requestData = $request->only(['end_date', 'worker_id']);

            $commonEndDate = $requestData['end_date'];


            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'employee_id' => $workerId,

                    'end_date' => $commonEndDate,
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            \Log::info('JSON Data: ' . $jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();

                foreach ($selectedData as $data) {
                    $operation = DB::table('essentails_employee_operations')->insert([
                        'operation_type' => 'absent_report',
                        'employee_id' =>  $data['employee_id'],
                        'end_date' =>  $data['end_date'],
                    ]);
                    $user=user::where('id', $data['employee_id'])->first();
                    $user->update(['status' ,'inactive']);
                }

                DB::commit();

                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        // return  $requestData;
        return response()->json($output);
    }



    public function work_cards_vaction_requests()
    {


        $business_id = request()->session()->get('user.business_id');



        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $ContactsLocation = SalesProject::all()->pluck('name', 'id');

        $user_businesses_ids = Business::pluck('id')->unique()->toArray();
        $user_projects_ids = SalesProject::all('id')->unique()->toArray();



        $departmentIds = EssentialsDepartment::whereIn('business_id', $user_businesses_ids)
            ->where('name', 'LIKE', '%دولي%')
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

            ])->where('followup_worker_requests.type', 'leavesAndDepartures')

                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->whereIn('essentials_wk_procedures.department_id', $departmentIds)->where('followup_worker_requests_process.sub_status', null);
        } else {
            $output = [
                'success' => false,
                'msg' => __('internationalrelations::lang.please_add_the_Ir_department'),
            ];
            return redirect()->action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index'])->with('status', $output);
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


                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    } elseif (in_array($row->status, ['approved', 'rejected'])) {
                        $status = trans('followup::lang.' . $row->status);
                    }

                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }

        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $workers = User::where('user_type', 'worker')->whereIn('assigned_to', $user_projects_ids)->select(
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
         ' - ',COALESCE(id_proof_number,'')) as full_name")
        )->pluck('full_name', 'id');

        $statuses = $this->statuses;



        return view('essentials::cards.vactionrequest')->with(compact('workers', 'statuses', 'main_reasons', 'classes', 'leaveTypes'));
    }

    public function residencyreports(Request $request)
    {
        $sales_projects = SalesProject::pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
       
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $proof_numbers = User::whereIn('id',$userIds)->where('users.user_type', 'worker')
            ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
            ' - ',COALESCE(users.id_proof_number,'')) as full_name"), 'users.id')
            ->get();

        $report = EssentialsResidencyHistory::whereIn('worker_id',$userIds)->with(['worker'])->select('*');
        if (!empty($request->input('proof_numbers')) &&  $request->input('proof_numbers') != "all") {
            $report->whereHas('worker', function ($query) use ($request) {
                $query->whereIn('id', $request->input('proof_numbers'));
            });
        }
        if ($request->ajax()) {
            return Datatables::of($report)
                ->editColumn('user', function ($row) {
                    return $row->worker->first_name . ' ' . $row->worker->mid_name . ' ' . $row->worker->last_name ?? '';
                })
                ->make(true);
        }

        return view('essentials::cards.reports.residenceReport')->with(compact('sales_projects', 'proof_numbers'));
    }



    public function postRenewData(Request $request)
    {
        try {
            $requestData = $request->only([
                'id',
                'employee_id',
                'number',
                'expiration_date',
                'renew_duration',
                'fees',
                'Payment_number',

            ]);

            $jsonData = [];

            foreach ($requestData['id'] as $index => $workerId) {
                $jsonObject = [
                    'id' => $requestData['id'][$index],
                    'employee_id' => $requestData['employee_id'][$index],
                    'number' => $requestData['number'][$index],
                    'expiration_date' => $requestData['expiration_date'][$index],
                    'renew_duration' => $requestData['renew_duration'][$index],
                    'fees' => $requestData['fees'][$index],
                    'Payment_number' => $requestData['Payment_number'][$index],
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            if (!empty($jsonData)) {
                $business_id = $request->session()->get('user.business_id');
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();
                foreach ($selectedData as $data) {

                    $card = EssentialsWorkCard::with(['user.OfficialDocument'])->find($data['id']);

                    $renewStartDate = Carbon::parse($data['expiration_date']);
                    $renewEndDate = $renewStartDate->addMonths($data['renew_duration']);


                    if ($card) {

                        EssentialsResidencyHistory::create([
                            'worker_id' => $data['employee_id'],
                            'renew_start_date' => $data['expiration_date'],
                            'residency_number' => $data['number'],
                            'duration' => $data['renew_duration'],
                            'renew_end_date' => $renewEndDate,
                        ]);

                        $newDuration = $card->workcard_duration + $data['renew_duration'];

                        $card->update(['workcard_duration' => $newDuration]);


                        $card->update(['fees' => $data['fees']]);

                        $card->update(['Payment_number' => $data['Payment_number']]);


                        $document = EssentialsOfficialDocument::where('type', 'residence_permit')
                            ->where('employee_id', $data['employee_id'])
                            ->first();

                        $document->update(['expiration_date' => $renewEndDate]);
                    }
                }


                DB::commit();

                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return redirect()->route('cards')->with(['output']);
        // return $output;
    }



    public function getSelectedRowsData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');


        $data = EssentialsWorkCard::whereIn('id', $selectedRows)
            ->with([
                'user',
                'user.assignedTo.contact.responsibleClients',
                'user.OfficialDocument'
            ])

            ->select(
                'id',
                'employee_id',
                'work_card_no as card_no',
                'fees as fees',
                'workcard_duration',
                'Payment_number as Payment_number',
                'fixnumber as fixnumber'
            )->get();


        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),

        ];


        foreach ($data as $row) {
            $doc = $row->user->OfficialDocument
                ->where('type', 'residence_permit')
                ->first();
            $fixnumber =  $row->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')->first()->unified_number;

            $row->expiration_date = $doc ? $doc->expiration_date : null;
            $row->number = $doc ? $doc->number : null;
            $row->fixnumber = $fixnumber ?  $fixnumber : null;
        }

        return response()->json(['data' => $data, 'durationOptions' => $durationOptions]);
    }



    public function getResidencyData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $employeeId = $request->input('employee_id');

        $residencyData = User::where('users.id', '=', $employeeId)
            ->join('essentials_official_documents as doc', 'doc.employee_id', '=', 'users.id')
            ->select(
                'doc.id',
                'users.border_no as border_no',
                'users.id_proof_number as residency_no',
                'doc.expiration_date as residency_end_date'
            )->first();

        return response()->json($residencyData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function get_responsible_data(Request $request)
    {
        $employeeId = $request->get('employeeId');
        $business_id = request()->session()->get('user.business_id');

        $userType = User::where('id', $employeeId)->value('user_type');

        if ($userType !== 'worker') {
            $professionId = 56;

            $responsible_clients = User::whereHas('appointment', function ($query) use ($professionId) {
                $query->where('profession_id', $professionId);
            })
                ->select('id', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"))
                ->get();

            return response()->json([
                'all_responsible_users' => [
                    'id' => null,
                    'name' => trans('essentials::lang.management'),
                ],
                'responsible_client' => $responsible_clients,
            ]);
        } else {

            $projects = User::with(['assignedTo'])
                ->find($employeeId);

            $assignedProject = $projects->assignedTo;

            $projectName = $assignedProject->name ?? '';
            $projectId = $assignedProject->id ?? '';

            $all_responsible_users = [
                'id' => $projectId,
                'name' => $projectName,
            ];

            if (!$all_responsible_users) {
                return response()->json(['error' => 'No responsible users found for the given employee ID']);
            }

            $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
            $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
            $name_in_charge_choices = $all_users->pluck('full_name', 'id');

            $userIds = json_decode($projects->assignedTo->assigned_to, true);
            $assignedresponibleClient = [];

            if ($userIds) {
                foreach ($userIds as $user_id) {
                    $assignedresponibleClient[] = [
                        'id' => $user_id,
                        'name' => $name_in_charge_choices[$user_id],
                    ];
                }
            }


            $b_id = User::where('id', $employeeId)->select('business_id')->get();
            $business = Business::where('id', 1)->select('name as name', 'id as id')->get();

            return response()->json([
                'all_responsible_users' => $all_responsible_users,

                'responsible_client' => $assignedresponibleClient,
                'business' => $business,
            ]);
        }
    }



    public function create(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
       
    

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
     
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }
        $all_users = User::whereIn('id',$userIds)
           ->where(function ($query) {
            $query->whereNotNull('users.border_no')
                ->orWhere('users.id_proof_name', 'eqama');
        })
            ->where('users.user_type', 'worker')
            ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"), 'users.id')->get();

        $employees = $all_users->pluck('full_name', 'id');
        


        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];
        $business = Company::whereIn('id',$companies_ids)->pluck('name', 'id');

        return view('essentials::cards.create')
            ->with(compact(
                'employees',
                'business',
                'durationOptions'
            ));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // if (!auth()->user()->can('user.create')) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }
        try {
            $data = $request->only([

                'Residency_no',

                'project',
                'workcard_duration',
                'Payment_number',
                'fees',
                'company_name',
                'employee_id',


            ]);



            $business_id = request()->session()->get('user.business_id');

            $data['employee_id'] = (int)$request->input('employee_id');




            $lastrecord = EssentialsWorkCard::orderBy('work_card_no', 'desc')->first();

            if ($lastrecord) {

                $lastEmpNumber = (int)substr($lastrecord->work_card_no, 3);



                $nextNumericPart = $lastEmpNumber + 1;

                $data['work_card_no'] = 'WC' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
            } else {

                $data['work_card_no'] = 'WC' . '000';
            }


            $data['fixnumber'] = 700646447;

            $workcard = EssentialsWorkCard::create($data);





            $output = [
                'success' => 1,
                'msg' => __('user.user_added'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' =>__('messeages.somthing_went_wrong'),
            ];
        }


        return redirect()->route('cards');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
    }
}
