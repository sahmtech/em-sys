<?php

namespace Modules\FollowUp\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Contact;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use App\Transaction;
use Modules\Sales\Entities\salesContract;

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
        $contacts = Contact::where('type', 'customer')->pluck('supplier_business_name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
       
        if (request()->ajax()) {
            $users = User::where('user_type', 'worker')
                ->join('contacts', 'contacts.id', '=', 'users.assigned_to')
                // ->join('essentials_admission_to_works', 'essentials_admission_to_works.employee_id', '=', 'users.id')
                ->leftjoin('essentials_admission_to_works', 'essentials_admission_to_works.employee_id', 'users.id')
                ->with(['country', 'contract','essentials_admission_to_works', 'OfficialDocument']);

            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
                $users->where('contacts.id', request()->input('project_name'));
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;

                $users->whereHas('contract', function ($query) use ($start, $end) {
                    $query->whereDate('contract_end_date', '>=', $start)
                        ->whereDate('contract_end_date', '<=', $end);
                });
            }
            if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {

                $users = $users->where('nationality_id', request()->nationality);
                error_log(request()->nationality);
            }
            $users->select(
                'users.*',
                'users.nationality_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'contacts.supplier_business_name as contact_name'
            );

            return Datatables::of($users)

                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                })
                ->addColumn('residence_permit_expiration', function ($user) {
                    return $this->getDocumentExpirationDate($user, 'residence_permit');
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

                ->rawColumns(['nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('followup::reports.projectWorkers')->with(compact('contacts', 'nationalities'));
    }


    public function projects(){
        $business_id = request()->session()->get('user.business_id');
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
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
                    return $contract->transaction->contact->supplier_business_name;
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
    
     
               
        
        return view('followup::reports.projects')->with(compact('contacts'));
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