<?php

namespace Modules\Essentials\Http\Controllers;

use App\Category;

use App\AccessRole;
use App\AccessRoleProject;
use App\Company;
use App\Contact;
use App\ContactLocation;
use App\PayrollGroupUser;
use App\TimesheetUser;
use App\User;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Spatie\Activitylog\Models\Activity;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\Sales\Entities\SalesProject;

class EssentailsworkersController extends Controller
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

        $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();


        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields_hrm();
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
            'users.id as worker_id',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, '')  ,' ' ,COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        )
            ->orderBy('users.id', 'desc')
            ->groupBy('users.id');

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

        // if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
        //     $start = request()->filter_start_date;
        //     $end = request()->filter_end_date;

        //     $users->whereHas('contract', function ($query) use ($start, $end) {
        //         $query->whereDate('contract_end_date', '>=', $start)
        //             ->whereDate('contract_end_date', '<=', $end);
        //     });
        // }
        if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {

            $users = $users->where('users.nationality_id', request()->nationality);
        }
        $start_date = request()->get('start_date');
        $end_date = request()->get('end_date');

        error_log($start_date);
        error_log($end_date);

        if (!is_null($start_date)) {
            $users = $users->whereHas('contract', function ($query) use ($start_date) {
                $query->whereDate('contract_end_date', '>=', $start_date);
            });
        }

        if (!is_null($end_date)) {
            $users = $users->whereHas('contract', function ($query) use ($end_date) {
                $query->whereDate('contract_end_date', '<=', $end_date);
            });
        }
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



        return view('essentials::workers.index')
            ->with(compact('contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
    }
    public function getWorkerInfo(Request $request)
    {
        $identifier = $request->input('worker_identifier');


        $worker = User::where('first_name', 'like', '%' . $identifier . '%')
            ->orWhere('id_proof_number', $identifier)
            ->orWhere('border_no', $identifier)
            ->with(['company', 'assignedTo', 'contract'])
            ->first();

        if ($worker) {
            return response()->json([
                'success' => true,
                'data' => [
                    'full_name' => $worker->first_name . ' ' . $worker->last_name,

                    'status' => $worker->status ? __('essentials::lang.' . $worker->status) : null,
                    'sub_status' => $worker->sub_status ? __('essentials::lang.' . $worker->sub_status) : null,

                    'emp_number' => $worker->emp_number,
                    'id_proof_number' => $worker->id_proof_number,
                    'residence_permit_expiration' => optional($worker->OfficialDocument->where('type', 'residence_permit')->where('is_active', 1)->first())->number,
                    'passport_number' => optional($worker->OfficialDocument->where('type', 'passport')->where('is_active', 1)->first())->number,
                    'passport_expire_date' => optional($worker->OfficialDocument->where('type', 'passport')->where('is_active', 1)->first())->expiration_date,
                    'border_no' => $worker->border_no,
                    'company_name' => optional($worker->company)->name,
                    'assigned_to' => optional($worker->assignedTo)->name,
                ]
            ]);
        }

        return response()->json(['success' => false]);
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
        return view('essentials::create');
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
    public function show($id, $can_edit = false, $from = null)
    {
        error_log($can_edit);
        $business_id = request()->session()->get('user.business_id');

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

            $document_delivery = FollowupDeliveryDocument::where('user_id', $user->id)->get();
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
        //  $user->specialization = $specialization;


        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);

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


        $payrolls = PayrollGroupUser::with('payrollGroup')->where('user_id', $id)->get();
        $timesheets = TimesheetUser::where('user_id', $id)->with('timesheetGroup', 'user')
            ->get();
        $projects = SalesProject::pluck('name', 'id');
        $companies = Company::pluck('name', 'id');
        $timesheets = $timesheets->map(function ($user) use ($projects, $companies) {
            return [
                'id' => $user->user_id,
                'name' => $user->user->first_name . ' '  . $user->user->last_name,
                'nationality' => User::find($user->user->id)->country?->nationality ?? '',
                'residency' => $user->id_proof_number,
                'monthly_cost' => $user->monthly_cost,
                'wd' => $user->work_days,
                'absence_day' => $user->absence_days,
                'absence_amount' => $user->absence_amount,
                'over_time_h' => $user->over_time_hours,
                'over_time' => $user->over_time_amount,
                'other_deduction' => $user->other_deduction,
                'other_addition' => $user->other_addition,
                'cost2' => $user->cost_2,
                'invoice_value' => $user->invoice_value,
                'vat' => $user->vat,
                'total' => $user->total,
                'sponser' => $user->user->company_id ? ($companies[$user->user->company_id] ?? '') : '',
                'project' => $user->user->assigned_to ? $projects[$user->user->assigned_to] ?? '' : '',
                'basic' => $user->basic,
                'housing' => $user->housing,
                'transport' => $user->transport,
                'other_allowances' => $user->other_allowances,
                'total_salary' => $user->total_salary,
                'deductions' => $user->deductions,
                'additions' => $user->additions,
                'final_salary' => $user->final_salary,
                'timesheet_date' => $user->timesheetGroup->timesheet_date,
            ];
        });
        return view('essentials::workers.show')->with(compact(
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
            'payrolls',
            'timesheets',
            'can_edit',
            'from'
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
