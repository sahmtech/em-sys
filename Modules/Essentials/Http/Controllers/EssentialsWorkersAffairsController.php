<?php

namespace Modules\Essentials\Http\Controllers;

use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use App\Category;
use App\AccessRole;
use App\AccessRoleCompany;
use App\AccessRoleProject;
use App\Contact;
use App\ContactLocation;
use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\User;
use App\Company;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Events\UserCreatedOrModified;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Storage;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;

class EssentialsWorkersAffairsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_workcards_indexWorkerProjects = auth()->user()->can('essentials.view_essentials_affairs_workers');
        if (!($is_admin || $can_workcards_indexWorkerProjects)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }


        $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();

        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields_hrm();
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
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

        $users = User::whereIn('users.id', $userIds)
            ->with(['assignedTo'])
            ->where('user_type', 'worker')
            ->where('users.status', '!=', 'inactive')
            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);

        $users->select(
            'users.*',
            'users.id as worker_id',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ',COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        )
            ->orderBy('users.id', 'desc')
            ->groupBy('users.id');

        if (!empty(request()->input('company')) && request()->input('company') !== 'all') {

            $users =  $users->where('users.company_id', request()->input('company'));
        }
        if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

            if (request()->input('project_name') == 'none') {
                $users = $users->whereNull('users.assigned_to');
            } else {
                $users = $users->where('users.assigned_to', request()->input('project_name'));
            }
        }

        if (!empty(request()->input('status_fillter')) && request()->input('status_fillter') !== 'all') {

            $users = $users->where('users.status', request()->input('status_fillter'));
        }

        if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
            $start = request()->filter_start_date;
            $end = request()->filter_end_date;

            $users->whereHas('contract', function ($query) use ($start, $end) {
                $query->whereDate('contract_end_date', '>=', $start)
                    ->whereDate('contract_end_date', '<=', $end);
            });
        }
        if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {

            $users = $users->where('users.nationality_id', request()->nationality);
        }
        // return $users->where('users.id_proof_number',2222222222)->first()->essentials_admission_to_works;
        if (request()->ajax()) {

            return DataTables::of($users)
                ->addColumn('worker_id', function ($user) {
                    return $user->worker_id ?? ' ';
                })

                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                })

                ->addColumn('residence_permit_expiration', function ($user) {
                    $residencePermitDocument = $user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();
                    if ($residencePermitDocument) {

                        return optional($residencePermitDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('passport_number', function ($user) {
                    $passportDocument = $user->OfficialDocument
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($user) {
                    $passportDocument = $user->OfficialDocument
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })->addColumn('company_name', function ($user) {
                    return optional($user->company)->name ?? ' ';
                })

                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })
                ->addColumn('admissions_date', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_date ?? ' ';
                })
                ->addColumn('admissions_type', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_type ?? ' ';
                })
                ->addColumn('admissions_status', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_status ?? ' ';
                })
                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })

                ->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';

                    return $professionName;
                })

                ->addColumn('bank_code', function ($user) {

                    $bank_details = json_decode($user->bank_details);
                    return $bank_details->bank_code ?? ' ';
                })
                ->addColumn('contact_name', function ($user) {

                    return $user->assignedTo->name ?? '';
                })
                ->addColumn('dob', function ($user) {

                    return $user->dob ?? '';
                })->addColumn('insurance', function ($user) {
                    if ($user->essentialsEmployeesInsurance && $user->essentialsEmployeesInsurance->is_deleted == 0) {
                        return __('followup::lang.has_insurance');
                    } else {
                        return __('followup::lang.has_not_insurance');
                    }
                })
                ->addColumn('categorie_id', function ($row) use ($travelCategories) {
                    $item = $travelCategories[$row->categorie_id] ?? '';

                    return $item;
                })
                ->filterColumn('worker', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('residence_permit', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['contact_name', 'worker_id', 'company_name', 'passport_number', 'passport_expire_date', 'worker', 'categorie_id', 'admissions_status', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        $companies = Company::whereIn('id', $companies_ids)->pluck('name', 'id');
        return view('essentials::employee_affairs.workers_affairs.index')
            ->with(compact('companies', 'contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
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

    public function updateWorkerProfilePicture(Request $request, $id)
    {
        try {

            $user = User::find($id);
            if (!$user) {
                throw new \Exception("User not found");
            }

            if ($request->hasFile('profile_picture')) {
                // Handle file upload
                $image = $request->file('profile_picture');
                $profile = $image->store('/profile_images');
                $user->update(['profile_image' => $profile]);
                error_log($profile);
            } elseif ($request->input('delete_image') == '1') {
                $oldImage = $user->profile_image;
                if ($oldImage) {
                    Storage::delete($oldImage);
                }
                $user->update(['profile_image' => null]);
                // Make sure to reset the delete_image flag in case of future updates
                $request->request->remove('delete_image');
            }

            $output = [
                'success' => 1,
                'msg' => __('user.user_update_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_add_wroker = auth()->user()->can('essentials.add_essentials_workers');
        if (!($is_admin || $can_add_wroker)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $username_ext = $this->moduleUtil->getUsernameExtension();
        // $locations = BusinessLocation::where('business_id', $business_id)
        //     ->Active()
        //     ->get();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = SalesProject::pluck('name', 'id')->toArray();
        $contacts = [null => __('essentials::lang.undefined')] + $contacts;

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
        $designations = Category::forDropdown($business_id, 'hrm_designation');

        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');
        $pay_comoponenets = EssentialsAllowanceAndDeduction::forDropdown($business_id);

        $user = !empty($data['user']) ? $data['user'] : null;

        $allowance_deduction_ids = [];
        if (!empty($user)) {
            $allowance_deduction_ids = EssentialsUserAllowancesAndDeduction::where('user_id', $user->id)
                ->pluck('allowance_deduction_id')
                ->toArray();
        }

        if (!empty($user)) {
            $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();
        } else {
            $contract = null;
        }

        // $locations = BusinessLocation::forDropdown($business_id, false, false, true, false);
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();
        $travel_ticket_categorie = EssentialsTravelTicketCategorie::pluck('name', 'id')->all();
        $contract_types = EssentialsContractType::where('type', '!=', 'تمهير')->pluck('type', 'id')->all();
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');

        $company = Company::all()->pluck('name', 'id');

        return  view('essentials::employee_affairs.workers_affairs.create')
            ->with(compact(
                'departments',
                'countries',
                'spacializations',
                'nationalities',
                'username_ext',
                'blood_types',
                'job_titles',
                'contacts',
                'company',
                'banks',
                'contract_types',
                'form_partials',
                'resident_doc',
                'user',

                'allowance_types',
                'travel_ticket_categorie',
                'contract_types',
                'nationalities',
                'specializations',
                'professions'

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
        $business_id = request()->session()->get('user.business_id');
        if (!($is_admin || auth()->user()->can('user.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;
            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;
            $request['user_type'] = 'worker';
            $existingprofnumber = null;
            $existingBordernumber = null;
            $emp_number = request()->input('emp_number');
            if ($emp_number) {
                $request['emp_number'] = $emp_number;
            } else {
                //auto generate
            }

            if ($request->input('id_proof_number')) {
                $existingprofnumber = User::where('id_proof_number', $request->input('id_proof_number'))->first();
            }
            if ($request->input('border_no')) {
                $existingBordernumber = User::where('border_no', $request->input('border_no'))->first();
            }



            if ($existingprofnumber || $existingBordernumber) {

                if ($existingprofnumber != null) {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.user_with_same_id_proof_number_exists'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.worker_with_same_border_number_exists'),
                    ];
                }
            } else {

                $user = $this->moduleUtil->createUser($request);
                $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_saved',  'model_instance' => $user, 'request' => $user]);


                $output = [
                    'success' => 1,
                    'msg' => __('user.user_added'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('workers_affairs')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $business_id = request()->session()->get('user.business_id');
        $can_show_worker = auth()->user()->can('essentials.show_essentials_workers');
        $documents = null;
        $document_delivery = null;


        if (!($is_admin || $can_show_worker)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }


        if (!in_array($id, $userIds)) {
            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => __('essentials::lang.user_not_found'),
            ]);
        }


        $user = User::with(['contactAccess', 'assignedTo', 'OfficialDocument', 'proposal_worker', 'employee_travle_categorie'])
            ->select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))
            ->find($id);


        $dataArray = [];

        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->where('status', 'valid')
            ->where('is_active', 1)->first();
        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->where('is_active', 1)->value('profession_id');
        // $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        $deliveryDocument =  FollowupDeliveryDocument::where('user_id', $user->id)->get();

        if ($user->user_type == 'worker') {


            if (!empty($user->proposal_worker_id)) {


                $officialDocuments = $user->OfficialDocument()->where('is_active', 1);
                $workerDocuments = $user->proposal_worker?->worker_documents;
                $contract_doc = $user->contract()->where('is_active', 1)->first();
                $qualificationDoc = $user->essentials_qualification()->first();

                if ($contract_doc !== false) {

                    $documents = $officialDocuments->merge([$contract_doc])->merge($workerDocuments);
                }

                if ($qualificationDoc) {
                    $documents = $officialDocuments->merge([$qualificationDoc])->merge($workerDocuments);
                }


                $documents = $officialDocuments->merge($workerDocuments);
            } else {

                $officialDocuments = $user->OfficialDocument()->where('is_active', 1)->get(); // Load documents into a collection
                $contract_doc = $user->contract()->where('is_active', 1)->first();
                $qualificationDoc = $user->essentials_qualification()->first();

                $documents = collect(); // Create an empty collection

                if (
                    $contract_doc !== null
                ) {
                    $documents->push($contract_doc); // Push contract document into the collection
                }

                if ($qualificationDoc !== null) {
                    $documents->push($qualificationDoc); // Push qualification document into the collection
                }

                if ($officialDocuments !== null) {
                    $documents = $documents->merge($officialDocuments); // Merge official documents with other documents
                }
            }
            // dd($documents);
        }



        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }
        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');

        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)->name;
        } else {
            $profession = "";
        }


        // if ($specializationId !== null) {
        //     $specialization = EssentialsSpecialization::find($specializationId)->name;
        // } else {
        //     $specialization = "";
        // }


        $user->profession = $profession;
        // $user->specialization = $specialization;


        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);
        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
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


        return view('essentials::employee_affairs.workers_affairs.show')
            ->with(compact(
                "deliveryDocument",
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
                'documents',
                'document_delivery',
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
        $business_id = request()->session()->get('user.business_id');
        $user = User::with(['contactAccess', 'assignedTo'])
            ->findOrFail($id);

        $contacts = SalesProject::pluck('name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $projects = SalesProject::pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::select([

            'profession_id',

        ])->where('employee_id', $id)->first();
        if ($appointments !== null) {
            $user->profession_id = $appointments['profession_id'];
        } else {
            $user->profession_id = null;
        }
        $allowance_deduction_ids = [];
        if (!empty($user)) {
            $allowance_deduction_ids = EssentialsUserAllowancesAndDeduction::with('essentialsAllowanceAndDeduction')
                ->where('user_id', $user->id)
                ->get();
        }

        if (!empty($user)) {
            $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->where('is_active', 1)->first();
        } else {
            $contract = null;
        }
        if (!empty($user))
            $user_travel = EssentialsEmployeeTravelCategorie::where('employee_id', $user->id)->where('categorie_id', '!=', null)->first();
        else {
            $user_travel = null;
        }

        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $idProofName = $user->id_proof_name;
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $companies = Company::all()->pluck('name', 'id');

        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');


        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'academic')->pluck('name', 'id');
        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        $locations = BusinessLocation::where('business_id', $business_id)
            ->get();

        $permitted_locations = $user->permitted_locations();

        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);

        $qualification = EssentialsEmployeesQualification::where('employee_id', $id)->first();
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();
        $travel_ticket_categorie = EssentialsTravelTicketCategorie::pluck('name', 'id')->all();
        $resident_doc = EssentialsOfficialDocument::select(['expiration_date', 'number'])->where('employee_id', $id)
            ->first();
        $officalDocuments = $user->OfficialDocument;
        return view('essentials::employee_affairs.workers_affairs.edit')
            ->with(compact(
                'officalDocuments',
                'projects',
                'contacts',
                'spacializations',
                'qualification',
                'resident_doc',
                'countries',
                'banks',
                'user_travel',
                'idProofName',
                'user',
                'contact_access',
                'is_checked_checkbox',
                'allowance_deduction_ids',
                'contract',
                'locations',
                'permitted_locations',
                'form_partials',
                'appointments',
                'travel_ticket_categorie',
                'contract_types',
                'nationalities',
                'professions',
                'companies',
                'job_titles',
                'allowance_types',
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
                'company_id', 'guardian_name', 'custom_field_1', 'custom_field_2', 'nationality', 'contract_type', 'contract_start_date', 'contract_end_date',
                'contract_duration', 'probation_period', 'user_type',
                'is_renewable', 'contract_file', 'essentials_salary', 'essentials_pay_period',
                'salary_type', 'amount', 'can_add_category',
                'travel_ticket_categorie', 'health_insurance', 'selectedData',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'essentials_department_id',
                'max_sales_discount_percent', 'family_number', 'alt_number', 'emp_number'

            ]);

            $existingprofnumber = null;
            $existingBordernumber = null;
            if ($request->input('id_proof_number')) {
                $existingprofnumber = User::where('id_proof_number', $request->input('id_proof_number'))->first();
            }
            if ($request->input('border_no')) {
                $existingBordernumber = User::where('border_no', $request->input('border_no'))->first();
            }



            if ($existingprofnumber || $existingBordernumber) {

                if ($existingprofnumber != null) {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.user_with_same_id_proof_number_exists'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.worker_with_same_border_number_exists'),
                    ];
                }
            } else {
                if ($user_data['emp_number'] == null) {
                    //auto generate
                }
                $business_id = request()->session()->get('user.business_id');
                if (!isset($user_data['selected_contacts'])) {
                    $user_data['selected_contacts'] = 0;
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



                $user = User::findOrFail($id);
                if ($request->hasFile('Iban_file')) {
                    error_log($request->hasFile('Iban_file'));

                    $file = request()->file('Iban_file');
                    $path = $file->store('/officialDocuments');
                    $bank_details = $request->input('bank_details');
                    $bank_details['Iban_file'] = $path;
                    $user_data['bank_details'] = json_encode($bank_details);


                    $Iban_doc = EssentialsOfficialDocument::where('employee_id', $user->id)->where('type', 'Iban')->first();
                    $bankCode = $bank_details['bank_code'];
                    $input['number'] = $bankCode;
                    $input['file_path'] =  $path;
                    $Iban_doc->update($input);
                }

                $delete_iban_file = $request->delete_iban_file ?? null;
                if ($delete_iban_file && $delete_iban_file == 1) {

                    $filePath =  !empty($user->bank_details) ? json_decode($user->bank_details, true)['Iban_file'] ?? null : null;
                    if ($filePath) {
                        Storage::delete($filePath);
                    }
                }

                if ($request->hasFile('Iban_file')) {
                    $file = request()->file('Iban_file');
                    $path = $file->store('/employee_bank_ibans');
                    $bank_details = $request->input('bank_details');
                    $bank_details['Iban_file'] = $path;
                    $user_data['bank_details'] = json_encode($bank_details);


                    $Iban_doc = EssentialsOfficialDocument::where('employee_id', $user->id)->where('type', 'Iban')->first();
                    $bankCode = $bank_details['bank_code'];
                    $input['number'] = $bankCode;
                    $input['file_path'] =  $path;
                    $Iban_doc->update($input);
                }
                $user->update($user_data);


                $deleted_documents = $request->deleted_documents ?? null;
                $offical_documents_types = $request->offical_documents_type;
                $offical_documents_choosen_files = $request->offical_documents_choosen_files;
                $offical_documents_previous_files = $request->offical_documents_previous_files;
                $files = [];
                if ($request->hasFile('offical_documents_files')) {
                    $files = $request->file('offical_documents_files');
                }
                if ($deleted_documents) {
                    foreach ($deleted_documents as $deleted_document) {
                        $filePath = EssentialsOfficialDocument::where('id', $deleted_document)->first()->file_path;
                        EssentialsOfficialDocument::where('id', $deleted_document)->delete();
                        if ($filePath) {
                            Storage::delete($filePath);
                            // EssentialsOfficialDocument::where('id', $deleted_document)->update([
                            //     'file_path' => Null,
                            // ]);
                        }
                    }
                }
                foreach ($offical_documents_types  as  $index => $offical_documents_type) {

                    if (
                        $offical_documents_type
                    ) {
                        if ($offical_documents_previous_files[$index] && $offical_documents_choosen_files[$index]) {
                            if (isset($files[$index])) {
                                $filePath = $files[$index]->store('/officialDocuments');
                                EssentialsOfficialDocument::where('id', $offical_documents_previous_files[$index])->update(['file_path' => $filePath]);
                            }
                        } elseif ($offical_documents_choosen_files[$index]) {
                            $document2 = new EssentialsOfficialDocument();
                            $document2->type = $offical_documents_type;
                            $document2->employee_id = $id;
                            if (isset($files[$index])) {
                                $filePath = $files[$index]->store('/officialDocuments');
                                $document2->file_path = $filePath;
                            }

                            $document2->save();
                        }
                    }
                }


                $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_updated', 'model_instance' => $user, 'request' => $user_data]);

                $this->moduleUtil->activityLog($user, 'edited', null, ['name' => $user->user_full_name]);



                $output = [
                    'success' => 1,
                    'msg' => __('user.user_update_success'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('show_workers_affairs', ['id' => $id])->with('status', $output);
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
