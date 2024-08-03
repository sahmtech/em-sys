<?php

namespace Modules\FollowUp\Http\Controllers;

use App\AccessRole;
use App\AccessRoleProject;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Notifications\CustomerNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\BusinessLocation;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use App\Transaction;
use App\Contact;
use App\User;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\FollowUp\Entities\FollowupUserAccessProject;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\SalesOrdersOperation;
use Modules\Sales\Entities\SalesProject;

class FollowUpOperationOrderController extends Controller
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
        //   $contracts= salesContract::all()->pluck('id','contract_number');
        $contracts = DB::table('sales_orders_operations')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
            ->select('sales_contracts.number_of_contract as contract_number')
            ->get();

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $can_followup_crud_operation_orders = auth()->user()->can('followup.crud_operation_orders');
        if (!($is_admin || $can_followup_crud_operation_orders)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $operations = DB::table('sales_orders_operations')
            ->leftjoin('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
            ->select(
                'sales_orders_operations.id as id',
                'sales_orders_operations.operation_order_no as operation_order_no',
                'sales_orders_operations.operation_order_no as orderQuantity',
                'contacts.supplier_business_name as contact_name',
                'sales_contracts.number_of_contract as contract_number',
                'sales_orders_operations.operation_order_type as operation_order_type',
                'sales_orders_operations.Status as Status'
            );
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $contacts_ids =   SalesProject::whereIn('id', $followupUserAccessProject)->pluck('contact_id')->unique()->toArray();
            $operations->whereIn('contacts.id', $contacts_ids);
        }



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

                ->addColumn('show_operation', function ($row) {

                    $html = '';
                    $html = '<a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                    return $html;
                })
                // ->addColumn('action', function ($row) {
                //     $html = '';
                //     $html .= '<a href="#" class="btn-modal" data-toggle="modal" data-target="#edit_order" data-row-id="' . $row->id . '"><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.edit_order') . '</a>';
                //     return $html;
                // })

                ->rawColumns(['show_operation', 'action'])
                ->removeColumn('id')
                ->make(true);
        }

        $status = [
            'Done' => __('sales::lang.Done'),
            'Under_process' => __('sales::lang.Under_process'),
            'Not_started' => __('sales::lang.Not_started'),

        ];
        $leads = Contact::whereIn('type', ['customer', 'lead'])

            ->where('business_id', $business_id)
            ->pluck('supplier_business_name', 'id');

        $agencies = Contact::where('type', 'agency')
            ->where('business_id', $business_id)
            ->pluck('supplier_business_name', 'id');

        return view('followup::operation_orders.index')->with(compact('contracts', 'leads', 'agencies', 'status'));
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

        $business_id = $request->session()->get('user.business_id');

        try {

            DB::transaction(function () use ($request) {
                $operation_order = [
                    'contact_id', 'sale_contract_id', 'operation_order_type', 'quantity',
                    'Interview', 'Location', 'Delivery', 'Note', 'Industry', 'status',
                ];
                $operation_details = $request->only($operation_order);

                $latestRecord = SalesOrdersOperation::orderBy('operation_order_no', 'desc')->first();

                if ($latestRecord) {
                    $latestRefNo = $latestRecord->operation_order_no;

                    $numericPart = (int)substr($latestRefNo, 3);
                    $numericPart++;

                    $operation_details['operation_order_no'] = 'POP' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
                } else {

                    $operation_details['operation_order_no'] = 'POP1111';
                }

                $operation_details['Status'] = $request->input('status');
                $operation_details['orderQuantity'] = $request->input('quantity');


                $operation = SalesOrdersOperation::create($operation_details);
            });

            $output = [
                'success' => 1,
                'msg' => __('sales::lang.operationOrder_added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        // return $output;
        return redirect()->route('operation_orders')->with($output);
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
        return $request;
        try {

            DB::transaction(function () use ($request) {
                $operation_order = [
                    'contact_id', 'sale_contract_id', 'operation_order_type',
                    'Interview', 'Location', 'Delivery', 'Note', 'Industry', 'status',
                ];
                $operation_details = $request->only($operation_order);

                $operation_details['Status'] = $request->input('status');

                // $operation = SalesOrdersOperation::where('id',$id)->update($operation_details);
            });

            $output = [
                'success' => 1,
                'msg' => __('sales::lang.operationOrder_added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        // return $output;
        return redirect()->route('operation_orders')->with($output);
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
