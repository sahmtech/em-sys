<?php

namespace Modules\FollowUp\Http\Controllers;

use App\AccessRole;
use App\AccessRoleProject;
use App\Contact;
use App\ContactLocation;
use App\Transaction;
use App\User;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\salesContract;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Sales\Entities\SalesProject;
use Modules\Sales\Http\Controllers\SalesController;

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
        $can_projectView = auth()->user()->can('followup.projectView');



        $contacts = Contact::whereIn('type', ['customer', 'lead'])

            ->with([
                'transactions', 'transactions.salesContract', 'salesProject', 'salesProject.users',
                'transactions.salesContract.salesOrderOperation'

            ]);
        $salesProjects = SalesProject::with(['contact']);

        if (!$is_admin) {
            $userProjects = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();
                if ($accessRole) {
                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                }
            }
            $userProjects = array_unique($userProjects);
            $salesProjects = $salesProjects->whereIn('id', $userProjects);
            // $contacts = $contacts->whereIn('id', $contactIds);

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
                ->addColumn('action', function ($row) use ($is_admin,$can_projectView) {
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

        $contacts2 = Contact::all()->pluck('supplier_business_name', 'id');
        return view('followup::projects.index')->with(compact('contacts2'));
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

        $users = User::where('assigned_to', $id)


            ->with([
                'country',
                'appointment.profession',
                'userAllowancesAndDeductions',
                'appointment.location',

                'contract',
                'OfficialDocument',
                'workCard'
            ])
            ->get();


        return view('followup::projects.show', compact('users', 'id'));
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