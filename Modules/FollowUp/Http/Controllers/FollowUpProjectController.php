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
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_projects= auth()->user()->can('followup.crud_projects');
        if (! $can_crud_projects) {
            abort(403, 'Unauthorized action.');
        }
     
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $contacts=Contact::where('type','customer')->pluck('name','id');
        if (request()->ajax()) {

            $contracts = SalesContract::with([
                    'transaction.contact.user',
                    'salesOrderOperation',       
            ]);
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
                $contactId = Transaction::where('contact_id', request()->input('project_name'))->value('id');
            
                $contracts->whereHas('transaction', function ($query) use ($contactId) {
                    $query->where('id', $contactId);
                });
            }
            if (!empty(request()->input('offer_status')) && request()->input('offer_status') !== 'all') {
                $contracts->whereHas('salesOrderOperation', function ($query) {
                    $query->where('Status', request()->input('offer_status'));
                });
            }
            if (!empty(request()->input('type')) && request()->input('type') !== 'all') {
                $contracts->whereHas('salesOrderOperation', function ($query) {
                    $query->where('operation_order_type', request()->input('type'));
                });
            }
            return Datatables::of($contracts)
                ->addColumn('contact_name', function ($contract) {
                    return $contract->transaction->contact->name;
                })
                ->addColumn('active_worker_count', function ($contract) {
                    return $contract->transaction->contact->user->where('user_type', 'worker')->where('status','active')->count();
                })
                ->addColumn('worker_count', function ($contract) {
                    return $contract->transaction->contact->user->where('user_type', 'worker')->count();
                })
                ->addColumn('duration', function ($contract) {
                    $startDate = \Carbon\Carbon::parse($contract->start_date);
                    $endDate = \Carbon\Carbon::parse($contract->end_date);
                
                    $duration = $startDate->diff($endDate);
                

                    $years = $duration->y > 0 ? ($duration->y == 1 ? $duration->y . ' ' . trans('sales::lang.year') : $duration->y . ' ' . trans_choice('sales::lang.years', $duration->y)) : '';
                    $months = $duration->m > 0 ? ($duration->m == 1 ? $duration->m . ' ' . trans('sales::lang.month') : $duration->m . ' ' . trans_choice('sales::lang.months', $duration->m)) : '';
                    $days = $duration->d > 0 ? ($duration->d == 1 ? $duration->d . ' ' . trans('sales::lang.day') : $duration->d . ' ' . trans_choice('sales::lang.days', $duration->d)) : '';
                    
                
                    $durationString = implode(', ', array_filter([$years, $months, $days]));
                
                    return $durationString;
                })
                
                ->addColumn('contract_form', function ($contract) {
                    return $contract->transaction ? $contract->transaction->contract_form : null;
                })
                ->addColumn('status', function ($contract) {
                    return $contract->salesOrderOperation ? $contract->salesOrderOperation->Status : null;
                })
                ->addColumn('type', function ($contract) {
                    return $contract->salesOrderOperation ? $contract->salesOrderOperation->operation_order_type : null;
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            $html .= '<a href="'.route('projectView', ['id' => $row->id]).'" class="btn btn-xs btn-primary">
                                <i class="fas fa-eye" aria-hidden="true"></i>'.__('messages.view').'
                            </a>';
                            // $html = '<a href="' . route('showEmployee', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';
                          //  &nbsp;
                        //     $html .= '<button class="btn btn-xs btn-danger delete_project_button" data-href="' . route('project.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                       }
            
                        return $html;
                    }
                )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                
                ->rawColumns(['action'])
                
                ->make(true);
        }
    
     
               
        
        return view('followup::projects.index')->with(compact('contacts'));
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
    
    $offerPrice = SalesContract::where('id', $id)->first()->offer_price_id;
    $contactId = Transaction::where('id', $offerPrice)->first()->contact_id;
    $users = User::select()
    ->where('assigned_to', $contactId)
    ->with(['country', 'appointment.profession','allowancesAndDeductions','appointment.location','contract','OfficialDocument' ,'workCard'])
    ->get();
    

    return view('followup::projects.show', compact('users'));

    
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
