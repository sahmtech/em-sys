<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleProject;
use App\Category;
use App\ContactLocation;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\Sales\Entities\SalesProject;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class EssentialsWorkerController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {

        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_essentials_workers = auth()->user()->can('essentials.insurance_index_workers');
        if (!($is_admin || $can_essentials_workers)) {
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
        $users = User::where('users.status', '!=', 'inactive')->whereIn('users.id', $userIds)->where('user_type', 'worker')

            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);
            $users->select(
                'users.*',
                'users.id as worker_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
                'sales_projects.name as contact_name'
            )->orderBy('users.id', 'desc')
                ->groupBy('users.id');
    

        if (request()->ajax()) {
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

            return DataTables::of($users)

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
                })  ->addColumn('insurance', function ($user) {
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
                ->rawColumns(['contact_name', 'company_name', 'passport_number', 'passport_expire_date', 'worker', 'categorie_id', 'admissions_status', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('essentials::projects_workers.medicalInsurance.index')->with(compact('contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
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


    public function show($id)
    {


        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_essentials_workers = auth()->user()->can('essentials.insurance_showWorkerProjects');
        if (!($is_admin || $can_essentials_workers)) {
            return redirect()->back()->with('status', [
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
            ->find($id);



        $documents = null;
        $document_delivery = null;

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

            $deliveryDocument = FollowupDeliveryDocument::where('user_id', $user->id)->get();
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

        // $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        // if ($specializationId !== null) {
        //     $specialization = EssentialsSpecialization::find($specializationId)->name;
        // } else {
        //     $specialization = "";
        // }


        $user->profession = $profession;
        // $user->specialization = $specialization;


        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);

        $users = User::whereIn('users.id', $userIds)->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $users->pluck('full_name', 'id');


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




        return view('essentials::projects_workers.medicalInsurance.show')->with(compact(
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
            'deliveryDocument',
        ));
    }
}