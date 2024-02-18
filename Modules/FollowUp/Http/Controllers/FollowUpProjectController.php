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
use Modules\FollowUp\Entities\FollowupUserAccessProject;
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
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $can_followup_crud_projects = auth()->user()->can('followup.crud_projects');
        if (!($is_admin || $can_followup_crud_projects)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $can_projectView = auth()->user()->can('followup.projectView');
        
        $salesProjects = SalesProject::with(['contact']);
        $contacts2 = Contact::whereIn('type',['lead','qualified','unqualified','converted'])
        ->pluck('supplier_business_name', 'id');
        
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $salesProjects =  $salesProjects->whereIn('id',  $followupUserAccessProject);
            $contacts_ids =  $salesProjects->pluck('contact_id')->unique()->toArray();
           
            $contacts2 = Contact::whereIn('id',  $contacts_ids)
            ->whereIn('type',['lead','qualified','unqualified','converted'])
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

     
    

        
    
        return view('followup::projects.show',
         compact('users', 'id'));
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
