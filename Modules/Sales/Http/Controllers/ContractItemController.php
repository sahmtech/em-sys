<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Http\Response;
use Modules\Sales\Entities\salesContractItem;

class ContractItemController extends Controller
{
    protected $moduleUtil;
   

     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
     public function index()
     {
     
        $business_id = request()->session()->get('user.business_id');
 

         $can_crud_contract_items= auth()->user()->can('sales.crud_contract_items');
         if (! $can_crud_contract_items) {
            //temp  abort(403, 'Unauthorized action.');
         }
         $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
 
         if (request()->ajax()) {
             $items = DB::table('sales_contract_items')->where('type','basic')->select(['id','number_of_item', 'name_of_item', 'details', 'type']);
                        
 
             return Datatables::of($items)
            
             ->addColumn(
                 'action',
                 function ($row) use ($is_admin) {
                     $html = '';
                     if ($is_admin) {
                         $html .= '<a href="'. route('item.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                         &nbsp;';
                         $html .= '<button class="btn btn-xs btn-danger delete_item_button" data-href="' . route('item.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                     }
         
                     return $html;
                 }
             )
             ->filterColumn('name_of_item', function ($query, $keyword) {
                 $query->where('name_of_item', 'like', "%{$keyword}%");
             })
             
             ->rawColumns(['action'])
             ->make(true);
         
         
             }
       return view('sales::contract_items.index');
     }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function storeAppindexItem(Request $request)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


 
        try {
            $input = $request->only(['number_of_item', 'name_of_item', 'details']);
            

            $input['number_of_item'] = $input['number_of_item'];
            
            $input['name_of_item'] = $input['name_of_item'];

            $input['type'] = 'appendix';
           
            $input['details'] = $input['details'];
         
            
            $appendixItem=salesContractItem::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
                'appendixItem'=>$appendixItem->id
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

       return $output;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


 
        try {
            $input = $request->only(['number_of_item', 'name_of_item', 'details']);
            

            $input['number_of_item'] = $input['number_of_item'];
            
            $input['name_of_item'] = $input['name_of_item'];
           
            $input['details'] = $input['details'];
         
            
            salesContractItem::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

       return redirect()->route('contract_itmes');
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $item = salesContractItem::findOrFail($id);


        return view('sales::contract_items.edit')->with(compact('item'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['number_of_item', 'name_of_item', 'details']);
            

            $input['number_of_item'] = $input['number_of_item'];
            
            $input['name_of_item'] = $input['name_of_item'];
           
            $input['details'] = $input['details'];
            
       
            
            salesContractItem::where('id', $id)->update($input);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('contract_itmes');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            salesContractItem::where('id', $id)
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
