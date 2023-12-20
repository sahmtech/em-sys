<?php

namespace Modules\FollowUp\Http\Controllers;

use App\AccessRole;
use App\AccessRoleProject;
use App\Category;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Contact;
use App\ContactLocation;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use App\Transaction;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\SalesProject;

class FollowUpReportsController extends Controller
{
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function projectWorkers()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_workers = auth()->user()->can('followup.crud_workers');
        if (!$can_crud_workers) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $contacts_fillter = SalesProject::all()->pluck('name', 'id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields();
        $users = User::where('user_type', 'worker')

            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);
        $users->select(
            'users.*',
            'users.id_proof_number',
            'users.nationality_id',
            'users.essentials_salary',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        );

        if (request()->ajax()) {
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

            return Datatables::of($users)

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

                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })
                ->addColumn('admissions_date', function ($user) {
                    // return $this->getDocumentnumber($user, 'admissions_date');
                    return optional($user->essentials_admission_to_works)->admissions_date ?? ' ';
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
                ->rawColumns(['nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('followup::reports.projectWorkers')->with(compact('contacts_fillter', 'status_filltetr', 'fields', 'nationalities'));
    }


    public function projects()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!($is_admin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $contacts = Contact::whereIn('type', ['customer', 'lead'])

            ->with([
                'transactions', 'transactions.salesContract', 'salesProject', 'salesProject.users',
                'transactions.salesContract.salesOrderOperation'

            ]);

        if (!$is_admin) {
            $userProjects = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                $userProjects = array_merge($userProjects, $userProjectsForRole);
            }
            $userProjects = array_unique($userProjects);
            $salesProjects = SalesProject::whereIn('id', $userProjects)->with(['contact'])->unique()->toArray();
            // $contacts = $contacts->whereIn('id', $contactIds);
            if (!($is_admin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            $salesProjects = SalesProject::with(['contact']);
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
                        ->where('status', 'active')
                        ->count();
                })
                ->addColumn('worker_count', function ($row) {

                    return $row->users
                        ->where('user_type', 'worker')

                        ->count();
                })
                ->addColumn('duration', function ($row) {
                    return $row->contract_duration    ?? null;
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
        $contacts_fillter = Contact::all()->pluck('supplier_business_name', 'id');
        return view('followup::reports.projects')->with(compact('contacts_fillter'));
    }


    public function chooseFields_projectsworker()
    {
        return view('followup::reports.chooseFields');
    }
    private function getDocumentExpirationDate($user, $documentType)
    {
        foreach ($user->OfficialDocument as $off) {
            if ($off->type == $documentType) {
                return $off->expiration_date;
            }
        }

        return ' ';
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
        return view('followup::create');
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
        return view('followup::show');
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
