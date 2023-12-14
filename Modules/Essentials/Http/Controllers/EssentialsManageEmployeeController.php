<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleProject;
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


        //  $allowance= EssentialsAllowanceAndDeduction::where('id', 1)->find();

        $categories = EssentialsAllowanceAndDeduction::where('id', $salaryType)->select('amount')
            ->first();
        return response()->json($categories); // Return 0 if allowance not found
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        if (!($isSuperAdmin || auth()->user()->can('user.view') || auth()->user()->can('user.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $permissionName = 'essentials.view_profile_picture';


        if (!Permission::where('name', $permissionName)->exists()) {
            $permission = new Permission(['name' => $permissionName]);
            $permission->save();
        } else {

            $permission = Permission::where('name', $permissionName)->first();
        }
        // $userId = 1270;
        // $user = User::find($userId);

        // if ($user && $permission) {

        //     $user->givePermissionTo($permission);
        // }

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
        $business_id = request()->session()->get('user.business_id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        // $users = User::where('users.business_id', $business_id)->where('users.is_cmmsn_agnt', 0)
        $users = User::where('users.business_id', $business_id)->where('users.is_cmmsn_agnt', 0)
            ->whereIn('user_type', ['employee', 'manager'])
            ->leftjoin('essentials_employee_appointmets', 'essentials_employee_appointmets.employee_id', 'users.id')
            ->leftjoin('essentials_admission_to_works', 'essentials_admission_to_works.employee_id', 'users.id')
            ->leftjoin('essentials_employees_contracts', 'essentials_employees_contracts.employee_id', 'users.id')
            ->leftJoin('essentials_countries', 'essentials_countries.id', '=', 'users.nationality_id')
            ->select([
                'users.id',
                'users.emp_number',
                'users.username',
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
                'essentials_employee_appointmets.profession_id as profession_id',

                'essentials_employee_appointmets.specialization_id as specialization_id'
            ])->orderby('id', 'desc');

        $workers = User::where('users.is_cmmsn_agnt', 0)
            ->whereIn('user_type', ['worker'])
            ->leftjoin('essentials_employee_appointmets', 'essentials_employee_appointmets.employee_id', 'users.id')
            ->leftjoin('essentials_admission_to_works', 'essentials_admission_to_works.employee_id', 'users.id')
            ->leftjoin('essentials_employees_contracts', 'essentials_employees_contracts.employee_id', 'users.id')
            ->leftJoin('essentials_countries', 'essentials_countries.id', '=', 'users.nationality_id')
            ->select([
                'users.id',
                'users.emp_number',
                'users.username',
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
                'essentials_employee_appointmets.profession_id as profession_id',

                'essentials_employee_appointmets.specialization_id as specialization_id'
            ])->orderby('id', 'desc');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $userProjects = [];
        if (!$is_admin) {
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                $userProjects = array_merge($userProjects, $userProjectsForRole);
            }
            $userProjects = array_unique($userProjects);
            $workers = $workers->whereIn('assigned_to', $userProjects);
           
        }

        $users = $users->union($workers)->orderby('id', 'desc');


        if (!empty($request->input('specialization'))) {

            $users->where('essentials_employee_appointmets.specialization_id', $request->input('specialization'));
        }


        if (!empty($request->input('status-select'))) {
            $users->where('users.status', $request->input('status'));
        }

        if (!empty($request->input('location'))) {

            $users->where('users.location_id', $request->input('location'));
        }

        if (request()->ajax()) {


            return Datatables::of($users)

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
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
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
                ->removecolumn('id')
                ->rawColumns(['action', 'profession', 'specialization', 'view'])
                ->make(true);
        }

        $query = User::where('business_id', $business_id)->whereIn('user_type', ['employee', 'worker', 'manager']);;
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');


        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }
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
                'business_locations',
                'bl_attributes',
                'default_location'
            ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.create'))) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
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
        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = SalesProject::pluck('name', 'id');
        //  dd($contacts);
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
        return view('essentials::employee_affairs.employee_affairs.create')
            ->with(compact(
                'roles',
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

        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.create'))) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
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
        //Get user form part from modules
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.create'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }


            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;



            //emp_number
            $business_id = request()->session()->get('user.business_id');

            $numericPart = (int)substr($business_id, 3);
            $lastEmployee = User::orderBy('emp_number', 'desc')
                ->first();

            if ($user && $user->user_type == 'employee') {
                    $user = $user->where('business_id', $business_id);
                }
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.create'))) {
            abort(403, 'Unauthorized action.');
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $user = User::with(['contactAccess','OfficialDocument','proposal_worker'])
            ->select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"))
            ->find($id);
        // dd( $user);

        if ($user && $user->user_type == 'employee') {
            $user = $user->where('business_id', $business_id);
        }
    
        
        $documents = null;

        if($user)
        {
            if ($user->user_type == 'employee') {
           
                $documents = $user->OfficialDocument;
            } 
    
            else if ($user->user_type == 'worker') {
               
              
                if (!empty($user->proposal_worker_id)) {
                  
                  
                    $officialDocuments = $user->OfficialDocument;
                    $workerDocuments = $user->proposal_worker?->worker_documents;
                   // dd($officialDocuments);
                    $documents = $officialDocuments->merge($workerDocuments);
                } 
                else {
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
        // dd( $Qualification);

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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.update'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::with(['contactAccess' ,'assignedTo'])
            ->findOrFail($id);

        if ($user && $user->user_type == 'employee') {
                $user = $user->where('business_id', $business_id);
            }
        //dd( $user->assigned_to);
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
        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);


        $resident_doc = EssentialsOfficialDocument::select(['expiration_date', 'number'])->where('employee_id', $id)
            ->first();
        return view('essentials::employee_affairs.employee_affairs.edit')
            ->with(compact('projects','resident_doc', 'roles', 'banks', 'idProofName', 'user', 'blood_types', 'contact_access', 'is_checked_checkbox', 'locations', 'permitted_locations', 'form_partials', 'appointments', 'username_ext', 'contract_types', 'nationalities', 'specializations', 'professions'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';
        if (!($isSuperAdmin || auth()->user()->can('user.update'))) {
            abort(403, 'Unauthorized action.');
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



            //  $user_data['status'] = !empty($request->input('is_active')) ? 'active' : 'inactive';
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
            if ($user && $user->user_type == 'employee') {
                $user = $user->where('business_id', $business_id);
            }

            $user->update($user_data);



            //Update module fields for user
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
        //
    }

    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        foreach ($roles_array as $key => $value) {
            if (!$is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }
        return $roles;
    }
}
