<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\BusinessLocation;
use App\User;
use App\ContactLocation;
use App\Category;
use App\Transaction;
use App\Contact;
use Modules\Sales\Entities\salesContractItem;
use DB;
use Spatie\Permission\Models\Permission;
use Modules\Essentials\Http\RequestsempRequest;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Events\UserCreatedOrModified;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Sales\Entities\SalesProject;
use Modules\Followup\Entities\FollowupWorkerRequest;



class EssentialsManageEmployeeController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function getAmount($salaryType)
    {




        $categories = EssentialsAllowanceAndDeduction::where('id', $salaryType)->select('amount')
            ->first();
        return response()->json($categories);
    }

    public function fetch_user($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)

            ->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */


    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.view') || auth()->user()->can('user.create'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        $permissionName = 'essentials.view_profile_picture';


        if (!Permission::where('name', $permissionName)->exists()) {
            $permission = new Permission(['name' => $permissionName]);
            $permission->save();
        } else {

            $permission = Permission::where('name', $permissionName)->first();
        }

        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $EssentialsProfession = EssentialsProfession::all()->pluck('name', 'id');
        $EssentialsSpecialization = EssentialsSpecialization::all()->pluck('name', 'id');
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');

        $contract = EssentialsEmployeesContract::all()->pluck('contract_end_date', 'id');


        // $users = User::where('users.business_id', $business_id)->where('users.is_cmmsn_agnt', 0)
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();
        $user_projects_ids = SalesProject::all('id')->unique()->toArray();
        if (!$is_admin) {
            $userProjects = [];
            $userBusinesses = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();
               
                if( $accessRole ){

                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
                            
            }
            $user_projects_ids = array_unique($userProjects);
            $user_businesses_ids = array_unique($userBusinesses);
        }

        $users = User::with(['userAllowancesAndDeductions'])->where(function ($query) use ($user_businesses_ids, $user_projects_ids) {
            $query->where(function ($query2) use ($user_businesses_ids) {
                $query2->whereIn('users.business_id', $user_businesses_ids)->whereIn('user_type', ['employee', 'manager','worker']);
            })->orWhere(function ($query3) use ($user_projects_ids) {
                $query3->where('user_type', 'worker')->whereIn('assigned_to', $user_projects_ids);
            });
        })->where('users.is_cmmsn_agnt', 0)

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
                'essentials_employee_appointmets.profession_id as profession_id',

                'essentials_employee_appointmets.specialization_id as specialization_id'
            ])->orderby('id', 'desc');



        // $userProjects = [];
        // if (!$is_admin) {
        //     $roles = auth()->user()->roles;
        //     foreach ($roles as $role) {

        //         $accessRole = AccessRole::where('role_id', $role->id)->first();

        //         $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
        //         $userProjects = array_merge($userProjects, $userProjectsForRole);
        //     }
        //     $userProjects = array_unique($userProjects);
        //     $users = $users->whereIn('assigned_to', $userProjects)
        //         ->orWhere(function ($query) use ($business_id) {
        //             $query->whereNull('assigned_to')->where('users.business_id', $business_id)->whereIn('user_type', ['employee', 'manager']);
        //         });
        // }

        // $users = $users->union($workers)->orderby('id', 'desc');


        if (!empty($request->input('specialization'))) {

            $users->where('essentials_employee_appointmets.specialization_id', $request->input('specialization'));
        }


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



                ->addColumn('specialization', function ($row) use ($appointments2, $specializations) {
                    $specializationId = $appointments2[$row->id] ?? '';
                    $specializationName = $specializations[$specializationId] ?? '';

                    return $specializationName;
                })





                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                        <li>
                                        <a href="#" class="btn-modal1"  data-toggle="modal" data-target="#addQualificationModal"  data-row-id="' . $row->id . '"  data-row-name="' . $row->full_name . '"  data-href=""><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.add_qualification') . '</a>
                                     
                                        </a>
                                        </li>';





                        $html .= '<li>
                                    <a href="#" class="btn-modal2"  data-toggle="modal" data-target="#add_doc"  data-row-id="' . $row->id . '"  data-row-name="' . $row->full_name . '"  data-href=""><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.add_doc') . '</a>
                                </li>';

                        $html .= '<li>
                                <a class=" btn-modal3" data-toggle="modal" data-target="#addContractModal"><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.add_contract') . '</a>
                            </li>';

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->addColumn('view', function ($row) {

                    $html = '<a href="' . route('showEmployee', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';

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
                ->rawColumns(['user_type', 'business_id', 'action', 'profession', 'specialization', 'view'])
                ->make(true);
        }

        $query = User::where('business_id', $business_id)->whereIn('user_type', ['employee', 'worker', 'manager']);;
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');


        $businesses = Business::forDropdown();
        // $bl_attributes = $business_locations['attributes'];
        // $business_locations = $business_locations['locations'];

        // $default_location = null;
        // foreach ($business_locations as $id => $name) {
        //     $default_location = BusinessLocation::findOrFail($id);
        //     break;
        // }
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

        return view('essentials::employee_affairs.employee_affairs.index')
            ->with(compact(
                'contract_types',
                'nationalities',
                'specializations',
                'professions',
                'users',
                'countries',
                'spacializations',
                'status',
                'offer_prices',
                'items',
                'businesses',
                // 'bl_attributes',
                // 'default_location'
            ));
    }


    public function employee_affairs_dashboard()
    {
        $today = now();
        $endDateThreshold = $today->copy()->addDays(14);
        
        $probation_period = EssentialsEmployeesContract::where('probation_period', 3)
            ->where(function ($query) use ($today) {
                $query->whereDate('contract_start_date', '<=', $today)
                      ->orWhereNull('contract_start_date'); 
            })
            ->whereDate(DB::raw('DATE_ADD(contract_start_date, INTERVAL probation_period MONTH)'), '>', $endDateThreshold)
            ->count();


            $today = now();
            $endDateThreshold = $today->copy()->addDays(60);

            $contract_end_date = EssentialsEmployeesContract::
                where(function ($query) use ($today) {
                    $query->whereDate('contract_start_date', '<=', $today)
                        ->orWhereNull('contract_start_date'); 
                })
                ->whereDate('contract_end_date', '<=', $endDateThreshold)
                ->count();

        $late_vacation = FollowupWorkerRequest::with(['user'])
                ->where('type', 'leavesAndDepartures')
                ->where('type', 'returnRequest') 
                ->whereHas('user', function ($query)  {
                    
                    $query->where('status', 'vecation');
                })
                ->where('end_date', '<', now()) 
                ->count();
             
        $nullCount = EssentialsAdmissionToWork::
                 orWhereNull('admissions_date')
              
                ->count();
            
            $nullCount += EssentialsEmployeeAppointmet::
                orWhereNull('start_from')
                ->orWhereNull('end_at')
                ->orWhereNull('profession_id')
                ->orWhereNull('specialization_id')
                // Add more columns as needed
                ->count();
            
            $nullCount += EssentialsEmployeesQualification::
                orWhereNull('graduation_year')
                ->orWhereNull('graduation_institution')
                ->orWhereNull('graduation_country')
                ->orWhereNull('degree')
                
                // Add more columns as needed
                ->count();
             
          return view('essentials::employee_affairs.dashboard')
          ->with(compact('probation_period',
          'contract_end_date' ,'late_vacation','nullCount'));   
    }


    public function finsish_contract_duration()
    {
        $today = now();
        $endDateThreshold = $today->copy()->addDays(14);
        
        $probation_period = EssentialsEmployeesContract::with('user')
       
        ->where('probation_period', 3)
            ->where(function ($query) use ($today) {
                $query->whereDate('contract_start_date', '<=', $today)
                      ->orWhereNull('contract_start_date'); 
            })
            ->whereDate(DB::raw('DATE_ADD(contract_start_date, INTERVAL probation_period MONTH)'), '>', $endDateThreshold)
            ->select('contract_end_date' ,'employee_id');


       // dd( $residencies->first());

        if (request()->ajax()) {

        return DataTables::of($probation_period)
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
                    return $row->contract_end_date;
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
            
        return view('essentials::employee_affairs.statistics.finsish_contract_duration');
     }

     public function finish_contracts()
     {
        $today = now();
        $endDateThreshold = $today->copy()->addDays(60);

        $contract_end_date = EssentialsEmployeesContract::with(['user'])
        ->whereHas('user', function ($query) {
            $query->where('user_type', 'worker');
        })
            ->whereDate('contract_end_date', '<=', $endDateThreshold)
            ->select('contract_end_date' ,'employee_id');
 
   //  dd( $contract_end_date->first());
 
         if (request()->ajax()) {
 
         return DataTables::of($contract_end_date)
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
                     return $row->user?->assignedTo?->contact?->supplier_business_name ?? null;
                 }
             )
             ->addColumn(
                 'end_date',
                 function ($row) {
                     return $row->contract_end_date;
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
             
         return view('essentials::employee_affairs.statistics.finish_contracts');
      }

      public function uncomplete_profiles()
      {
        
        $usersWithNullAdmission = User::whereHas('essentials_admission_to_works', function ($query) {
            
            $query->whereNull('admissions_date');
        })
        ->whereHas('essentialsEmployeeAppointmets', function ($query) {
            
            $query->WhereNull('start_from')
            ->orWhereNull('end_at')
            ->orWhereNull('profession_id')
            ->orWhereNull('specialization_id');
        })
        ->whereHas('essentials_qualification', function ($query) {
            
            $query->WhereNull('graduation_year')
            ->orWhereNull('graduation_institution')
            ->orWhereNull('graduation_country')
            ->orWhereNull('degree');
            
        })
        ->get();
       // dd($usersWithNullAdmission);

        if (request()->ajax()) {

        return DataTables::of($usersWithNullAdmission)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->first_name . ' ' . $row->last_name ?? '';
                }
            )
           
            ->addColumn(
                'project',
                function ($row) {
                    return $row->assignedTo?->contact?->supplier_business_name ?? null;
                }
            )
            ->addColumn(
                'customer_name',
                function ($row) {
                    return $row->assignedTo?->contact->supplier_business_name ?? null;
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
            
        return view('essentials::employee_affairs.statistics.uncomplete_profies');
      }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');


        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }

        $roles = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $locations = BusinessLocation::where('business_id', $business_id)
            ->Active()
            ->get();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = SalesProject::pluck('name', 'id');

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



        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $countries = $countries = EssentialsCountry::forDropdown();
        $resident_doc = null;
        $user = null;
        return view('essentials::employee_affairs.employee_affairs.create')
            ->with(compact(
                'roles',
                'countries',
                'spacializations',
                'nationalities',
                'username_ext',
                'blood_types',
                'contacts',
                'locations',
                'banks',
                'contract_types',
                'form_partials',
                'resident_doc',
                'user'
            ));
    }



    public function createWorker($id)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');


        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }

        $roles = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $locations = BusinessLocation::where('business_id', $business_id)
            ->Active()
            ->get();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contact = Contact::find($id);

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
        return view('followup::workers.create')
            ->with(compact(
                'roles',
                'nationalities',
                'username_ext',
                'blood_types',
                'contact',
                'locations',
                'banks',
                'contract_types',
                'form_partials',
                'resident_doc',
                'user'
            ));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        try {
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }


            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;




            $business_id = request()->session()->get('user.business_id');

            $numericPart = (int)substr($business_id, 3);
            $lastEmployee = User::orderBy('emp_number', 'desc')
                ->first();


            if ($lastEmployee) {

                $lastEmpNumber = (int)substr($lastEmployee->emp_number, 3);



                $nextNumericPart = $lastEmpNumber + 1;

                $request['emp_number'] = $business_id . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
            } else {

                $request['emp_number'] =  $business_id . '000';
            }


            $existingprofnumber = User::where('id_proof_number', $request->input('id_proof_number'))->first();

            if ($existingprofnumber) {
                $errorMessage = trans('essentials::lang.user_with_same_id_proof_number_exists');
                throw new \Exception($errorMessage);
            }

            $user = $this->moduleUtil->createUser($request);

            event(new UserCreatedOrModified($user, 'added'));

            $output = [
                'success' => 1,
                'msg' => __('user.user_added'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('employees')->with('status', $output);
    }


    public function storeWorker(Request $request)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        try {
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;

            $business_id = request()->session()->get('user.business_id');

            $numericPart = (int)substr($business_id, 3);
            $lastEmployee = User::where('business_id', $business_id)
                ->orderBy('emp_number', 'desc')
                ->first();

            if ($lastEmployee) {

                $lastEmpNumber = (int)substr($lastEmployee->emp_number, 3);

                $nextNumericPart = $lastEmpNumber + 1;

                $request['emp_number'] = $business_id . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
            } else {

                $request['emp_number'] =  $business_id . '000';
            }


            $user = $this->moduleUtil->createUser($request);

            event(new UserCreatedOrModified($user, 'added'));

            $output = [
                'success' => 1,
                'msg' => __('user.user_added'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('projects')->with('status', $output);
    }

    public function show($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.view'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $user = User::with(['contactAccess', 'OfficialDocument', 'proposal_worker'])
            ->select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))
            ->find($id);




        $documents = null;

        if ($user) {
            if ($user->user_type == 'employee') {

                $documents = $user->OfficialDocument;
            } else if ($user->user_type == 'worker') {


                if (!empty($user->proposal_worker_id)) {


                    $officialDocuments = $user->OfficialDocument;
                    $workerDocuments = $user->proposal_worker?->worker_documents;

                    $documents = $officialDocuments->merge($workerDocuments);
                } else {
                    $documents = $user->OfficialDocument;
                }
            }
        }


        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();


        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('profession_id');

        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)->name;
        } else {
            $profession = "";
        }

        $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        if ($specializationId !== null) {
            $specialization = EssentialsSpecialization::find($specializationId)->name;
        } else {
            $specialization = "";
        }


        $user->profession = $profession;
        $user->specialization = $specialization;


        $view_partials = $this->moduleUtil->getModuleData(
            'moduleViewPartials',
            ['view' => 'manage_user.show', 'user' => $user]
        );

        $users = User::forDropdown($business_id, false);

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



        return view('essentials::employee_affairs.employee_affairs.show')->with(compact(
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

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.update'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::with(['contactAccess', 'assignedTo'])
            ->findOrFail($id);

        $contacts = SalesProject::pluck('name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $projects = SalesProject::pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::select([

            'profession_id',
            'specialization_id'
        ])->where('employee_id', $id)
            ->first();
        if ($appointments !== null) {
            $user->profession_id = $appointments['profession_id'];
            $user->specialization_id = $appointments['specialization_id'];
        } else {
            $user->profession_id = null;
            $user->specialization_id = null;
        }
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

        $idProofName = $user->id_proof_name;
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $roles = $this->getRolesArray($business_id);
        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $contract = EssentialsEmployeesContract::where('employee_id', '=', $user->id)->select('*')->get();

        $specializations = EssentialsSpecialization::all()->pluck('id', 'name');
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('id', 'name');
        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        $locations = BusinessLocation::where('business_id', $business_id)
            ->get();

        $permitted_locations = $user->permitted_locations();
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);

        $qualification = EssentialsEmployeesQualification::where('employee_id', $id)->first();

        $resident_doc = EssentialsOfficialDocument::select(['expiration_date', 'number'])->where('employee_id', $id)
            ->first();
        return view('essentials::employee_affairs.employee_affairs.edit')
            ->with(compact(
                'projects',
                'contacts',
                'spacializations',
                'qualification',
                'resident_doc',
                'countries',
                'roles',
                'banks',
                'idProofName',
                'user',
                'blood_types',
                'contact_access',
                'is_checked_checkbox',
                'locations',
                'permitted_locations',
                'form_partials',
                'appointments',
                'username_ext',
                'contract_types',
                'nationalities',
                'specializations',
                'professions'
            ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.update'))) {
           //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $user_data = $request->only([
                'surname', 'first_name', 'last_name', 'email', 'selected_contacts', 'marital_status', 'border_no', 'bank_details',
                'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1', 'location_id',
                'social_media_2', 'permanent_address', 'current_address', 'profession', 'specialization',

                'guardian_name', 'custom_field_1', 'custom_field_2', 'nationality', 'contract_type', 'contract_start_date', 'contract_end_date',
                'contract_duration', 'probation_period',
                'is_renewable', 'contract_file', 'essentials_salary', 'essentials_pay_period',
                'salary_type', 'amount', 'can_add_category',
                'travel_ticket_categorie', 'health_insurance', 'selectedData',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'essentials_department_id',
                'max_sales_discount_percent', 'family_number', 'alt_number',

            ]);




            $business_id = request()->session()->get('user.business_id');
            if (!isset($user_data['selected_contacts'])) {
                $user_data['selected_contacts'] = 0;
            }
            if (empty($request->input('allow_login'))) {
                $user_data['username'] = null;
                $user_data['password'] = null;
                $user_data['allow_login'] = 0;
            } else {
                $user_data['allow_login'] = 1;
            }

            if (!empty($request->input('password'))) {
                $user_data['password'] = $user_data['allow_login'] == 1 ? Hash::make($request->input('password')) : null;
            }

            $user_data['cmmsn_percent'] = !empty($user_data['cmmsn_percent']) ? $this->moduleUtil->num_uf($user_data['cmmsn_percent']) : 0;

            $user_data['max_sales_discount_percent'] = null;
            if (!empty($request->input('dob'))) {
                $user_data['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }
            if (!empty($request->input('border_no'))) {
                $user_data['border_no'] = $request->input('border_no');
            }
            if (!empty($request->input('nationality'))) {
                $user_data['nationality_id'] = $request->input('nationality');
            }
            if (!empty($request->input('bank_details'))) {
                $user_data['bank_details'] = json_encode($request->input('bank_details'));
            }
            if (!empty($request->input('has_insurance'))) {
                $user_data['has_insurance'] = json_encode($request->input('has_insurance'));
            }

            DB::beginTransaction();
            if ($user_data['allow_login'] && $request->has('username')) {
                $user_data['username'] = $request->input('username');
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('username');
                if (blank($user_data['username'])) {
                    $user_data['username'] = $this->moduleUtil->generateReferenceNumber('username', $ref_count);
                }

                $username_ext = $this->moduleUtil->getUsernameExtension();
                if (!empty($username_ext)) {
                    $user_data['username'] .= $username_ext;
                }
            }

            $user = User::findOrFail($id);


            $user->update($user_data);




            $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_updated', 'model_instance' => $user, 'request' => $user_data]);

            $this->moduleUtil->activityLog($user, 'edited', null, ['name' => $user->user_full_name]);

            event(new UserCreatedOrModified($user, 'updated'));

            $output = [
                'success' => 1,
                'msg' => __('user.user_update_success'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('employees')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
    }

    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        foreach ($roles_array as $key => $value) {
            if (!$is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }
        return $roles;
    }
}
