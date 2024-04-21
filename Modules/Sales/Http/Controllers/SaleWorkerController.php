<?php

namespace Modules\Sales\Http\Controllers;

use App\Contact;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\SalesUnSupportedOperationOrder;
use Modules\Sales\Entities\SalesUnSupportedWorker;

class SaleWorkerController extends Controller
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
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_Unsupported_workers = auth()->user()->can('sales.Unsupported_workers');
        if (!($is_admin || $can_Unsupported_workers)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        if (request()->ajax()) {

            $unSupportedWorker = SalesUnSupportedWorker::all();



            return Datatables::of($unSupportedWorker)


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


                // ->addColumn(
                //     'attachments',
                //     function ($row) {
                //         $html = '';
                //         if (!empty($row->attachment)) {
                //             $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->attachment . '\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                //             '&nbsp;';
                //         } else {
                //             $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                //         }



                //         return $html;
                //     }
                // )

                ->removeColumn('id')
                //       ->rawColumns(['attachments'])
                ->make(true);
        }


        return view('sales::requests.unSupportedWorker')->with(compact('specializations', 'professions', 'nationalities'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function orderOperationForUnsupportedWorkers()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_show_sale_operation_order = auth()->user()->can('sales.show_sale_operation_order');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $orders = SalesUnSupportedWorker::where('status', '!=', 'ended')->pluck('order_no', 'id');

        $operations =
            DB::table('sales_un_supported_operation_orders')
            ->join('sales_un_supported_workers', 'sales_un_supported_operation_orders.workers_order_id', 'sales_un_supported_workers.id')
            ->select(
                'sales_un_supported_operation_orders.id as id',
                'sales_un_supported_operation_orders.operation_order_no as operation_order_no',
                'sales_un_supported_operation_orders.orderQuantity as orderQuantity',
                'sales_un_supported_operation_orders.Interview as Interview',
                'sales_un_supported_operation_orders.Industry as Industry',
                'sales_un_supported_operation_orders.Location as Location',
                'sales_un_supported_operation_orders.Delivery as Delivery',
                'sales_un_supported_operation_orders.status as Status',

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
                // ->addColumn('show_operation', function ($row) use ($is_admin, $can_show_sale_operation_order) {

                //     $html = '';
                //     if ($is_admin  || $can_show_sale_operation_order) {
                //         $html = '<a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                //     }
                //     return $html;
                // })

                // ->rawColumns(['show_operation', 'action'])
                ->removeColumn('id')
                ->make(true);
        }

        $status = [
            'done' => __('sales::lang.done'),
            'under_process' => __('sales::lang.nnder_process'),
            'not_started' => __('sales::lang.not_started'),

        ];


        $agencies = Contact::where('type', 'agency')
            ->where('business_id', $business_id)
            ->pluck('supplier_business_name', 'id');

        return view('sales::operation_order.un_supported')->with(compact('agencies', 'orders', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        try {
            $input = $request->only(['nationlity', 'quantity', 'date', 'attachment', 'profession', 'salary', 'specialization', 'note']);

            $input2['nationality_id'] = $input['nationlity'];
            $input2['date'] = $input['date'];
            $input2['note'] = $input['note'];
            $input2['total_quantity'] = $input['quantity'];
            $input2['remaining_quantity_for_operation'] = $input['quantity'];
            $input2['remaining_quantity_for_delegation'] = $input['quantity'];
            $input2['profession_id'] = $input['profession'];
            $input2['salary'] = $input['salary'];
            $input2['specialization_id'] = $input['specialization'];
            $latestRecord = SalesUnSupportedWorker::orderBy('order_no', 'desc')->first();

            if ($latestRecord) {
                $latestRefNo = $latestRecord->order_no;
                $numericPart = (int)substr($latestRefNo, 3);
                $numericPart++;
                $input2['order_no'] = 'USW' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            } else {
                $input2['order_no'] = 'USW1111';
            }

            if (isset($request->attachment) && !empty($request->attachment)) {
                $attachmentPath = $request->attachment->store('/unsupportedWorkersRequests');
                $input2['attachment'] = $attachmentPath;
            }

            SalesUnSupportedWorker::create($input2);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }




        return redirect()->route('Unsupported_workers');
    }


    public function getOrderDetails(Request $request)
    {
        $order = SalesUnSupportedWorker::where('id', $request->order_id)->first();
        $sumOfSalesOrdersQuantities = SalesUnSupportedOperationOrder::where('workers_order_id', $request->order_id)->sum('orderQuantity');
        $maxQuantity =  $order->total_quantity - $sumOfSalesOrdersQuantities;
        return $maxQuantity;
    }


    public function storeOrderOperation(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $operation_order = [
                    'order_id', 'quantity', 'Industry',
                    'Interview', 'Location', 'Delivery',
                ];

                $operation_details = $request->only($operation_order);

                $latestRecord = SalesUnSupportedOperationOrder::orderBy('operation_order_no', 'desc')->first();

                if ($latestRecord) {
                    $latestRefNo = $latestRecord->operation_order_no;
                    $numericPart = (int)substr($latestRefNo, 3);
                    $numericPart++;
                    $operation_details['operation_order_no'] = 'UOP' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
                } else {
                    $operation_details['operation_order_no'] = 'UOP1111';
                }

                $operation_details['orderQuantity'] = $request->input('quantity');
                $operation_details['workers_order_id'] = $request->input('order_id');

                SalesUnSupportedOperationOrder::create($operation_details);
                $order = SalesUnSupportedWorker::where('id', $request->input('order_id'))->first();


                if ($order) {
                    $order->remaining_quantity_for_operation = $order->remaining_quantity_for_operation - $request->input('quantity');

                    if ($order->remaining_quantity_for_operation == 0) {
                        $order->status = 'ended';
                    } else {
                        $order->status = 'pending';
                    }

                    $order->save();
                }
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


        return redirect()->route('sale.orderOperationForUnsupportedWorkers')->with($output);
    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('sales::edit');
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
