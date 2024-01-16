<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\Contact;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;

use Modules\InternationalRelations\Entities\IrDelegation;

class DelegationController extends Controller
{
    protected $moduleUtil;




    public function __construct(ModuleUtil $moduleUtil)
    {

        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)

    {
        

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_add_proposed_worker = auth()->user()->can('internationalrelations.add_proposed_worker');
        $can_import_proposed_workers= auth()->user()->can('internationalrelations.import_proposed_workers');
        if (!($is_admin || $can_add_proposed_worker || $can_import_proposed_workers)) {
           //temp  abort(403, 'Unauthorized action.');
        }

        
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        if (request()->ajax()) {
           $irDelegations = IrDelegation::with(['agency', 'transactionSellLine.service']);
           if (!empty($request->input('agency'))) {
            $irDelegations->where('agency_id', $request->input('agency'));
            }

            return Datatables::of($irDelegations)
            ->addColumn('agency_name', function ($delegation) {
                return $delegation->agency->supplier_business_name ?? null;
            })
            ->addColumn('target_quantity', function ($delegation) {
                return $delegation->targeted_quantity ?? null;
            })
            ->addColumn('currently_proposed_labors_quantity', function ($delegation) {
                return $delegation->proposed_labors_quantity ?? null;
            })
            ->addColumn('profession_name', function ($delegation) {
                return $delegation->transactionSellLine->service->profession->name ?? null;
            })
            ->addColumn('specialization_name', function ($delegation) {
                return $delegation->transactionSellLine->service->specialization->name ?? null;
            })
            ->addColumn('gender', function ($delegation) {
                return __('sales::lang.' . $delegation->transactionSellLine->service->gender)?? null;
            })
            ->addColumn('service_price', function ($delegation) {
                return $delegation->transactionSellLine->service->service_price ?? null;
            })
            ->addColumn('additional_allwances', function ($delegation) {
                if (!empty($delegation->transactionSellLine->additional_allwances)) {
                    $allowancesHtml = '<ul>';
                    foreach (json_decode($delegation->transactionSellLine->additional_allwances) as $allowance) {
                        if (is_object($allowance) && property_exists($allowance, 'salaryType') && property_exists($allowance, 'amount')) {
                            $allowancesHtml .= '<li>' . __('sales::lang.' . $allowance->salaryType) . ': ' . $allowance->amount . '</li>';
                        }
                    }
                    $allowancesHtml .= '</ul>';
                    return $allowancesHtml;
                }

                return null;
            })
            ->addColumn('monthly_cost_for_one', function ($delegation) {
                return $delegation->transactionSellLine->service->monthly_cost_for_one ?? null;
            })
          
            ->addColumn('actions', function ($delegation) use ($can_add_proposed_worker, $can_import_proposed_workers, $is_admin) {
                $html = '';
                if ($is_admin || $can_add_proposed_worker) {
                    $html .= '<button class="btn btn-xs btn-primary">
                                <a href="' . route('createProposed_labor', ['delegation_id' => $delegation->id, 'agency_id' => $delegation->agency->id, 'transaction_sell_line_id' => $delegation->transactionSellLine->id]) . '" style="color: white; text-decoration: none;">'
                                    . trans("internationalrelations::lang.addWorker") .
                                '</a>
                              </button>&nbsp;';
                }
                if ($is_admin || $can_import_proposed_workers) {
                    $html .= '<button class="btn btn-xs btn-success">
                                <a href="' . route('importWorkers', ['delegation_id' => $delegation->id, 'agency_id' => $delegation->agency->id, 'transaction_sell_line_id' => $delegation->transactionSellLine->id]) . '" style="color: white; text-decoration: none;">'
                                    . trans("internationalrelations::lang.importWorkers") .
                                '</a>
                              </button>';
                }
                return $html;
            })
            
            ->rawColumns(['actions', 'additional_allwances'])
            ->make(true);
            }


        return view('internationalrelations::EmploymentCompanies.requests')->with(compact('agencys'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('internationalrelations::create');
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
        return view('internationalrelations::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
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
