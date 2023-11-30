<?php

namespace Modules\FollowUp\Http\Controllers;

use App\Contact;
use App\Transaction;
use App\User;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\salesContract;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
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

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_projects = auth()->user()->can('followup.crud_projects');
        if (!$can_crud_projects) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $contacts = Contact::whereIn('type', ['customer', 'lead'])

            ->with([
                 'transactions', 'transactions.salesContract','contactLocation','contactLocation.assignedTo',
                'transactions.salesContract.salesOrderOperation'

            ]);

        if (request()->ajax()) {
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
              error_log (request()->input('project_name'));
                $contacts->where('contacts.id', request()->input('project_name'));
            }
           
           
            return Datatables::of($contacts)
                ->addColumn('contact_name', function ($contact) {
                    return $contact->supplier_business_name ?? null;
                })
                ->addColumn('number_of_contract', function ($contact) {
                    return $contact->transactions?->salesContract?->number_of_contract ?? null;
                })
                ->addColumn('start_date', function ($contact) {
                    return $contact->transactions?->salesContract?->start_date ?? null;
                })
                ->addColumn('end_date', function ($contact) {
                    return $contact->transactions?->salesContract?->end_date ?? null;
                })
                ->addColumn('active_worker_count', function ($contact) {
                    return optional($contact->contactLocation)->sum(function ($location) {
                        return $location->assignedTo
                            ->where('user_type', 'worker')
                            ->where('status', 'active')
                            ->count();
                    }) ?? 0;
                })
                ->addColumn('worker_count', function ($contact) {
                    return optional($contact->contactLocation)->sum(function ($location) {
                        return $location->assignedTo
                            ->where('user_type', 'worker')

                            ->count();
                    }) ?? 0;
                })
                ->addColumn('duration', function ($contact) {
                    $startDate = $contact->transactions?->salesContract?->start_date ?? null;
                    $endDate = $contact->transactions?->salesContract?->end_date ?? null;

                    if ($startDate && $endDate) {
                        $startDate = \Carbon\Carbon::parse($startDate);
                        $endDate = \Carbon\Carbon::parse($endDate);

                        $duration = $startDate->diff($endDate);

                        $years = $duration->y > 0 ? ($duration->y == 1 ? $duration->y . ' ' . trans('sales::lang.year') : $duration->y . ' ' . trans_choice('sales::lang.years', $duration->y)) : '';
                        $months = $duration->m > 0 ? ($duration->m == 1 ? $duration->m . ' ' . trans('sales::lang.month') : $duration->m . ' ' . trans_choice('sales::lang.months', $duration->m)) : '';
                        $days = $duration->d > 0 ? ($duration->d == 1 ? $duration->d . ' ' . trans('sales::lang.day') : $duration->d . ' ' . trans_choice('sales::lang.days', $duration->d)) : '';

                        $durationString = implode(', ', array_filter([$years, $months, $days]));

                        return $durationString;
                    }

                    return null;
                })
                ->addColumn('contract_form', function ($contact) {
                    return $contact->transactions?->contract_form ?? null;;
                })
                ->addColumn('status', function ($contact) {
                    return $contact->transactions?->contract?->salesOrderOperation?->Status ?? null;;
                })
                ->addColumn('type', function ($contact) {
                    return $contact->transactions?->contract?->salesOrderOperation?->operation_order_type ?? null;;
                })
                ->addColumn('action', function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="' . route('projectView', ['id' => $row->id]) . '" class="btn btn-xs btn-primary">
                             <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                         </a>';
                    }
                    return $html;
                })
                ->filterColumn('contact_name', function ($query, $keyword) {
                    $query->where('supplier_business_name', 'like', "%{$keyword}%");
                })

                ->rawColumns([
                    'contact_name','number_of_contract','start_date','end_date',
                    // 'active_worker_count', 'worker_count',
                     'duration', 'contract_form',
                    'status', 'type', 'action'
                ])

                ->make(true);
        }


        $contacts2 = Contact::whereIn('type', ['customer', 'lead'])->   pluck('supplier_business_name');

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
        $contact = Contact::findOrFail($id);
        $locationIds = $contact->contactLocation->pluck('id');
        $users = User::whereIn('assigned_to', $locationIds)
        
            
            ->with(['country', 'appointment.profession', 'allowancesAndDeductions', 'appointment.location', 'contract', 'OfficialDocument', 'workCard'])
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
