<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\Contact;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\salesOrdersOperation;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\InternationalRelations\Entities\IrVisaCard;
use DB;
use Modules\InternationalRelations\Entities\IrProposedLabor;

class VisaCardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    { 
        $visaCards = IrVisaCard::with('operationOrder.contact','operationOrder.salesContract.transaction.sell_lines.agencies',
        'operationOrder.salesContract.transaction.sell_lines.service');
       
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
            ->rawColumns(['nationality_list','agency_name','profession_list'])
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
     
        try {
            DB::transaction(function () use ($request) {
                $visaDetails = [
                    'visa_number' => $request->input('visa_number'),
                    'arrival_date' => $request->input('arrival_date'),
                    'operation_order_id' => $request->input('operation_order'),
                ];
    
                DB::table('ir_visa_cards')->insert($visaDetails);
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
                'msg' => $e->getMessage(),
            ];
        }
    
        return redirect()->route('visa_cards')->with($output);
    }
    
    

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function viewVisaWorkers($visaId)
    {
        try {
            $nationalities = EssentialsCountry::nationalityForDropdown();
            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');
            $business_id = request()->session()->get('user.business_id');
            $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
            $workers = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('visa_id', $visaId)->select(['id',
            DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            'is_price_offer_sent',
            'is_accepted_by_worker',
            'medical_examination','fingerprinting','is_passport_stamped','passport_number','date_of_offer',
            'agency_id', 'transaction_sell_line_id'
                ]);
                   
            
             if (request()->ajax()) {
            
                return Datatables::of($workers)
                
                ->addColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->transactionSellLine->service->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine->service->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id];
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
                    $text = $row->is_passport_stamped == 1
                        ? __('lang_v1.done')
                        : __('lang_v1.not_yet');
                
                    $color = $row->is_passport_stamped == 1
                        ? 'green'
                        : 'red';
                
                    return '<span style="color: ' . $color . ';">' . $text . '</span>';
                })
                ->rawColumns(['is_passport_stamped','fingerprinting', 'medical_examination'])
                
                ->make(true);
            }
            $visaCards = IrVisaCard::where('id', $visaId)->with('operationOrder.salesContract.transaction.sell_lines')->first();
            $sellLineIds = $visaCards->operationOrder->salesContract->transaction->sell_lines->pluck('id')->toArray();
            $workers = IrProposedLabor::whereIn('transaction_sell_line_id', $sellLineIds)->where('visa_id',Null)->get();
            
            // Create the desired array for the select dropdown directly
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
            
    
         
            return response()->view('internationalrelations::visa.show', compact('visaId','workers'));

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
