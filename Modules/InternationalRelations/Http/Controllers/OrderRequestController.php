<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

use App\Utils\ModuleUtil;
use App\Contact;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\InternationalRelations\Entities\IrDelegation;

use Modules\InternationalRelations\Entities\Ir_delegation;
use Modules\Sales\Entities\SalesOrdersOperation;

class OrderRequestController extends Controller
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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_orders_operations = auth()->user()->can('internationalrelations.crud_orders_operations');
        if (!($isSuperAdmin || $can_crud_orders_operations)) {
            abort(403, 'Unauthorized action.');
        }
        $operations = DB::table('sales_orders_operations')
            ->join('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
            ->where('sales_orders_operations.operation_order_type', '=', 'External')
            ->select(
                'sales_orders_operations.id as id',
                'sales_orders_operations.operation_order_no as operation_order_no',
                'sales_orders_operations.orderQuantity as orderQuantity',
                'sales_orders_operations.DelegatedQuantity as DelegatedQuantity',
                'contacts.supplier_business_name as contact_name',
                'sales_contracts.number_of_contract as contract_number',
                'sales_orders_operations.operation_order_type as operation_order_type',
                'sales_orders_operations.Status as Status',
                'sales_orders_operations.has_visa as has_visa',

            );
        $contracts = DB::table('sales_orders_operations')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
            ->select('sales_contracts.number_of_contract as contract_number')
            ->get();
        if (request()->input('number_of_contract')) {

            $operations->where('sales_contracts.number_of_contract', request()->input('number_of_contract'));
        }

        if (request()->input('Status') && request()->input('Status') !== 'all') {
            $operations->where('sales_orders_operations.operation_order_type', request()->input('Status'));
        }


        if (request()->ajax()) {


            return Datatables::of($operations)
                ->addColumn('Status', function ($row) {

                    return __('sales::lang.' . $row->Status);
                })
                ->editColumn('orderQuantity', function ($row) {

                    return $row->orderQuantity - $row->DelegatedQuantity;
                })

                ->addColumn('Delegation', function ($row) {
                    $html = '';

                    if ($row->orderQuantity - $row->DelegatedQuantity != 0) {
                        $html .= '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'Delegation'], [$row->id]) . '" class="btn btn-xs btn-warning btn-modal" data-container=".view_modal"><i class="fas fa-plus" aria-hidden="true"></i> ' . __('internationalrelations::lang.Delegation') . '</a>&nbsp;';
                    } else {
                        $html .= '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'viewDelegation'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('internationalrelations::lang.viewDelegation') . '</a>&nbsp;';
                    }

                    if ($row->has_visa != 1) {
                        $html .= '<button data-id="' . $row->id . '" class="btn btn-xs btn-info btn-add-visa" data-toggle="modal" data-target="#addVisaModal">
                                    <i class="fas fa-plus" aria-hidden="true"></i> ' . __('internationalrelations::lang.addvisa') . '
                                </button>';
                    }
                    return $html;
                })



                ->rawColumns(['Delegation', 'Status'])
                ->make(true);
        }
        $status = [
            'done' => __('sales::lang.done'),
            'Under_process' => __('sales::lang.Under_process'),
            'Not_started' => __('sales::lang.Not_started'),

        ];

        return view('internationalrelations::orderRequest.index')
            ->with(compact('contracts', 'status'));
    }


    public function Delegation($id)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_delegate_order = auth()->user()->can('internationalrelations.delegate_order');
        if (!($isSuperAdmin || $can_delegate_order)) {
            abort(403, 'Unauthorized action.');
        }
        $operation = SalesOrdersOperation::with('salesContract.transaction')
            ->where('id', $id)
            ->first();

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $operation->salesContract->transaction->id)->with(['sale_project:id,name,phone_in_charge', 'sell_lines', 'sell_lines.service'])
            ->select(
                'id',
                'business_id',
                'location_id',
                'status',
                'sales_project_id',
                'ref_no',
                'final_total',
                'down_payment',
                'contract_form',
                'transaction_date'

            )->get()[0];

        $agencies = Contact::where('type', '=', 'recruitment')->get();


        return view('internationalrelations::orderRequest.Delegation')->with(compact('query', 'agencies', 'id'));
    }

    public function viewDelegation($id)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');
        if (!($isSuperAdmin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_view_delegation_info = auth()->user()->can('internationalrelations.view_delegation_info');
        if (!($isSuperAdmin || $can_view_delegation_info)) {
            abort(403, 'Unauthorized action.');
        }
        $operation = SalesOrdersOperation::with('salesContract.transaction')
            ->where('id', $id)
            ->first();
        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $operation->salesContract->transaction->id)
            ->with(['sell_lines'])
            ->get();

        $sellLineIds = $query->pluck('sell_lines.*.id')->flatten()->toArray();

        $irDelegations = IrDelegation::with('agency')->whereIn('transaction_sell_line_id', $sellLineIds)->get();

        return view('internationalrelations::orderRequest.viewDelegation')->with(compact('irDelegations'));
    }
    public function saveRequest(Request $request)
    {

        try {

    
            $order_id = isset($request->order_id) ? $request->order_id : null;
            $order = SalesOrdersOperation::find($order_id);

            if (!$order) {
                return response()->json(['success' => false, 'message' => __('lang_v1.order_not_found')]);
            }

            $data_array = $request->input('data_array');
            $sumTargetQuantity = 0;

            foreach ($data_array as $item) {

                $sumTargetQuantity += $item['target_quantity'];
             
            }

            if ($sumTargetQuantity > $order->orderQuantity - $order->DelegatedQuantity) {
                return response()->json(['success' => false, 'message' => __('lang_v1.Sum_of_target_quantity_is_greater_than_order_quantity')]);
            }

            if ($sumTargetQuantity < $order->orderQuantity - $order->DelegatedQuantity) {
                $order->Status = 'under_process';
                $order->save();
            }

            foreach ($data_array as $index => $item) {
                if (isset($item['target_quantity'])) {

                    $delegation = DB::table('ir_delegations')
                        ->where('transaction_sell_line_id', $item['product_id'])
                        ->where('agency_id', $item['agency_name'])
                        ->first();

                    if ($delegation) {
                       
                        DB::table('ir_delegations')
                            ->where('transaction_sell_line_id', $item['product_id'])
                            ->where('agency_id', $item['agency_name'])
                            ->update(['targeted_quantity' => DB::raw('targeted_quantity + ' . $item['target_quantity'])]);
                    } else {

                        $filePath = null;

                        if ($request->hasFile('attachments') && $request->file('attachments')[$index]->isValid()) {
                         
                            $file = $request->file('attachments')[$index];
                            $filePath = $file->store('/delegations_validation_files');
                           
                        } 


                        DB::table('ir_delegations')->insert([
                            'transaction_sell_line_id' => $item['product_id'],
                            'agency_id' => $item['agency_name'],
                            'targeted_quantity' => $item['target_quantity'],
                            'validationFile' => $filePath ?? null
                        ]);
                    }
                }
            }

            $order->DelegatedQuantity = $order->DelegatedQuantity + $sumTargetQuantity;
            $order->save();

            // $output = [
            //     'success' => true,
            //     'msg' => __('lang_v1.added_success'),
            // ];
            return response()->json(['success' => true, 'message' =>  __('lang_v1.saved_successfully')]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]; 
            return redirect()->route('order_request')->with($output);
        }
    
      
       
    }
  
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */


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
