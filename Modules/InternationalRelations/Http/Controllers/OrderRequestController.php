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
use App\TransactionSellLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\InternationalRelations\Entities\IrDelegation;

use Modules\InternationalRelations\Entities\Ir_delegation;
use Modules\Sales\Entities\SalesOrdersOperation;
use Modules\Sales\Entities\SalesProject;
use Modules\Sales\Entities\SalesUnSupportedOperationOrder;
use Modules\Sales\Entities\SalesUnSupportedWorker;

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

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_add_operation_order_visa = auth()->user()->can('internationalrelations.add_operation_order_visa');
        $can_delegate_operation_order = auth()->user()->can('internationalrelations.delegate_operation_order');
        $can_view_order_delegations = auth()->user()->can('internationalrelations.view_order_delegations');


        if (!($is_admin || $can_add_operation_order_visa ||  $can_delegate_operation_order || $can_view_order_delegations)) {
            //temp  abort(403, 'Unauthorized action.');
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

                ->addColumn('Delegation', function ($row) use ($is_admin, $can_view_order_delegations, $can_delegate_operation_order,   $can_add_operation_order_visa) {
                    $html = '';

                    if ($row->orderQuantity - $row->DelegatedQuantity != 0) {
                        if ($is_admin || $can_delegate_operation_order) {
                            $html .= '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'Delegation'], [$row->id]) . '" class="btn btn-xs btn-warning btn-modal" data-container=".view_modal"><i class="fas fa-plus" aria-hidden="true"></i> ' . __('internationalrelations::lang.Delegation') . '</a>&nbsp;';
                        }
                    } else {
                        if ($is_admin || $can_view_order_delegations) {
                            $html .= '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'viewDelegation'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('internationalrelations::lang.viewDelegation') . '</a>&nbsp;';
                        }
                    }

                    if ($row->has_visa != 1) {
                        if ($is_admin || $can_add_operation_order_visa) {
                            $html .= '<button data-id="' . $row->id . '" class="btn btn-xs btn-info btn-add-visa" data-toggle="modal" data-target="#addVisaModal">
                                    <i class="fas fa-plus" aria-hidden="true"></i> ' . __('internationalrelations::lang.addvisa') . '
                                </button>';
                        }
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

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delegate_order = auth()->user()->can('internationalrelations.delegate_order');
        if (!($is_admin || $can_delegate_order)) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $operation = SalesOrdersOperation::with('salesContract.transaction')
            ->where('id', $id)
            ->first();

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $operation->salesContract->transaction->id)->with(['contact:id,supplier_business_name,mobile', 'sell_lines', 'sell_lines.service'])
            ->select(
                'id',
                'business_id',
                'location_id',
                'status',
                'contact_id',
                'ref_no',
                'final_total',
                'down_payment',
                'contract_form',
                'transaction_date'

            )->get()[0];

        $agencies = Contact::where('type', '=', 'recruitment')->get();


        return view('internationalrelations::orderRequest.Delegation')->with(compact('query', 'agencies', 'id'));
    }

    public function unSupportedDelegation($id)
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $operation = SalesUnSupportedOperationOrder::where('id', $id)->first();


        $query = SalesUnSupportedWorker::where('id', $operation->workers_order_id)->get()[0];

        $agencies = Contact::where('type', '=', 'recruitment')->get();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        return view('internationalrelations::orderRequest.unSupportedDelegation')->with(compact('query', 'operation', 'specializations', 'professions', 'nationalities', 'agencies', 'id'));
    }
    public function viewDelegation($id)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_delegation_info = auth()->user()->can('internationalrelations.view_delegation_info');
        if (!($is_admin || $can_view_delegation_info)) {
            //temp  abort(403, 'Unauthorized action.');
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

        $irDelegations = IrDelegation::with('agency', 'transactionSellLine.service.profession')->where('operation_order_id', $id)->whereIn('transaction_sell_line_id', $sellLineIds)->get();

        return view('internationalrelations::orderRequest.viewDelegation')->with(compact('irDelegations'));
    }

    public function saveUbnSupportedRequest(Request $request)
    {

        try {


            $order_id = isset($request->order_id) ? $request->order_id : null;
            $order = SalesUnSupportedOperationOrder::find($order_id);
            $today = \Carbon::now()->format('Y-m-d H:i:s');

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
                $order->status = 'under_process';
                $order->save();
            }

            foreach ($data_array as $index => $item) {
                if (isset($item['target_quantity'])) {
                    $filePath = null;


                    if ($request->hasFile('attachments') && $request->file('attachments')[$index]->isValid()) {

                        $file = $request->file('attachments')[$index];
                        $filePath = $file->store('/delegations_validation_files');
                    }
                    $sellLine = SalesUnSupportedWorker::where('id', $item['worker_order_id'])->first();

                    $delegation = IrDelegation::where('unSupportedworker_order_id', $sellLine->id)
                        ->where('agency_id', $item['agency_name'])
                        ->first();

                    if ($delegation) {
                        IrDelegation::where('unSupportedworker_order_id', $sellLine->id)
                            ->where('agency_id', $item['agency_name'])
                            ->update([
                                'targeted_quantity' => DB::raw('targeted_quantity + ' . $item['target_quantity']),
                                'validationFile' => $filePath ?? null
                            ]);

                        SalesUnSupportedWorker::where('id', $sellLine->id)->update([
                            'remaining_quantity_for_delegation' => \DB::raw('remaining_quantity_for_delegation - ' . $item['target_quantity']),

                        ]);
                    } else {


                        IrDelegation::create([
                            'unSupportedworker_order_id' =>  $sellLine->id,
                            'unSupported_operation_id' => $order_id,
                            'agency_id' => $item['agency_name'],
                            'targeted_quantity' => $item['target_quantity'],
                            'validationFile' => $filePath ?? null,
                            'start_date' => $today
                        ]);

                        SalesUnSupportedWorker::where('id', $sellLine->id)->update([
                            'remaining_quantity_for_delegation' => \DB::raw('remaining_quantity_for_delegation - ' . $item['target_quantity']),

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
    public function saveRequest(Request $request)
    {

        try {


            $order_id = isset($request->order_id) ? $request->order_id : null;
            $order = SalesOrdersOperation::find($order_id);
            $today = \Carbon::now()->format('Y-m-d H:i:s');

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
                    $filePath = null;


                    if ($request->hasFile('attachments') && $request->file('attachments')[$index]->isValid()) {

                        $file = $request->file('attachments')[$index];
                        $filePath = $file->store('/delegations_validation_files');
                    }
                    $sellLine = TransactionSellLine::where('service_id', $item['product_id'])->first();

                    $delegation = IrDelegation::where('transaction_sell_line_id', $sellLine->id)
                        ->where('agency_id', $item['agency_name'])
                        ->first();

                    if ($delegation) {
                        IrDelegation::where('transaction_sell_line_id', $sellLine->id)
                            ->where('agency_id', $item['agency_name'])
                            ->update([
                                'targeted_quantity' => DB::raw('targeted_quantity + ' . $item['target_quantity']),
                                'validationFile' => $filePath ?? null
                            ]);

                        TransactionSellLine::where('id', $sellLine->id)->update([
                            'operation_remaining_quantity' => \DB::raw('operation_remaining_quantity - ' . $item['target_quantity']),

                        ]);
                    } else {


                        IrDelegation::create([
                            'transaction_sell_line_id' =>  $sellLine->id,
                            'operation_order_id' => $order_id,
                            'agency_id' => $item['agency_name'],
                            'targeted_quantity' => $item['target_quantity'],
                            'validationFile' => $filePath ?? null,
                            'start_date' => $today
                        ]);

                        TransactionSellLine::where('id', $sellLine->id)->update([
                            'operation_remaining_quantity' => \DB::raw('operation_remaining_quantity - ' . $item['target_quantity']),

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
    public function orderOperationForUnsupportedWorkers()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $orders = SalesUnSupportedWorker::where('status', '!=', 'ended')->pluck('order_no', 'id');

        $can_add_operation_order_visa = auth()->user()->can('internationalrelations.add_operation_order_visa');
        $can_delegate_operation_order = auth()->user()->can('internationalrelations.delegate_operation_order');
        $can_view_order_delegations = auth()->user()->can('internationalrelations.view_order_delegations');


        $operations =
            DB::table('sales_un_supported_operation_orders')
            ->join('sales_un_supported_workers', 'sales_un_supported_operation_orders.workers_order_id', 'sales_un_supported_workers.id')
            ->select(
                'sales_un_supported_operation_orders.id as id',
                'sales_un_supported_operation_orders.operation_order_no as operation_order_no',
                'sales_un_supported_operation_orders.orderQuantity as orderQuantity',
                'sales_un_supported_operation_orders.DelegatedQuantity as DelegatedQuantity',
                'sales_un_supported_operation_orders.Interview as Interview',
                'sales_un_supported_operation_orders.Industry as Industry',
                'sales_un_supported_operation_orders.Location as Location',
                'sales_un_supported_operation_orders.Delivery as Delivery',
                'sales_un_supported_operation_orders.status as Status',
                'sales_un_supported_operation_orders.has_visa as has_visa',
                'sales_un_supported_workers.profession_id',
                'sales_un_supported_workers.specialization_id',
                'sales_un_supported_workers.nationality_id',
                'sales_un_supported_workers.salary',
                'sales_un_supported_workers.date',
            )->orderby('id', 'desc');


        if (request()->ajax()) {


            return Datatables::of($operations)
                ->addColumn('Status', function ($row) {

                    return __('sales::lang.' . $row->Status);
                })
                ->editColumn('orderQuantity', function ($row) use ($nationalities) {
                    $item = $row->orderQuantity - $row->DelegatedQuantity ?? '';

                    return $item;
                })
                ->editColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->profession_id] ?? '';

                    return $item;
                })
                ->editColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->specialization_id] ?? '';

                    return $item;
                })
                ->addColumn('Delegation', function ($row) use ($is_admin, $can_view_order_delegations, $can_delegate_operation_order,   $can_add_operation_order_visa) {
                    $html = '';

                    if ($row->orderQuantity - $row->DelegatedQuantity != 0) {
                        if ($is_admin || $can_delegate_operation_order) {
                            $html .= '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'unSupportedDelegation'], [$row->id]) . '" class="btn btn-xs btn-warning btn-modal" data-container=".view_modal"><i class="fas fa-plus" aria-hidden="true"></i> ' . __('internationalrelations::lang.Delegation') . '</a>&nbsp;';
                        }
                    }
                    //  else {
                    //     if ($is_admin || $can_view_order_delegations) {
                    //         $html .= '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'viewDelegation'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('internationalrelations::lang.viewDelegation') . '</a>&nbsp;';
                    //     }
                    // }

                    if ($row->has_visa != 1) {
                        if ($is_admin || $can_add_operation_order_visa) {
                            $html .= '<button data-id="' . $row->id . '" class="btn btn-xs btn-info btn-add-visa" data-toggle="modal" data-target="#addVisaModal">
                                    <i class="fas fa-plus" aria-hidden="true"></i> ' . __('internationalrelations::lang.addvisa') . '
                                </button>';
                        }
                    }
                    return $html;
                })

                ->removeColumn('id')
                ->rawColumns(['Delegation'])
                ->make(true);
        }


        return view('internationalrelations::orderRequest.un_supported')->with(compact('orders'));
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


    public function getNationalities(Request $request)
    {
        $orderId = $request->input('orderId');

        $delegations = IrDelegation::where('operation_order_id', $orderId)->get();

        $transactionSellLineIds = $delegations->pluck('transaction_sell_line_id')->unique();


        $services = TransactionSellLine::whereIn('id', $transactionSellLineIds)
            ->with('service.nationality')
            ->get();


        $nationalities = $services->pluck('service.nationality')
            ->unique()
            ->values()
            ->all();

        return response()->json(['success' => true, 'data' => ['nationalities' => $nationalities]]);
    }

    public function getUnSupportedNationalities(Request $request)
    {
        $orderId = $request->input('orderId');

        $nationality = IrDelegation::where('unSupported_operation_id', $orderId)
            ->with('unSupported_operation.unSupported_worker.nationality')
            ->first();

        $nationalities = [];

        if ($nationality && $nationality->unSupported_operation && $nationality->unSupported_operation->unSupported_worker && $nationality->unSupported_operation->unSupported_worker->nationality) {

            $nationalities[] = $nationality->unSupported_operation->unSupported_worker->nationality;
        }

        return response()->json(['success' => true, 'data' => ['nationalities' => $nationalities]]);
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
