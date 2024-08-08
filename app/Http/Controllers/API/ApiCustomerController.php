<?php

namespace App\Http\Controllers\API;

use App\AccessRole;
use App\AccessRoleCompany;
use App\Business;
use App\BusinessLocation;
use App\BusinessLocationPolygonMarker;
use App\Category;
use App\Company;
use App\Contact;
use App\RequestProcess;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\ToDo;
use Modules\Essentials\Utils\EssentialsUtil;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\salesContract;
use App\Request as UserRequest;
use App\RequestAttachment;
use App\SentNotification;
use App\SentNotificationsUser;
use Modules\CEOManagment\Entities\ProcedureTask;
use Modules\CEOManagment\Entities\RequestProcedureTask;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\UserLeaveBalance;
use Spatie\Permission\Models\Permission;

class ApiCustomerController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;
    protected $essentialsUtil;
    protected $commonUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, EssentialsUtil $essentialsUtil, Util $commonUtil)
    {
        $this->middleware('localization');
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */


    public function bills()
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id =  $user->crm_contact_id;
            $transactions = Transaction::where('contact_id', $contact_id)->get();
            $companies = Company::pluck('name', 'id');
            $bills = [];
            foreach ($transactions as $transaction) {
                if ($transaction->company_id) {
                    $tmp = User::where('id', $transaction->created_by)?->first();
                    $bills[] = [
                        'id' => $transaction->id,
                        'invoice_no' => $transaction->invoice_no,
                        'transaction_date' => $transaction->transaction_date,
                        'company' => $companies[$transaction->company_id],
                        'type' => $transaction->type,
                        'status' => $transaction->status,
                        'payment_status' => $transaction->payment_status,
                        "tax_amount" => $transaction->tax_amount,
                        "discount_amount" => $transaction->discount_amount,
                        'final_total' => $transaction->final_total,
                        'created_by' => ($tmp?->first_name ?? '') . ' ' . ($tmp?->mid_name ?? '') . ' ' . ($tmp?->last_name ?? ''),
                        'created_at' => Carbon::parse($transaction->created_at)->format(('Y-m-d')),
                    ];
                }
            }
            $res = [
                'bills' =>  $bills,
            ];




            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
    public function home()
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id =  $user->crm_contact_id;



            $contracts = salesContract::join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')
                ->select([
                    'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                    'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file',
                    'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
                ])->where('contact_id', $contact_id)->count();

            $SalesProjects = SalesProject::where('contact_id', $contact_id)->count();


            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $users = User::where('user_type', 'worker')
                ->whereIn('users.assigned_to', $projectsIds)
                ->where(function ($query) {
                    $query->where('status', 'active')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status', 'inactive')
                                ->whereIn('sub_status', ['vacation', 'escape', 'return_exit']);
                        });
                })
                ->pluck('id')
                ->unique()
                ->toArray();

            $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->whereNull('sub_status')->groupBy('request_id');
            $requestsProcess = UserRequest::select([

                'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.is_new', 'requests.created_at', 'requests.created_by', 'requests.reason',

                'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

                'wk_procedures.action_type as action_type', 'wk_procedures.department_id as department_id', 'wk_procedures.can_return', 'wk_procedures.start as start',

                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number', 'users.assigned_to',



            ])
                ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                    $join->on('requests.id', '=', 'latest_process.request_id');
                })
                ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
                ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
                ->leftjoin('procedure_tasks', 'procedure_tasks.procedure_id', '=', 'wk_procedures.id')
                ->leftjoin('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
                ->leftjoin('request_procedure_tasks', function ($join) {
                    $join->on('request_procedure_tasks.procedure_task_id', '=', 'procedure_tasks.id')
                        ->on('request_procedure_tasks.request_id', '=', 'requests.id');
                })
                ->leftJoin('users', 'users.id', '=', 'requests.related_to')

                ->whereIn('requests.related_to', $users)
                ->groupBy('requests.id')->orderBy('requests.created_at', 'desc')->count();





            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $workers = User::where('user_type', 'worker')->whereIn('users.assigned_to',  $projectsIds)
                ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
                ->with(['country', 'contract', 'OfficialDocument'])->select(
                    'users.id',
                    'users.*',
                    'users.id_proof_number',
                    'users.nationality_id',
                    'users.essentials_salary',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
                    'sales_projects.name as contact_name'
                )->count();



            $bills = Transaction::where('contact_id', $contact_id)->whereNotNull('company_id')->count();



            $res = [
                'workeres' =>   $workers,
                'contracts' =>   $contracts,
                'projects' => $SalesProjects,
                'requests' =>   $requestsProcess,
                'bills' => $bills,
                'first_name' =>   $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
            ];




            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function getCustomerInfo()
    {

        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $user = Auth::user();
            $user = User::where('id', $user->id)->first();
            $res = [
                'first_name' =>   $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function agentProjects()
    {
        try {
            // $business_id = request()->session()->get('user.business_id');
            $user = User::where('id', auth()->user()->id)->first();
            $business_id = $user->business_id;
            $contact_id =  $user->crm_contact_id;
            $SalesProjects = SalesProject::where('contact_id', $contact_id)->get();
            $cities = EssentialsCity::forDropdown();
            $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
            $all_users = $query->select('id', FacadesDB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
            $name_in_charge_choices = $all_users->pluck('full_name', 'id');

            $projects = [];
            foreach ($SalesProjects as   $SalesProject) {
                $projects[] = [
                    'id' => $SalesProject->id ?? '',
                    'project_name' => $SalesProject->name ?? '',
                    'location' => $SalesProject?->city ?  $cities[$SalesProject->city] : '',
                    'person_in_charge' => $SalesProject?->name_in_charge ? $name_in_charge_choices[$SalesProject->name_in_charge] : '',
                    'phone_of_person_in_charge' => $SalesProject?->phone_in_charge ?? '',
                    'email_of_person_in_charge' => $SalesProject?->email_in_charge ?? '',
                ];
            }
            $res = [
                'projects' => $projects,
            ];
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return $this->otherExceptions($e);
        }
    }

    public function agentContracts()
    {

        try {
            $contacts = Contact::all()->pluck('supplier_business_name', 'id');
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id =  $user->crm_contact_id;



            $contracts = salesContract::join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')
                ->select([
                    'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                    'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file',
                    'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
                ])->where('contact_id', $contact_id)->get();


            $cont = [];
            foreach ($contracts as $row) {
                $contract_form = '';
                if ($row->contract_form == 'monthly_cost') {
                    $contract_form = __('sales::lang.monthly_cost');
                } elseif ($row->contract_form == 'operating_fees') {
                    $contract_form = __('sales::lang.operating_fees');
                } else {
                    $contract_form = '';
                }
                $status = '';
                if ($row->status == 'valid') {
                    $status = __('sales::lang.valid');
                } else {
                    $status = __('sales::lang.finished');
                }
                $cont[] = [
                    'id' => $row->id,
                    'contact_id' => $contacts[$row->contact_id] ?? '',
                    'number_of_contract' => $row->number_of_contract,
                    'status' =>  $status,
                    'start_date' => $row->start_date,
                    'end_date' => $row->end_date,
                    'contract_form' =>  $contract_form,

                    'file_path' =>  asset('/uploads/' . $row->file),
                ];
            }

            $res = [
                'contracts' => $cont,
            ];
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return $this->otherExceptions($e);
        }
    }

    public function agentWorker()
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id =  $user->crm_contact_id;


            $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');

            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $users = User::where('user_type', 'worker')->whereIn('users.assigned_to',  $projectsIds)
                ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
                ->with(['country', 'contract', 'OfficialDocument'])->select(
                    'users.id',
                    'users.*',
                    'users.id_proof_number',
                    'users.nationality_id',
                    'users.essentials_salary',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
                    'sales_projects.name as contact_name'
                )->get();


            $worker = [];
            foreach ($users as $user) {

                $residencePermitDocument = $user->OfficialDocument
                    ->where('type', 'residence_permit')
                    ->first();
                if ($residencePermitDocument) {

                    $residencePermitDocument = $residencePermitDocument?->expiration_date ?? '';
                } else {

                    $residencePermitDocument = '';
                }

                $professionId = $appointments[$user->id] ?? '';
                $professionName = $professions[$professionId] ?? '';


                $status = $user->status;
                if ($status == 'active') {
                    $status = __('essentials::lang.active');
                } else if ($status == 'vecation') {
                    $status = __('essentials::lang.vecation');
                } else if ($status == 'inactive') {
                    $status = __('essentials::lang.inactive');
                } else if ($status == 'terminated') {
                    $status = __('essentials::lang.terminated');
                } else {
                    $status = '';
                }
                $gender = $user->gender;
                if ($gender == 'male') {
                    $gender = __('lang_v1.male');
                } else if ($gender == 'female') {
                    $gender = __('lang_v1.female');
                } else {
                    $gender = __('lang_v1.others');
                }

                $bank_details = json_decode($user->bank_details);
                $bank_code = $bank_details->bank_code ?? '';
                $worker[] = [
                    'nationality' => $user->country?->nationality ?? '',
                    'name' => $user->worker ?? '',
                    'residence_permit' => $this->getDocumentnumber($user, 'residence_permit'),
                    'contact_name' => $user->contact_name,
                    'residence_permit_expiration' => $residencePermitDocument,
                    'admissions_date' => $user->essentials_admission_to_works?->admissions_date ?? '',
                    'contract_end_date' => $user->contract?->contract_end_date ?? '',
                    'contact_number' => $user->contact_number ?? '',
                    'email' => $user->email,
                    'profession' => $professionName,
                    'status' =>  $status,
                    'salary' => $user->essentials_salary,
                    'total_salary' => $user->total_salary,
                    'gender' => $gender,
                    'marital_status' => $user->marital_status,
                    'blood_group' => $user->blood_group,
                    'bank_code' =>    $bank_code,
                ];
            }

            $res = [
                'worker' => $worker,
            ];
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return $this->otherExceptions($e);
        }
    }
    public function agentRequests()
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();

            $allRequestTypes = RequestsType::pluck('type', 'id');


            $saleProjects = SalesProject::all()->pluck('name', 'id');

            $contact_id =  $user->crm_contact_id;

            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $created_users = User::select(
                'id',
                DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name")
            )->pluck('full_name', 'id');
            // error_log($projectsIds[0]);
            $users = User::where('user_type', 'worker')
                ->whereIn('users.assigned_to', $projectsIds)
                ->where(function ($query) {
                    $query->where('status', 'active')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status', 'inactive')
                                ->whereIn('sub_status', ['vacation', 'escape', 'return_exit']);
                        });
                })
                ->pluck('id')
                ->unique()
                ->toArray();

            $all_users = User::whereIn('id', $users)
                ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))
                ->get();

            $all_users = $all_users->pluck('full_name', 'id');

            $requestsProcess = null;
            $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->whereNull('sub_status')->groupBy('request_id');

            $requestsProcess = UserRequest::select([

                'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.is_new', 'requests.created_at', 'requests.created_by', 'requests.reason',

                'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

                'wk_procedures.action_type as action_type', 'wk_procedures.department_id as department_id', 'wk_procedures.can_return', 'wk_procedures.start as start',

                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number', 'users.assigned_to',



            ])
                ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                    $join->on('requests.id', '=', 'latest_process.request_id');
                })
                ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
                ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
                ->leftjoin('procedure_tasks', 'procedure_tasks.procedure_id', '=', 'wk_procedures.id')
                ->leftjoin('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
                ->leftjoin('request_procedure_tasks', function ($join) {
                    $join->on('request_procedure_tasks.procedure_task_id', '=', 'procedure_tasks.id')
                        ->on('request_procedure_tasks.request_id', '=', 'requests.id');
                })
                ->leftJoin('users', 'users.id', '=', 'requests.related_to')

                ->whereIn('requests.related_to', $users)
                ->groupBy('requests.id')->orderBy('requests.created_at', 'desc');




            $requests = $requestsProcess->get();

            foreach ($requests as $request) {
                $tasksDetails = DB::table('request_procedure_tasks')
                    ->join('procedure_tasks', 'procedure_tasks.id', '=', 'request_procedure_tasks.procedure_task_id')
                    ->join('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
                    ->where('procedure_tasks.procedure_id', $request->procedure_id)
                    ->where('request_procedure_tasks.request_id', $request->id)
                    ->select('tasks.description', 'request_procedure_tasks.id', 'request_procedure_tasks.procedure_task_id', 'tasks.link', 'request_procedure_tasks.isDone', 'procedure_tasks.procedure_id')
                    ->get();


                $request->tasksDetails = $tasksDetails;
            }

            $requests_arr = [];
            $requestTypeMap = (array)[
                'exitRequest' => __('request.exitRequest'),
                'returnRequest' => __('request.returnRequest'),
                'escapeRequest' => __('request.escapeRequest'),
                'advanceSalary' => __('request.advanceSalary'),
                'leavesAndDepartures' => __('request.leavesAndDepartures'),
                'atmCard' => __('request.atmCard'),
                'residenceRenewal' => __('request.residenceRenewal'),
                'workerTransfer' => __('request.workerTransfer'),
                'residenceCard' => __('request.residenceCard'),
                'workInjuriesRequest' => __('request.workInjuriesRequest'),
                'residenceEditRequest' => __('request.residenceEditRequest'),
                'baladyCardRequest' => __('request.baladyCardRequest'),
                'mofaRequest' => __('request.mofaRequest'),
                'insuranceUpgradeRequest' => __('request.insuranceUpgradeRequest'),
                'chamberRequest' => __('request.chamberRequest'),
                'WarningRequest' => __('request.WarningRequest'),
                'cancleContractRequest' => __('request.cancleContractRequest'),
                'passportRenewal' => __('request.passportRenewal'),
                'AjirAsked' => __('request.AjirAsked'),
                'AlternativeWorker' => __('request.AlternativeWorker'),
                'TransferringGuaranteeFromExternalClient' => __('request.TransferringGuaranteeFromExternalClient'),
                'Permit' => __('request.Permit'),
                'FamilyInsurace' => __('request.FamilyInsurace'),
                'Ajir_link' => __('request.Ajir_link'),
                'ticketReservationRequest' => __('request.ticketReservationRequest'),
                'authorizationRequest' => __('request.authorizationRequest'),
                'salaryInquiryRequest' => __('request.salaryInquiryRequest'),
                'interviewsRequest' => __('request.interviewsRequest'),
                'moqimPrint' => __('request.moqimPrint'),
                'salaryIntroLetter' => __('request.salaryIntroLetter'),
                'QiwaContract' => __('request.QiwaContract'),
                'ExitWithoutReturnReport' => __('request.ExitWithoutReturnReport'),

            ];
            foreach ($requests as $row) {
                $tmp = '';
                if ($row->request_type_id) {
                    $tmp = $allRequestTypes[$row->request_type_id];
                }


                // Custom render logic based on request type

                $tmp = $requestTypeMap[$tmp] ?? '';



                $requests_arr[] = [
                    'request_no' => $row->request_no,
                    'user' => $row->user,
                    'id_proof_number' => $row->id_proof_number,
                    'assigned_to' => $row->assigned_to ? $saleProjects[$row->assigned_to] : '',
                    'request_type' => $tmp,
                    'created_at' => Carbon::parse($row->created_at),
                    'created_user' => $created_users[$row->created_by],
                    'status' => $row->status ? __('request.' . $row->status) : '',
                    'note' => $row->note,
                ];
            }

            $res = [
                'requests' => $requests_arr,
            ];
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return $this->otherExceptions($e);
        }
    }

    public function get_agent_request()
    {

        try {
            $user = User::where('id', auth()->user()->id)->first();

            $requestTypes = RequestsType::where('start_from_customer', 1)->where('for', 'worker')
                ->get()
                ->map(function ($requestType) {
                    return [
                        'id' => $requestType->id,
                        'type' => $requestType->type,
                        'for' => $requestType->for,
                    ];
                })
                ->toArray();

            $contact_id =  $user->crm_contact_id;

            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();

            $users = User::where('user_type', 'worker')
                ->whereIn('users.assigned_to', $projectsIds)
                ->where(function ($query) {
                    $query->where('status', 'active')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status', 'inactive')
                                ->whereIn('sub_status', ['vacation', 'escape', 'return_exit']);
                        });
                })
                ->pluck('id')
                ->unique()
                ->toArray();

            $all_users = User::whereIn('id', $users)
                ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))
                ->get();

            // $all_users = $all_users->pluck('full_name', 'id');

            $leaveTypes = EssentialsLeaveType::all();

            $classes = EssentialsInsuranceClass::all();

            $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->get();

            $saleProjects = SalesProject::all();

            $job_titles = EssentialsProfession::where('type', 'job_title')->get();

            $specializations = EssentialsSpecialization::all();

            $nationalities = EssentialsCountry::all();

            $res = [
                'type' => collect($requestTypes)->mapWithKeys(function ($requestType) {
                    return [

                        $requestType['id'] => [
                            'key' => $requestType['id'],
                            'value' => trans('request.' . $requestType['type']) . ' - ' . trans('request.' . $requestType['for']),
                        ],
                    ];
                })->toArray(),
                'user_id' => collect($all_users)->mapWithKeys(function ($user) {
                    return [

                        $user->id => [
                            'key' => $user->id,
                            'value' => $user->full_name,
                        ],
                    ];
                })->toArray(),
                // 'leaveType' => collect($leaveTypes)->mapWithKeys(function ($leaveType) {
                //     return [

                //         $leaveType->id => [
                //             'key' => $leaveType->id,
                //             'value' => $leaveType->leave_type,
                //         ],
                //     ];
                // })->toArray(),

                // 'resEditType' => [
                //     [
                //         'key' => 'name',
                //         'value' => __('request.name'),
                //     ],
                //     [
                //         'key' => 'religion',
                //         'value' => __('request.religion'),
                //     ]
                // ],
                // 'atmType' =>   [
                //     [
                //         'key' => 'release',
                //         'value' => __('request.release'),
                //     ],
                //     [
                //         'key' => 're_issuing',
                //         'value' => __('request.re_issuing'),
                //     ],
                //     [
                //         'key' => 'update',
                //         'value' => __('request.update_info'),
                //     ],

                // ],
                // 'baladyType' =>  [
                //     [
                //         'key' => 'renew',
                //         'value' =>  __('request.renew'),
                //     ],
                //     [
                //         'key' => 'issuance',
                //         'value' => __('request.issuance'),
                //     ],
                // ],
                'ins_class'  => collect($classes)->mapWithKeys(function ($classe) {
                    return [

                        $classe->id => [
                            'key' => $classe->id,
                            'value' => $classe->name,
                        ],
                    ];
                })->toArray(),
                // 'main_reason' => collect($main_reasons)->mapWithKeys(function ($main_reason) {
                //     return [

                //         $main_reason->id => [
                //             'key' => $main_reason->id,
                //             'value' => $main_reason->reason,
                //         ],
                //     ];
                // })->toArray(),
                // 'trip_type' => [
                //     [
                //         'key' => 'round',
                //         'value' =>  __('request.round_trip'),
                //     ],
                //     [
                //         'key' => 'one_way',
                //         'value' =>  __('request.one_way_trip'),
                //     ],
                // ],
                // 'project_name' => collect($saleProjects)->mapWithKeys(function ($saleProject) {
                //     return [

                //         $saleProject->id => [
                //             'key' => $saleProject->id,
                //             'value' => $saleProject->name,
                //         ],
                //     ];
                // })->toArray(),

                // 'interview_place' =>   [
                //     [
                //         'key' => 'online',
                //         'value' =>  __('request.online'),
                //     ],
                //     [
                //         'key' => 'housing',
                //         'value' => __('request.housing_place'),
                //     ],
                //     [
                //         'key' => 'company',
                //         'value' =>  __('request.company_place'),
                //     ],
                //     [
                //         'key' => 'customer',
                //         'value' =>  __('request.customer_place'),
                //     ],
                // ],
                // 'profession' => collect($specializations)->mapWithKeys(function ($specialization) {
                //     return [

                //         $specialization->id => [
                //             'key' => $specialization->id,
                //             'value' => $specialization->name,
                //         ],
                //     ];
                // })->toArray(),
                // 'job_title' => collect($job_titles)->mapWithKeys(function ($job_title) {
                //     return [

                //         $job_title->id => [
                //             'key' => $job_title->id,
                //             'value' => $job_title->name,
                //         ],
                //     ];
                // })->toArray(),
                // 'nationlity' => collect($nationalities)->mapWithKeys(function ($nationality) {
                //     return [

                //         $nationality->id => [
                //             'key' => $nationality->id,
                //             'value' => $nationality->nationality,
                //         ],
                //     ];
                // })->toArray(),


            ];
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage();
            return $this->otherExceptions($e);
        }
    }

    public function store_agent_request(Request $request)
    {
        DB::beginTransaction();

        try {

            $attachmentPath = $request->hasFile('attachment') ? $request->attachment->store('/requests_attachments') : null;
            // $duration = $request->duration ?? null;
            $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
            $end_date = $request->end_date ?? $request->return_date;
            $today = Carbon::today();
            $requestType = RequestsType::findOrFail($request->type);
            $type = $requestType->type;
            $customer_department = $requestType->customer_department;

            if ($this->isInvalidDateRange($type, $startDate, $end_date, $today)) {
                return redirect()->back()->withErrors([__('request.time_is_gone')]);
            }

            if ($type == 'leavesAndDepartures' && is_null($request->leaveType)) {
                return redirect()->back()->withErrors([__('request.please select the type of leave')]);
            }

            $createdByUser = auth()->user();
            $createdBy_type = $createdByUser->user_type;
            $userIds = explode(',', $request->user_id);
            foreach ($userIds as $userId) {
                if ($userId === null) continue;
                $business_id = User::where('id', $userId)->first()->business_id;
                if ($this->hasPendingRequest($userId, $request->type, $userIds)) {
                    return new CommonResource(['msg' => 'request.this_user_has_this_request_recently']);
                }

                if (!$this->processUserRequest($userId, $request, $type, $startDate, $end_date, $customer_department, $createdBy_type, $business_id, $attachmentPath)) {
                    DB::rollBack();
                    return new CommonResource(['msg' => 'messages.something_went_wrong']);
                }
            }

            DB::commit();
            return new CommonResource(['msg' => 'تم إنشاء الطلب بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }




    private function getDocumentnumber($user, $documentType)
    {
        foreach ($user->OfficialDocument as $off) {
            if ($off->type == $documentType) {
                return $off->number;
            }
        }

        return ' ';
    }

    public function updateUserInfo(Request $request)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            if ($request->otp == '1111') {
                $user = User::where('id', $user->id)->first();
                $res = [
                    'first_name' =>  $request->first_name ?? $user->first_name,
                    'mid_name' => $request->mid_name ?? $user->mid_name,
                    'last_name' => $request->last_name ?? $user->last_name,
                    'email' => $request->email ?? $user->email,
                    'contact_number' => $request->contact_number ?? $user->contact_number,
                    'updated_by' => auth()->user()->id
                ];

                $user->update($res);
                return new CommonResource($res);
            } else {
                throw new \Exception("The provided OTP is incorrect.");
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function resetPassword(Request $request)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            //  $user = Auth::user();

            if ($request->otp == '1111') {
                $user = User::where('contact_number', $request->phone)->first();
                if (!$user) {
                    throw new \Exception("no such user, register first.");
                }
                $user->update(['password' => Hash::make($request->new_password), 'updated_by' => auth()->user()->id]);
                return new CommonResource(['msg' => 'تم تغيير كلمة المرور بنجاح']);
            } else {
                throw new \Exception("The provided OTP is incorrect.");
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public  function changeToDoStatus(Request $request, $id)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $toDo = ToDo::find($id);
            $status = ['new', 'in_progress', 'on_hold', 'completed',];
            $toDo->update(['status' => $status[$request->status]]);
            return new CommonResource(['msg' => 'تم تغيير حالة المهمة بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function getPayrollDetails()
    {
        try {
            $user = Auth::user();
            $business_id = $user->business_id;



            $payrolls = Transaction::where('transactions.business_id', $business_id)
                ->where('type', 'payroll')->where('transactions.expense_for', $user->id)
                ->join('users as u', 'u.id', '=', 'transactions.expense_for')
                ->leftJoin('categories as dept', 'u.essentials_department_id', '=', 'dept.id')
                ->leftJoin('categories as dsgn', 'u.essentials_designation_id', '=', 'dsgn.id')
                ->leftJoin('essentials_payroll_group_transactions as epgt', 'transactions.id', '=', 'epgt.transaction_id')
                ->leftJoin('essentials_payroll_groups as epg', 'epgt.payroll_group_id', '=', 'epg.id')
                ->select([
                    'transactions.id as id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'final_total',
                    'transaction_date',
                    'ref_no',
                    'transactions.payment_status',
                    'dept.name as department',
                    'dsgn.name as designation',
                    'epgt.payroll_group_id',
                ])->get();
            $res = [];
            foreach ($payrolls as $payroll) {
                $payrollId = $payroll->id;
                $query = Transaction::where('business_id', $business_id)
                    ->with(['transaction_for', 'payment_lines']);


                $payroll = $query->findOrFail($payrollId);

                $transaction_date = Carbon::parse($payroll->transaction_date);

                $department = Category::where('category_type', 'hrm_department')
                    ->find($payroll->transaction_for->essentials_department_id);

                $designation = Category::where('category_type', 'hrm_designation')
                    ->find($payroll->transaction_for->essentials_designation_id);

                $location = BusinessLocation::where('business_id', $business_id)
                    ->find($payroll->transaction_for->location_id);

                $month_name = $transaction_date->format('F');
                $year = $transaction_date->format('Y');
                $allowances = !empty($payroll->essentials_allowances) ? json_decode($payroll->essentials_allowances, true) : [];
                $deductions = !empty($payroll->essentials_deductions) ? json_decode($payroll->essentials_deductions, true) : [];
                $bank_details = json_decode($payroll->transaction_for->bank_details, true);
                $payment_types = $this->moduleUtil->payment_types();
                $final_total_in_words = $this->commonUtil->numToIndianFormat($payroll->final_total);

                $start_of_month = Carbon::parse($payroll->transaction_date);
                $end_of_month = Carbon::parse($payroll->transaction_date)->endOfMonth();

                $leaves = EssentialsLeave::where('business_id', $business_id)
                    ->where('user_id', $payroll->transaction_for->id)
                    ->whereDate('start_date', '>=', $start_of_month)
                    ->whereDate('end_date', '<=', $end_of_month)
                    ->get();

                $total_leaves = 0;
                $days_in_a_month = Carbon::parse($start_of_month)->daysInMonth;
                foreach ($leaves as $key => $leave) {
                    $start_date = Carbon::parse($leave->start_date);
                    $end_date = Carbon::parse($leave->end_date);

                    $diff = $start_date->diffInDays($end_date);
                    $diff += 1;
                    $total_leaves += $diff;
                }

                $total_days_present = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee(
                    $business_id,
                    $payroll->transaction_for->id,
                    $start_of_month->format('Y-m-d'),
                    $end_of_month->format('Y-m-d')
                );

                $total_work_duration = $this->essentialsUtil->getTotalWorkDuration(
                    'hour',
                    $payroll->transaction_for->id,
                    $business_id,
                    $start_of_month->format('Y-m-d'),
                    $end_of_month->format('Y-m-d')
                );

                $res[] = [
                    'payroll' => $payroll,
                    'month_name' => $month_name,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'year' => $year,
                    'payment_types' => $payment_types,
                    'bank_details' => $bank_details,
                    'designation' => $designation,
                    'department' => $department,
                    'final_total_in_words' => $final_total_in_words,
                    'total_leaves' => $total_leaves,
                    'days_in_a_month' => $days_in_a_month,
                    'total_work_duration' => $total_work_duration,
                    'location' => $location,
                    'total_days_present' => $total_days_present
                ];
            }
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function clockin(Request $request)
    {
        // modified to not need a user_id, it can depend on the token
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $business = Business::findOrFail($business_id);
            $settings = $business->essentials_settings;
            $settings = !empty($settings) ? json_decode($settings, true) : [];
            $essentialsUtil = new \Modules\Essentials\Utils\EssentialsUtil;

            $data = [
                'business_id' => $business_id,
                // 'user_id' => $request->input('user_id'),
                'clock_in_time' => empty($request->input('clock_in_time')) ? \Carbon::now() : $request->input('clock_in_time'),
                'clock_in_note' => $request->input('clock_in_note'),
                'ip_address' => $request->input('ip_address'),
            ];
            $data['user_id'] = $user->id;
            // if (!empty($settings['is_location_required'])) {
            $long = $request->input('longitude');
            $lat = $request->input('latitude');

            if (empty($long) || empty($lat)) {
                throw new \Exception('Latitude and longitude are required');
            }

            $response = $essentialsUtil->getLocationFromCoordinates($lat, $long);

            if (!empty($response)) {
                $data['clock_in_location'] = $response;
            }
            $data['clock_in_lat'] = $lat;
            $data['clock_in_lng'] = $long;
            //  }

            $output = $essentialsUtil->clockin($data, $settings);

            if ($output['success']) {
                return $this->respond($output);
            } else {
                return $this->otherExceptions($output['msg']);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }





    /////////////////////////////////////////////////////
    private function hasPendingRequest($userId, $requestTypeId, $userIds)
    {
        $isExists = UserRequest::where('related_to', $userId)
            ->where('request_type_id', $requestTypeId)
            ->where('status', 'pending')
            ->first();

        return $isExists && count($userIds) == 1;
    }
    private function isInvalidDateRange($type, $startDate, $end_date, $today)
    {
        if ($startDate && $type != 'escapeRequest') {
            $startDateCarbon = Carbon::parse($startDate);
            if ($startDateCarbon->lt($today)) {
                return true;
            }
            if ($end_date) {
                $endDateCarbon = Carbon::parse($end_date);
                if ($startDateCarbon->gt($endDateCarbon)) {
                    return true;
                }
            }
        }
        return false;
    }
    private function getTypePrefix($request_type_id)
    {
        $prefix = RequestsType::where('id', $request_type_id)->first()->prefix;
        return $prefix;
    }
    public function generateRequestNo($request_type_id)
    {
        $type = RequestsType::where('id', $request_type_id)->first()->type;
        $RequestsTypes = RequestsType::where('type', $type)->pluck('id')->toArray();

        $latestRecord = UserRequest::whereIn('request_type_id', $RequestsTypes)->orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $prefix = $this->getTypePrefix($request_type_id);
            $numericPart = (int)substr($latestRefNo, strlen($prefix));
            $numericPart++;
            $input['request_no'] = $prefix . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $input['request_no'] = $this->getTypePrefix($request_type_id) . '0001';
        }

        return $input['request_no'];
    }
    private function createUserRequest($request, $userId, $startDate, $end_date, $attachmentPath)
    {
        return UserRequest::create([
            'request_no' => $this->generateRequestNo($request->type),
            'related_to' => $userId,
            'request_type_id' => $request->type,
            'start_date' => $startDate,
            'end_date' => $end_date,
            'reason' => $request->reason,
            'note' => $request->note,
            'attachment' => $attachmentPath,
            'essentials_leave_type_id' => $request->leaveType,
            'escape_time' => $request->escape_time,
            'installmentsNumber' => $request->installmentsNumber,
            'monthlyInstallment' => $request->monthlyInstallment,
            'advSalaryAmount' => $request->amount,
            'created_by' => auth()->user()->id,
            'insurance_classes_id' => $request->ins_class,
            'baladyCardType' => $request->baladyType,
            'resCardEditType' => $request->resEditType,
            'workInjuriesDate' => $request->workInjuriesDate,
            'contract_main_reason_id' => $request->main_reason,
            'contract_sub_reason_id' => $request->sub_reason,
            'visa_number' => $request->visa_number,
            'atmCardType' => $request->atmType,
            'authorized_entity' => $request->authorized_entity,
            'commissioner_info' => $request->commissioner_info,
            'trip_type' => $request->trip_type,
            'Take_off_location' => $request->Take_off_location,
            'destination' => $request->destination,
            'weight_of_furniture' => $request->weight_of_furniture,
            'date_of_take_off' => $request->date_of_take_off,
            'time_of_take_off' => $request->time_of_take_off,
            'return_date' => $request->return_date_of_trip,
            'job_title_id' => $request->job_title,
            'specialization_id' => $request->profession,
            'nationality_id' => $request->nationlity,
            'number_of_salary_inquiry' => $request->number_of_salary_inquiry,
            'sale_project_id' => $request->project_name,
            'interview_date' => $request->interview_date,
            'interview_time' => $request->interview_time,
            'interview_place' => $request->interview_place,
        ]);
    }
    private function validateContractCancellation($userId, $request, $userIds)
    {
        $contract = EssentialsEmployeesContract::where('employee_id', $userId)->firstOrFail();
        if (is_null($contract->wish_id)) {
            if (count($userIds) == 1) {
                return redirect()->back()->withErrors([__('request.no_wishes_found')]);
            }
            return false;
        }
        if (now()->diffInMonths($contract->contract_end_date) > 1) {
            if (count($userIds) == 1) {
                return redirect()->back()->withErrors([__('request.contract_expired')]);
            }
            return false;
        }
        return true;
    }
    private function validateLeaveBalance($userId, $request, $startDate, $end_date, $userIds)
    {
        $leaveBalance = UserLeaveBalance::where([
            'user_id' => $userId,
            'essentials_leave_type_id' => $request->leaveType,
        ])->first();

        if (!$leaveBalance || $leaveBalance->amount == 0) {
            if (count($userIds) == 1) {
                $messageKey = !$leaveBalance ? 'this_user_cant_ask_for_leave_request' : 'this_user_has_not_enough_leave_balance';
                return redirect()->back()->withErrors([__("request.$messageKey")]);
            }
            return false;
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        if ($daysRequested > $leaveBalance->amount) {
            if (count($userIds) == 1) {
                return redirect()->back()->withErrors([__("request.this_user_has_not_enough_leave_balance")]);
            }
            return false;
        }
        return true;
    }
    private function processUserRequest($userId, $request, $type, $startDate, $end_date, $customer_department, $createdBy_type, $business_id, $attachmentPath)
    {
        if ($type == "exitRequest") {
            $startDate = DB::table('essentials_employees_contracts')
                ->where('employee_id', $userId)
                ->first()
                ->contract_end_date ?? null;
        }

        if ($type == "leavesAndDepartures" && !$this->validateLeaveBalance($userId, $request, $startDate, $end_date, $request->user_id)) {
            return false;
        }

        if ($type == 'cancleContractRequest' && !$this->validateContractCancellation($userId, $request, $request->user_id)) {
            return false;
        }

        $newRequest = $this->createUserRequest($request, $userId, $startDate, $end_date, $attachmentPath);

        if ($attachmentPath) {

            RequestAttachment::create([
                'request_id' => $newRequest->id,
                'file_path' => $attachmentPath,
            ]);
        }

        return $this->processRequestProcedure($newRequest, $request->type, $business_id, $customer_department, $createdBy_type);
    }
    private function processRequestProcedure($request, $requestTypeId, $business_id, $customer_department, $createdBy_type)
    {
        $procedure = WkProcedure::where('business_id', $business_id)
            ->where('request_type_id', $requestTypeId)
            ->where('start', 1)
            ->where('department_id', $customer_department)
            ->first();
        if ($createdBy_type == 'manager' || $createdBy_type == 'admin') {
            $nextProcedure = WkProcedure::where('business_id', $business_id)
                ->where('request_type_id', $requestTypeId)
                ->where('department_id', $procedure->next_department_id)
                ->first();

            $process = RequestProcess::create([
                'request_id' => $request->id,
                'procedure_id' => $nextProcedure ? $nextProcedure->id : null,
                'status' => 'pending',
            ]);

            if ($nextProcedure && $nextProcedure->action_type == 'task') {
                $this->createRequestProcedureTasks($request->id, $nextProcedure->id);
            }
            $this->makeToDo($request, $business_id);
        } else {
            error_log($request->id);
            $process = RequestProcess::create([
                'request_id' => $request->id,
                'procedure_id' => $procedure ? $procedure->id : null,
                'status' => 'pending',
            ]);
            $this->makeToDo($request, $business_id);
        }

        if (!$process) {
            RequestAttachment::where('request_id', $request->id)->delete();
            $request->delete();
            return false;
        }


        return true;
    }
    private function createRequestProcedureTasks($requestId, $procedureId)
    {
        $procedureTasks = ProcedureTask::where('procedure_id', $procedureId)->get();
        foreach ($procedureTasks as $task) {
            RequestProcedureTask::create([
                'request_id' => $requestId,
                'procedure_task_id' => $task->id,
            ]);
        }
    }
    public function makeToDo($request, $business_id)
    {
        $created_by = $request->created_by;

        $request_type = RequestsType::where('id', $request->request_type_id)->first()->type;
        $input['business_id'] = $business_id;
        $input['company_id'] = $business_id;
        $input['created_by'] = $created_by;
        $input['task'] = "طلب جديد";
        $input['date'] = Carbon::now();
        $input['priority'] = 'high';
        $input['description'] = $request_type;
        $input['status'] = !empty($input['status']) ? $input['status'] : 'new';

        $process = RequestProcess::where('request_id', $request->id)->latest()->first();
        $users = [];
        $userCompanyId = User::where('id', $request->related_to)->first()->company_id;
        $acessRoleCompany = AccessRoleCompany::where('company_id', $userCompanyId)->pluck('access_role_id')->toArray();
        $rolesFromAccessRoles = AccessRole::whereIn('id', $acessRoleCompany)->pluck('role_id')->toArray();

        $procedure = $process->procedure_id;
        $department_id = WKProcedure::where('id', $procedure)->first()->department_id;
        $viewRequestPermission = $this->getViewRequestsPermission($department_id);
        if ($viewRequestPermission) {
            $permission_id = Permission::with('roles')->where('name', $viewRequestPermission)->first();
            $rolesIds = $permission_id->roles->pluck('id')->toArray();
            $users = User::whereHas('roles', function ($query) use ($rolesIds,  $rolesFromAccessRoles) {
                $query->whereIn('id', $rolesIds)->whereIn('id', $rolesFromAccessRoles);
            })->where('essentials_department_id', $department_id);
        }


        $input['task_id'] = $request->request_no;

        $to_dos = ToDo::create($input);
        $usersData = $users->get();

        $to_dos->users()->sync($usersData);


        $user_ids = $users->pluck('id')->toArray();
        $to =  $users->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
            ->pluck('full_name')->toArray();
        if (!empty($user_ids)) {
            $to = [];
            $userName = User::where('id', $request->related_to)->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
                ->pluck('full_name')->toArray()[0];
            $sentNotification = SentNotification::create([
                'via' => 'dashboard',
                'type' => 'GeneralManagementNotification',
                'title' =>  $input['task'],
                'msg' => __('request.' . $request_type) . ' ' . $userName,
                'sender_id' => auth()->user()->id,
                'to' => json_encode($to),
            ]);
            // $details = new stdClass();
            // $details->title =  $input['task'];
            // $details->message = $request_type;

            foreach ($user_ids as $user_id) {
                SentNotificationsUser::create([
                    'sent_notifications_id' => $sentNotification->id,
                    'user_id' => $user_id,
                ]);
                // User::where('id', $user_id)->first()?->notify(new GeneralNotification($details, false, true));
            }
        }
    }
    public function getViewRequestsPermission($department)
    {
        $departments = [
            'followup' => ['names' => ['%متابعة%'], 'permission' => 'followup.view_followup_requests'],
            'accounting' => ['names' => ['%حاسب%', '%مالي%'], 'permission' => 'accounting.view_accounting_requests'],
            'workcard' => ['names' => ['%حكومية%'], 'permission' => 'essentials.view_workcards_request'],
            'hr' => ['names' => ['%بشرية%'], 'permission' => 'essentials.view_HR_requests'],
            'employee_affairs' => ['names' => ['%موظف%'], 'permission' => 'essentials.view_employees_affairs_requests'],
            'insurance' => ['names' => ['%تأمين%'], 'permission' => 'essentials.crud_insurance_requests'],
            'payroll' => ['names' => ['%رواتب%'], 'permission' => 'essentials.view_payroll_requests'],
            'housing' => ['names' => ['%سكن%'], 'permission' => 'housingmovements.crud_htr_requests'],
            'international_relations' => ['names' => ['%دولي%'], 'permission' => 'internationalrelations.view_ir_requests'],
            'legal' => ['names' => ['%قانوني%'], 'permission' => 'legalaffairs.view_legalaffairs_requests'],
            'sales' => ['names' => ['%مبيعات%'], 'permission' => 'sales.view_sales_requests'],
            'ceo' => ['names' => ['%تنفيذ%'], 'permission' => 'ceomanagment.view_CEO_requests'],
            'general' => ['names' => ['%مجلس%', '%عليا%'], 'permission' => 'generalmanagement.view_president_requests']
        ];

        foreach ($departments as $dept => $info) {
            $deptIds = EssentialsDepartment::where(function ($query) use ($info) {
                foreach ($info['names'] as $name) {
                    $query->orWhere('name', 'like', $name);
                }
            })->pluck('id')->toArray();

            if (in_array($department, $deptIds)) {

                return $info['permission'];
            }
        }

        return null;
    }
}
