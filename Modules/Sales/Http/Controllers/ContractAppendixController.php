<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\salesContractAppendic;
use Modules\Sales\Entities\salesContract;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Http\Response;
use Modules\Sales\Entities\salesContractItem;
class ContractAppendixController extends Controller
{
    protected $moduleUtil;
   

     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
    
     public function index()
     {
     
        $business_id = request()->session()->get('user.business_id');
 
         if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
             abort(403, 'Unauthorized action.');
         }
         $can_crud_contract_appendics= auth()->user()->can('sales.crud_contract_appendics');
         if (! $can_crud_contract_appendics) {
             abort(403, 'Unauthorized action.');
         }
         $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
         $contracts=salesContract::all()->pluck('number_of_contract','id');
         $items=salesContractItem::all()->pluck('name_of_item','id');

         if (request()->ajax()) {
             $appendics = DB::table('sales_contract_appendics')->select(['id','number_of_appendix','notes','contract_id', 'contract_item_id']);
            
             if (!empty(request()->input('contract')) && request()->input('contract') !== 'all') {
                $appendics->where('contract_id', request()->input('contract'));
            }
             return Datatables::of($appendics)
             ->editColumn('contract_id',function($row)use($contracts){
                $item = $contracts[$row->contract_id]??'';

                return $item;
            })
            ->editColumn('contract_item_id',function($row)use($items){
                $item = $items[$row->contract_item_id]??'';

                return $item;
            })
             ->addColumn(
                 'action',
                 function ($row) use ($is_admin) {
                     $html = '';
                     if ($is_admin) {
                         $html .= '<a href="'. route('appendix.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                         &nbsp;';
                         $html .= '<button class="btn btn-xs btn-danger delete_appendix_button" data-href="' . route('appendix.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                     }
         
                     return $html;
                 }
             )
             ->filterColumn('number_of_appendix', function ($query, $keyword) {
                 $query->where('number_of_appendix', 'like', "%{$keyword}%");
             })
             
             ->rawColumns(['action'])
             ->make(true);
         
         
            }
       return view('sales::contract_appendics.index')->with(compact('contracts'));
     }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
  
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['contract', 'appendixItemId', 'notes']);
            
            $input['contract_id'] = $input['contract'];      
            $input['contract_item_id'] = $input['appendixItemId'];      
            $input['notes'] = $input['notes'];
            $latestRecord = salesContractAppendic::orderBy('number_of_appendix', 'desc')->first();

        
            if ($latestRecord) {
                $latestRefNo = $latestRecord->number_of_appendix;
                $numericPart = (int)substr($latestRefNo, 3); 
                $numericPart++;
                $input['number_of_appendix'] = 'CAP' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            } else {
                
                $input['number_of_appendix'] = 'CAP0001';
            }
    
            $var=salesContractAppendic::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       return redirect()->route('contract_appendices');
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
        $contracts=salesContract::all()->pluck('number_of_contract','id');
        $appendix =salesContractAppendic::findOrFail($id);
        $item=salesContractItem::whereId($appendix->contract_item_id)->first();
        $contract=salesContract::whereId($appendix->contract_id)->first()->number_of_contract;
        error_log($contract);
        return view('sales::contract_appendics.edit')->with(compact('item','appendix','contract','contracts'));
    }

    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
             
        try {
            $input = $request->only(['contract', 'number_of_item', 'name_of_item', 'notes']);

            
            $contractId = $input['contract'];
            $number_of_item = $input['number_of_item'];
            $name_of_item = $input['name_of_item'];
            $notes = $input['notes'];
            
            $salesContractAppendic = salesContractAppendic::find($id);
            $oldContract=$salesContractAppendic->contract_id;
            if($salesContractAppendic && $input['contract'] != NULL)
            {
                $salesContractAppendic->contract_id = $contractId;
                $salesContractAppendic->notes = $notes;
                $salesContractAppendic->save();
            }
            if($salesContractAppendic && $input['contract'] == Null)
            {

                $salesContractAppendic->contract_id = $oldContract;
                $salesContractAppendic->notes = $notes;
                $salesContractAppendic->save();
            }
            // Update salesContractItem
            $salesContractItem = salesContractItem::find($salesContractAppendic->contract_item_id);
            
            if ($salesContractItem) {
                $salesContractItem->number_of_item = $number_of_item;
                $salesContractItem->name_of_item = $name_of_item;
                $salesContractItem->save();
            }
            

            
            
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('contract_appendices');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            salesContractAppendic::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       
       return $output;

    }
}
