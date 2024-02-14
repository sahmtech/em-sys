<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\AccessRoleCompany;
use App\Company;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\CEOManagment\Entities\RequestsType;

use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use App\Request as UserRequest;

use App\Business;

use App\User;


use Carbon\Carbon;

use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsResidencyHistory;
use Modules\Essentials\Entities\EssentialsWorkCard;
use Modules\Essentials\Entities\EssentialsOfficialDocument;

use App\AccessRole;

use Spatie\Permission\Models\Permission;

use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;

use Modules\Essentials\Entities\EssentailsEmployeeOperation;

use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Sales\Entities\salesContractItem;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use App\Category;
use App\Transaction;




class EssentialsCardsController extends Controller
{

    protected $moduleUtil;

    protected $requestUtil;

    public function __construct(ModuleUtil $moduleUtil,RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;

   
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
        $can_change_status = auth()->user()->can('essentials.workcards_requests_change_status');
        $can_return_request = auth()->user()->can("essentials.return_workcards_request");
        $can_show_request = auth()->user()->can("essentials.show_workcards_request");


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

        $ownerTypes=['worker'];

        return $this->requestUtil->getRequests( $departmentIds, $ownerTypes, 'essentials::cards.allrequest' , $can_change_status, $can_return_request, $can_show_request);
 
    
    }
   
   
    public function storeRequest(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
      
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%حكومية%')
        ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
       
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

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
  
      
        $late_vacation=[];
        $type = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first();
            if ($type) {
                $late_vacation = UserRequest::with(['related_to_user'])->whereIn('related_to', $userIds)
                    ->where('request_type_id', $type->id)
                    ->whereHas('related_to_user', function ($query) {
                        $query->where('status', 'vecation');
                    })
                    ->where('end_date', '<', now())
                    ->select('end_date')->count();
            }



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
              
                    function ($row) {
                       $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                        return $html;
                   }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'project', 'end_date','customer_name', 'action'])
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
                   
                    //$user->update(['status' ,'inactive']);
                    $user->status='inactive';
                    $user->save();

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
        return response()->json( $output );
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
                    $user->status='inactive';
                    $user->save();
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

        $can_change_status = auth()->user()->can('essentials.workcards_requests_change_status');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%دولي%')
            ->pluck('id')->toArray();

            if (empty($departmentIds)) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.there_is_no_governmental_dep'),
                ];
                return redirect()->back()->with('status', $output);
            }
    
            $ownerTypes=['employee','manager'];
    
            return $this->requestUtil->getRequests( $departmentIds, $ownerTypes, 'essentials::cards.vactionrequest' , $can_change_status);
       
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

  
}
