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
use Modules\InternationalRelations\Entities\IrVisaCard;

use Modules\InternationalRelations\Entities\IrDelegation;
use DB;
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
        $visaCards = IrVisaCard::with(
            'operationOrder.contact',
            'operationOrder.salesContract.transaction.sell_lines.agencies',
            'operationOrder.salesContract.transaction.sell_lines.service'
        );

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
                    $sellLines = optional($row->operationOrder->salesContract->transaction->sell_lines);

                    $nationalityNames = $sellLines->map(function ($sellLine) use ($nationalities) {
                        return optional($sellLine->service)->nationality_id;
                    })->filter()->map(function ($nationalityId) use ($nationalities) {
                        return '<li>' . $nationalities[$nationalityId] . '</li>';
                    })->implode('');

                    return '<ul>' . $nationalityNames . '</ul>';
                })
                ->addColumn('profession_list', function ($row) use ($professions) {
                    $sellLines = optional($row->operationOrder->salesContract->transaction->sell_lines);

                    $professionsNames = $sellLines->map(function ($sellLine) use ($professions) {
                        return optional($sellLine->service)->profession_id;
                    })->filter()->map(function ($professionId) use ($professions) {
                        return '<li>' . $professions[$professionId] . '</li>';
                    })->implode('');

                    return '<ul>' . $professionsNames . '</ul>';
                })

                ->addColumn('agency_name', function ($row) {
                    $sellLines = $row->operationOrder->salesContract->transaction->sell_lines;

                    $agencyNames = $sellLines->flatMap(function ($sellLine) {
                        return $sellLine->agencies->pluck('supplier_business_name')->map(function ($name) {
                            return "<li>$name</li>";
                        });
                    })->implode('');

                    return "<ul>$agencyNames</ul>";
                })
                ->addColumn('orderQuantity', function ($row) {
                    return optional($row->operationOrder)->orderQuantity;
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


        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_store_visa_card = auth()->user()->can('internationalrelations.store_visa_card');
        if (!($is_admin || $can_store_visa_card)) {
            //temp  abort(403, 'Unauthorized action.');
        }

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
                        'operation_order_id' => $request->input('id'),
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
                'msg' => __('messages.somthing_went_wrong'),
            ];
        }

        return redirect()->route('order_request')->with($output);
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
            $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('visa_id', $visaId)->select([
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
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
            $visaCards = IrVisaCard::where('id', $visaId)->with('operationOrder.salesContract.transaction.sell_lines')->first();
            $sellLineIds = $visaCards->operationOrder->salesContract->transaction->sell_lines->pluck('id')->toArray();
            // dd($sellLineIds);

            $workers = IrProposedLabor::where(function ($query) use ($sellLineIds) {
                $query->whereNull('transaction_sell_line_id')
                    ->orWhereIn('transaction_sell_line_id', $sellLineIds);
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



            return response()->view('internationalrelations::visa.show', compact('visaId', 'workersOptions',));

            return response()->view('internationalrelations::visa.show', compact('visaId', 'workers',));
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
                'msg' => __('messages.somthing_went_wrong'),
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
