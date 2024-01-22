<?php

namespace Modules\Essentials\Http\Controllers;

use App\Category;
use App\AccessRole;
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


        $contacts_fillter = SalesProject::all()->pluck('name', 'id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $users = User::whereIn('users.id', $userIds)
            ->with(['assignedTo'])
            ->where('user_type', 'worker')
            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);

        $users->select(
            'users.*',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        )
            ->orderBy('users.id', 'desc')
            ->groupBy('users.id');

        if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

            $users = $users->where('users.assigned_to', request()->input('project_name'));
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

        if (request()->ajax()) {

            return DataTables::of($users)

                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                })
                // ->addColumn('building', function ($user) {
                //     return $user->htrRoomsWorkersHistory->last()->room->building?->name ?? '';
                // })

                // ->addColumn('building_address', function ($user) {
                //     return $user->htrRoomsWorkersHistory->last()->room->building?->address ?? '';
                // })

                // ->addColumn('room_number', function ($user) {
                //     return $user->htrRoomsWorkersHistory->last()->room->room_number ?? '';
                // })
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

                ->addColumn('profession', function ($row) use ($appointments, $professions) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $professions[$professionId] ?? '';

                    return $professionName;
                })



                ->addColumn('specialization', function ($row) use ($appointments2, $specializations) {
                    $specializationId = $appointments2[$row->id] ?? '';
                    $specializationName = $specializations[$specializationId] ?? '';

                    return $specializationName;
                })->addColumn('bank_code', function ($user) {

                    $bank_details = json_decode($user->bank_details);
                    return $bank_details->bank_code ?? ' ';
                })
                ->addColumn('contact_name', function ($user) {

                    return $user->assignedTo->name ?? '';
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
                ->rawColumns(['contact_name', 'worker', 'categorie_id', 'admissions_status', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('essentials::employee_affairs.workers_affairs.index')
            ->with(compact('contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
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


            $com_id = request()->input('essentials_department_id');
            $latestRecord = User::where('company_id', $com_id)->orderBy('emp_number', 'desc')
                ->first();

            if ($latestRecord) {
                $latestRefNo = $latestRecord->emp_number;
                $latestRefNo++;
                $request['emp_number'] = str_pad($latestRefNo, 4, '0', STR_PAD_LEFT);
            } else {

                $request['emp_number'] =  $business_id . '000';
            }



            $existingprofnumber = User::where('id_proof_number', $request->input('id_proof_number'))->first();

            if ($existingprofnumber) {
                $errorMessage = trans('essentials::lang.worker_with_same_id_proof_number_exists');
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


        $user = User::with(['contactAccess', 'assignedTo', 'OfficialDocument', 'proposal_worker'])
            ->select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))
            ->find($id);


        $dataArray = [];
        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();
        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('profession_id');
        // $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        $deliveryDocument =  FollowupDeliveryDocument::where('user_id', $user->id)->get();

        if ($user->user_type == 'worker') {


            if (!empty($user->proposal_worker_id)) {


                $officialDocuments = $user->OfficialDocument;
                $workerDocuments = $user->proposal_worker?->worker_documents;

                $documents = $officialDocuments->merge($workerDocuments);
            } else {
                $documents = $user->OfficialDocument;
            }
        }



        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


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
