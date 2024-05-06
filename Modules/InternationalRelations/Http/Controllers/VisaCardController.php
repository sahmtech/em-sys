<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\Contact;
use App\TransactionSellLine;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\SalesOrdersOperation;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\SalesUnSupportedOperationOrder;
use Modules\InternationalRelations\Entities\IrVisaCard;

use Modules\InternationalRelations\Entities\IrDelegation;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\InternationalRelations\Entities\IrProposedLabor;

class VisaCardController extends Controller
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
        $can_crud_visa_card = auth()->user()->can('internationalrelations.crud_visa_cards');
        if (!($is_admin || $can_crud_visa_card)) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $visaCards = IrVisaCard::whereNotNull('operation_order_id')
            ->with(
                'operationOrder.contact',
                'delegation',
                'transaction_sell_line',
                'operationOrder.salesContract.transaction.sell_lines.agencies',
                'operationOrder.salesContract.transaction.sell_lines.service'
            )->get();
        //dd($visaCards[1]->transaction_sell_line_id);
        // dd($visaCards[1]->delegation($visaCards[1]->transaction_sell_line_id)->targeted_quantity);

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        if ($request->ajax()) {
            return Datatables::of($visaCards)
                ->addColumn('operation_order_no', function ($row) {
                    return optional($row->operationOrder)->operation_order_no;
                })
                ->addColumn('supplier_business_name', function ($row) {
                    return optional($row->operationOrder->contact)->supplier_business_name;
                })
                ->addColumn('number_of_contract', function ($row) {
                    return optional($row->operationOrder->salesContract)->number_of_contract;
                })
                ->addColumn('nationality_list', function ($row) use ($nationalities) {
                    $nationalityId = "";
                    $transactionSellLine = $row->transaction_sell_line;
                    if ($transactionSellLine) {
                        $service = $transactionSellLine->service;
                        if ($service) {
                            $nationalityId = $service->nationality_id;
                            return $nationalities[$nationalityId];
                        } else {
                            return $nationalityId;
                        }
                    }
                    // $nationalityNames = $sellLines->map(function ($sellLine) use ($nationalities) {
                    //     return optional($sellLine->service)->nationality_id;
                    // })->filter()->map(function ($nationalityId) use ($nationalities) {
                    //     return '<li>' . $nationalities[$nationalityId] . '</li>';
                    // })->implode('');
                    // return '<ul>' . $nationalityNames . '</ul>';

                })
                ->addColumn('profession_list', function ($row) use ($professions) {

                    // $professionsNames = $sellLines->map(function ($sellLine) use ($professions) {
                    //     return optional($sellLine->service)->profession_id;
                    // })->filter()->map(function ($professionId) use ($professions) {
                    //     return '<li>' . $professions[$professionId] . '</li>';
                    // })->implode('');
                    // return '<ul>' . $professionsNames . '</ul>';
                    // $professionId = "";

                    $professionId = "";
                    $transactionSellLine = $row->transaction_sell_line;
                    if ($transactionSellLine) {
                        $service = $transactionSellLine->service;
                        if ($service) {
                            $professionId = $service->profession_id;
                            return $professions[$professionId];
                        } else {
                            return $professionId;
                        }
                    }
                })

                ->addColumn(
                    'agency_name',
                    function ($row) {
                        $operationOrderId = $row->operationOrder->id;
                        error_log($operationOrderId);
                        $visa_transaction_sell_line_id = $row->transaction_sell_line_id;
                        error_log($visa_transaction_sell_line_id);
                        $delegation = IrDelegation::where('operation_order_id', $operationOrderId)
                            ->where('transaction_sell_line_id', $visa_transaction_sell_line_id)
                            ->first();
                        error_log($delegation);
                        $agency = null;

                        if ($delegation) {
                            $agency = Contact::where('id', $delegation->agency_id)->first();
                            error_log('agency', $agency->id);
                            return $agency?->supplier_business_name ?? null;
                        }



                        // if ($irDelegations) {
                        //     $agencyNames = $irDelegations->flatMap(function ($delegation) {
                        //         $agency = Contact::where('id', $delegation->agency_id)->first();
                        //         if ($agency) {
                        //             return ["<li>{$agency->supplier_business_name}</li>"];
                        //         }
                        //         return [];
                        //     })->unique()->implode('');

                        //     return "<ul>{$agencyNames}</ul>";
                        // } else {
                        //     return '';
                        // }
                    }
                )


                // ->addColumn('orderQuantity', function ($row) {
                //     return $row->delegation ? $row->delegation->targeted_quantity : null;
                // })
                ->addColumn('orderQuantity', function ($row) {
                    $operationOrderId = $row->operationOrder->id;

                    $visa_transaction_sell_line_id = $row->transaction_sell_line_id;

                    $delegation = IrDelegation::where('operation_order_id', $operationOrderId)
                        ->where('transaction_sell_line_id', $visa_transaction_sell_line_id)
                        ->first();

                    return $delegation->targeted_quantity ?? null;
                })
                ->rawColumns(['nationality_list', 'agency_name', 'profession_list'])
                ->make(true);
        }

        $orders = DB::table('sales_orders_operations')
            ->where('operation_order_type', '=', 'External')
            ->whereNotIn('id', function ($query) {
                $query->select('operation_order_id')->from('ir_visa_cards');
            })
            ->pluck('operation_order_no', 'id');


        return view('internationalrelations::visa.index')->with(compact('orders'));
    }

    public function unSupported_visa_cards(Request $request)
    {


        $visaCards = IrVisaCard::whereNotNull('unSupported_operation_id')->with(
            'unSupported_operation',
            'unSupportedworker_order'
        );

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        if ($request->ajax()) {
            return Datatables::of($visaCards)
                ->addColumn('operation_order_no', function ($row) {
                    return optional($row->unSupported_operation)->operation_order_no;
                })
                ->addColumn('nationality_list', function ($row) use ($nationalities) {
                    return $nationalities[optional($row->unSupportedworker_order)->nationality_id] ?? '';
                })
                ->addColumn('profession_list', function ($row) use ($professions) {
                    return $professions[optional($row->unSupportedworker_order)->profession_id] ?? '';
                })
                ->addColumn('agency_name', function ($row) {
                    $irDelegations = IrDelegation::where('unSupported_operation_id', $row->unSupported_operation->id)->get();
                    $agencyNames = $irDelegations->flatMap(function ($delegation) {
                        $agency = Contact::where('id', $delegation->agency_id)->first();
                        if ($agency) {
                            return ["<li>{$agency->supplier_business_name}</li>"];
                        }
                        return [];
                    })->unique()->implode('');


                    return "<ul>{$agencyNames}</ul>";
                })
                ->addColumn('orderQuantity', function ($row) {
                    return optional($row->unSupported_operation)->orderQuantity;
                })
                ->rawColumns(['nationality_list', 'agency_name', 'operation_order_no', 'profession_list', 'orderQuantity'])
                ->make(true);
        }

        $orders = DB::table('sales_un_supported_operation_orders')
            ->whereNotIn('id', function ($query) {
                $query->select('unSupported_operation_id')->from('ir_visa_cards');
            })
            ->pluck('operation_order_no', 'id');

        return view('internationalrelations::visa.unSupported_visa_cards')->with(compact('orders'));
    }
    public function getVisaReport()
    {
        $visaCards = IrVisaCard::with(['delegation'])->get();

        return view('internationalrelations::visa.visa_report')
            ->with(compact('visaCards'));
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
    // public function store(Request $request)
    // {

    //     

    //     $business_id = request()->session()->get('user.business_id');

    //     $can_store_visa_card = auth()->user()->can('internationalrelations.store_visa_card');
    //     if (!($isSuperAdmin || $can_store_visa_card)) {
    //        //temp  abort(403, 'Unauthorized action.');
    //     }
    //     try {
    //         DB::transaction(function () use ($request) {
    //             $filePath = null;
    //             if (request()->hasFile('file')) {
    //                 $file = request()->file('file');
    //                 $filePath = $file->store('/visa_cards');
    //             }

    //             $visaDetails = [
    //                 'visa_number' => $request->input('visa_number'),
    //                 'file' => $filePath,
    //                 'operation_order_id' => $request->input('id'),
    //             ];

    //             DB::table('ir_visa_cards')->insert($visaDetails);
    //             SalesOrdersOperation::where('id', $request->input('id'))->update(['has_visa' => '1']);
    //         });

    //         $output = [
    //             'success' => 1,
    //             'msg' => __('sales::lang.operationOrder_added_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => 0,
    //             'msg' => $e->getMessage(),
    //         ];
    //     }

    //     return redirect()->route('order_request')->with($output);
    // }

    public function store(Request $request)
    {

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->input('visa_number') as $nationalityId => $visaNumber) {
                    $filePath = null;
                    if ($request->hasFile('file') && $request->hasFile("file.{$nationalityId}")) {
                        $file = $request->file("file.{$nationalityId}");
                        $filePath = $file->store('/visa_cards');
                    }
                    $sellLines = TransactionSellLine::whereHas('service.nationality', function ($query) use ($nationalityId) {
                        $query->where('id', $nationalityId);
                    })->first();
                    $visaDetails = [
                        'visa_number' => $visaNumber,
                        'file' => $filePath,
                        'start_date' => \Carbon::now(),
                        'operation_order_id' => $request->input('id') ?? null,
                        'transaction_sell_line_id' => $sellLines->id,
                    ];

                    DB::table('ir_visa_cards')->insert($visaDetails);
                }


                SalesOrdersOperation::where('id', $request->input('id'))->update(['has_visa' => '1']);
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

        return redirect()->back()->with($output);
    }
    public function unSupportedVisaStore(Request $request)
    {


        try {
            DB::transaction(function () use ($request) {
                foreach ($request->input('visa_number') as $nationalityId => $visaNumber) {
                    $filePath = null;
                    if ($request->hasFile('file') && $request->hasFile("file.{$nationalityId}")) {
                        $file = $request->file("file.{$nationalityId}");
                        $filePath = $file->store('/visa_cards');
                    }
                    $order = SalesUnSupportedOperationOrder::where('id', $request->input('unSupported_operation_id'))->first();
                    $visaDetails = [
                        'visa_number' => $visaNumber,
                        'file' => $filePath,
                        'start_date' => \Carbon::now(),
                        'unSupported_operation_id' => $request->input('unSupported_operation_id') ?? null,
                        'unSupportedworker_order_id' => $order->workers_order_id ?? null
                    ];

                    DB::table('ir_visa_cards')->insert($visaDetails);
                }


                SalesUnSupportedOperationOrder::where('id', $request->input('unSupported_operation_id'))->update(['has_visa' => '1']);
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

        return redirect()->back()->with($output);
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function viewVisaWorkers($visaId)
    {



        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_visa_workers = auth()->user()->can('internationalrelations.view_visa_workers');
        $can_change_arrival_date = auth()->user()->can('internationalrelations.change_arrival_date');
        if (!($is_admin || $can_view_visa_workers)) {
            //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $nationalities = EssentialsCountry::nationalityForDropdown();
            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');
            $business_id = request()->session()->get('user.business_id');
            $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');

            $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')
                ->where('visa_id', $visaId)
                ->select([
                    'id',
                    FacadesDB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
                    'is_price_offer_sent',
                    'is_accepted_by_worker',
                    'medical_examination', 'fingerprinting', 'is_passport_stamped', 'passport_number', 'date_of_offer',
                    'agency_id', 'transaction_sell_line_id', 'arrival_date'
                ]);


            if (request()->ajax()) {

                return Datatables::of($workers)

                    ->addColumn('profession_id', function ($row) use ($professions) {
                        $item = '';
                        if ($row->transactionSellLine) {
                            $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';
                        }
                        return $item;
                    })
                    ->addColumn('nationality_id', function ($row) use ($nationalities) {
                        $item = '';
                        if ($row->transactionSellLine) {
                            $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';
                        }
                        return $item;
                    })
                    ->addColumn('change_arrival_date', function ($row) use ($is_admin, $can_change_arrival_date) {
                        if ($is_admin || $can_change_arrival_date) {
                            if (!empty($row->arrival_date)) {
                                return '<button type="button" class="btn btn-success change-arrival-date" 
                                    data-worker-id="' . $row->id . '" 
                                    data-arrival-date="' . $row->arrival_date . '" 
                                    data-toggle="modal" 
                                    data-target="#changeArrivalDateModal">' . $row->arrival_date . '</button>';
                            } else {
                                return " ";
                            }
                        } else {
                            return  $row->arrival_date;
                        }
                    })
                    ->editColumn('agency_id', function ($row) use ($agencys) {

                        if ($row->agency_id) {
                            return $agencys[$row->agency_id];
                        } else {
                            return '';
                        }
                    })
                    ->editColumn('medical_examination', function ($row) {
                        $text = $row->medical_examination == 1
                            ? __('lang_v1.done')
                            : __('lang_v1.not_yet');

                        $color = $row->medical_examination == 1
                            ? 'green'
                            : 'red';

                        return '<span style="color: ' . $color . ';">' . $text . '</span>';
                    })
                    ->editColumn('fingerprinting', function ($row) {
                        $text = $row->fingerprinting == 1
                            ? __('lang_v1.done')
                            : __('lang_v1.not_yet');

                        $color = $row->fingerprinting == 1
                            ? 'green'
                            : 'red';

                        return '<span style="color: ' . $color . ';">' . $text . '</span>';
                    })
                    ->editColumn('is_passport_stamped', function ($row) {
                        $text = $row->is_passport_stamped;
                        return  $text;
                    })
                    ->rawColumns(['is_passport_stamped', 'fingerprinting', 'medical_examination', 'change_arrival_date'])

                    ->make(true);
            }
            $visaCard = IrVisaCard::where('id', $visaId)->with('operationOrder')->first();
            error_log($visaCard);
            $agencyId = null;


            if ($visaCard && $visaCard->operationOrder) {
                $operationOrderId = $visaCard->operationOrder->id;
                $visa_transaction_sell_line_id = $visaCard->transaction_sell_line_id;
                error_log($operationOrderId);
                error_log($visa_transaction_sell_line_id);

                $delegations = IrDelegation::where('operation_order_id', $operationOrderId)
                    ->where('transaction_sell_line_id', $visa_transaction_sell_line_id)
                    ->get();
                error_log($delegations);

                $agencyId = $delegations->pluck('agency_id')->unique();
            }
            error_log($agencyId);

            $workers = IrProposedLabor::where('agency_id', $agencyId)
                ->whereNull('visa_id')
                ->where('is_accepted_by_worker', 1)
                ->get();

            error_log($workers);
            $workersOptions = $workers->map(function ($worker) {
                return [
                    'id' => $worker->id,
                    'full_name' => sprintf(
                        '%s %s - %s',
                        $worker->first_name ?? '',
                        $worker->last_name ?? '',
                        $worker->passport_number ?? ''
                    ),
                ];
            })->pluck('full_name', 'id')->toArray();



            return response()->view('internationalrelations::visa.show', compact('visaId', 'workers', 'workersOptions',));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function viewUnSuupportedVisaWorkers($visaId)
    {



        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_visa_workers = auth()->user()->can('internationalrelations.view_visa_workers');
        $can_change_arrival_date = auth()->user()->can('internationalrelations.change_arrival_date');
        if (!($is_admin || $can_view_visa_workers)) {
            //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $nationalities = EssentialsCountry::nationalityForDropdown();
            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');
            $business_id = request()->session()->get('user.business_id');
            $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
            $workers = IrProposedLabor::with('unSupportedworker_order', 'agency')->where('visa_id', $visaId)->select([
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
                'is_price_offer_sent',
                'is_accepted_by_worker',
                'medical_examination', 'fingerprinting', 'is_passport_stamped', 'passport_number', 'date_of_offer',
                'agency_id', 'unSupportedworker_order_id', 'arrival_date'
            ]);


            if (request()->ajax()) {

                return Datatables::of($workers)

                    ->addColumn('profession_id', function ($row) use ($professions) {
                        $item = '';
                        if ($row->unSupportedworker_order) {
                            $item = $professions[$row->unSupportedworker_order->profession_id] ?? '';
                        }
                        return $item;
                    })
                    ->addColumn('nationality_id', function ($row) use ($nationalities) {
                        $item = '';
                        if ($row->unSupportedworker_order) {
                            $item = $nationalities[$row->unSupportedworker_order->nationality_id] ?? '';
                        }
                        return $item;
                    })
                    ->addColumn('change_arrival_date', function ($row) use ($is_admin, $can_change_arrival_date) {
                        if ($is_admin || $can_change_arrival_date) {
                            if (!empty($row->arrival_date)) {
                                return '<button type="button" class="btn btn-success change-arrival-date" 
                                    data-worker-id="' . $row->id . '" 
                                    data-arrival-date="' . $row->arrival_date . '" 
                                    data-toggle="modal" 
                                    data-target="#changeArrivalDateModal">' . $row->arrival_date . '</button>';
                            } else {
                                return " ";
                            }
                        } else {
                            return  $row->arrival_date;
                        }
                    })
                    ->editColumn('agency_id', function ($row) use ($agencys) {

                        if ($row->agency_id) {
                            return $agencys[$row->agency_id];
                        } else {
                            return '';
                        }
                    })
                    ->editColumn('medical_examination', function ($row) {
                        $text = $row->medical_examination == 1
                            ? __('lang_v1.done')
                            : __('lang_v1.not_yet');

                        $color = $row->medical_examination == 1
                            ? 'green'
                            : 'red';

                        return '<span style="color: ' . $color . ';">' . $text . '</span>';
                    })
                    ->editColumn('fingerprinting', function ($row) {
                        $text = $row->fingerprinting == 1
                            ? __('lang_v1.done')
                            : __('lang_v1.not_yet');

                        $color = $row->fingerprinting == 1
                            ? 'green'
                            : 'red';

                        return '<span style="color: ' . $color . ';">' . $text . '</span>';
                    })
                    ->editColumn('is_passport_stamped', function ($row) {
                        $text = $row->is_passport_stamped;
                        return  $text;
                    })
                    ->rawColumns(['is_passport_stamped', 'fingerprinting', 'medical_examination', 'change_arrival_date'])

                    ->make(true);
            }
            $visaCard = IrVisaCard::where('id', $visaId)->with('unSupported_operation')->first();

            $agencyIds = [];


            if ($visaCard && $visaCard->unSupported_operation) {
                $operationOrderId = $visaCard->unSupported_operation->id;


                $delegations = IrDelegation::where('unSupported_operation_id', $operationOrderId)->get();


                $agencyIds = $delegations->pluck('agency_id')->unique()->toArray();
            }

            $workers = IrProposedLabor::where(function ($query) use ($agencyIds) {
                $query->whereNull('agency_id')
                    ->orWhereIn('agency_id', $agencyIds);
            })
                ->whereNull('visa_id')
                ->where('is_accepted_by_worker', 1)
                ->get();






            $workersOptions = $workers->map(function ($worker) {
                return [
                    'id' => $worker->id,
                    'full_name' => sprintf(
                        '%s %s - %s',
                        $worker->first_name ?? '',
                        $worker->last_name ?? '',
                        $worker->passport_number ?? ''
                    ),
                ];
            })->pluck('full_name', 'id')->toArray();



            return response()->view('internationalrelations::visa.show', compact('visaId', 'workersOptions'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function changeArrivalDate(Request $request)
    {
        try {
            $proposal_worker_id = $request->input('worker_id');
            $new_arrival_date = $request->input('arrival_date');

            IrProposedLabor::where('id', $proposal_worker_id)
                ->update(['arrival_date' => $new_arrival_date]);

            $output = [
                'success' => true,
                'msg' => __('internationalrelations::lang.success_update_arriavl_date'),
            ];
            return response()->json($output);
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return response()->json($output);
        }
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
