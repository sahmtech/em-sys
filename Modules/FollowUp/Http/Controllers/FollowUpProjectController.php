<?php

namespace Modules\FollowUp\Http\Controllers;


use App\Contact;

use App\User;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;

use Modules\FollowUp\Entities\FollowupUserAccessProject;
use Modules\Sales\Entities\SalesProject;

use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsSpecialization;

use App\Category;
use Modules\Essentials\Entities\EssentialsDepartment;

use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;


use Modules\CEOManagment\Entities\WkProcedure;

use Modules\CEOManagment\Entities\RequestsType;

use Modules\Essentials\Entities\EssentialsLeaveType;

use Modules\Essentials\Entities\EssentialsInsuranceClass;

use App\Company;

class FollowUpProjectController extends Controller
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
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $can_followup_crud_projects = auth()->user()->can('followup.crud_projects');
        if (!($is_admin || $can_followup_crud_projects)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $can_projectView = auth()->user()->can('followup.projectView');

        $contacts2 = Contact::whereIn('type', ['lead', 'qualified', 'unqualified', 'converted'])
            ->pluck('supplier_business_name', 'id');
        $salesProjects = SalesProject::with(['contact']);

        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $salesProjects =  $salesProjects->whereIn('id',  $followupUserAccessProject);
            $contacts_ids =  $salesProjects->pluck('contact_id')->unique()->toArray();

            $contacts2 = Contact::whereIn('id',  $contacts_ids)
                ->whereIn('type', ['lead', 'qualified', 'unqualified', 'converted'])
                ->pluck('supplier_business_name', 'id');
        }


        if (request()->ajax()) {
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

                $salesProjects = $salesProjects->where('contact_id', request()->input('project_name'));
            }

            return Datatables::of($salesProjects)
                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'contact_name',
                    function ($row) {
                        return $row->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'contact_location_name',
                    function ($row) {
                        return  $row->name;
                    }
                )
                ->addColumn('number_of_contract', function ($row) {
                    return $row->salesContract?->number_of_contract ?? null;
                })
                ->addColumn('start_date', function ($row) {
                    return $row->salesContract?->start_date ?? null;
                })
                ->addColumn('end_date', function ($row) {
                    return $row->salesContract?->end_date ?? null;
                })
                ->addColumn('active_worker_count', function ($row) {

                    return $row->users
                        ->where('user_type', 'worker')
                        ->where(function ($query) {
                            $query->where('users.status', 'active')
                                ->orWhere(function ($subQuery) {
                                    $subQuery->where('users.status', 'inactive')
                                        ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                                });
                        })
                        ->count();
                })
                ->addColumn('worker_count', function ($row) {

                    return $row->users
                        ->where('user_type', 'worker')
                        ->count();
                })
                ->addColumn('duration', function ($row) {
                    return $row->contract_duration ?? null;
                })
                ->addColumn('contract_form', function ($row) {
                    return $row->salesContract?->transaction->contract_form ?? null;;
                })

                ->addColumn('status', function ($row) {
                    return $row->salesContract?->status     ?? null;;
                })
                ->addColumn('type', function ($row) {
                    return $row->salesContract->salesOrderOperation?->operation_order_type ?? null;;
                })
                ->addColumn('action', function ($row) use ($is_admin, $can_projectView) {
                    $html = '';
                    if (($is_admin  || $can_projectView)) {
                        $html .= '<a href="' . route('projectView', ['id' => $row->id]) . '" class="btn btn-xs btn-primary">
                             <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                         </a>';
                    }
                    return $html;
                })

                ->filterColumn('contact_name', function ($query, $keyword) {

                    $query->whereHas('contact', function ($qu) use ($keyword) {
                        $qu->where('supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contact_location_name', function ($query, $keyword) {

                    $query->where('name', 'like', "%{$keyword}%");
                })


                ->rawColumns(['id', 'contact_location_name', 'contract_form', 'contact_name', 'active_worker_count', 'worker_count', 'action'])
                ->make(true);
        }


        return view('followup::projects.index')
            ->with(compact('contacts2'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */


    public function createWorker()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_add_wroker = auth()->user()->can('followup.create_worker');
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
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {

        $users = User::where('assigned_to', $id)
            ->with([
                'country',
                'appointment' => function ($query) {
                    $query->where('is_active', 1)->latest('created_at');
                },
                'appointment.profession',
                'userAllowancesAndDeductions',

                'appointment.location',
                'contract' => function ($query) {
                    $query->where('is_active', 1)->latest('created_at');
                },
                'OfficialDocument' => function ($query) {
                    $query->where('is_active', 1)->latest('created_at');
                },
                'workCard'
            ])
            ->get();


        $allRequestTypes = RequestsType::pluck('type', 'id');
        $business_id = request()->session()->get('user.business_id');
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'LIKE', '%متابعة%')
                ->orWhere(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%أعمال%');
                })->orWhere(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%شركات%');
                });
        })
            ->pluck('id')->toArray();
        $requestTypeIds = WkProcedure::distinct()
            ->with('request_type')
            ->whereIn('department_id', $departmentIds)
            ->where('request_owner_type', 'worker')
            ->where('start', '1')
            ->pluck('request_type_id')
            ->toArray();

        $requestTypes = RequestsType::whereIn('id', $requestTypeIds)
            ->get()
            ->mapWithKeys(function ($requestType) {
                return [$requestType->id => $requestType->type];
            })
            ->unique()
            ->toArray();

        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
        $saleProjects = SalesProject::all()->pluck('name', 'id');


        return view(
            'followup::projects.show',
            compact('users', 'id', 'requestTypes', 'main_reasons', 'classes', 'saleProjects', 'leaveTypes')
        );
    }



    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('followup::edit');
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