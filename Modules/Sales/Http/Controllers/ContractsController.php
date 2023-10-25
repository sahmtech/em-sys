<?php

namespace Modules\Sales\Http\Controllers;

use App\Contact;
use App\Transaction;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use App\Utils\ModuleUtil;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\salesContractItem;

class ContractsController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
       
        $business_id = request()->session()->get('user.business_id');
       

     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_contracts= auth()->user()->can('sales.crud_contract');
        if (! $can_crud_contracts) {
            abort(403, 'Unauthorized action.');
        }
        $contacts=Contact::all()->pluck('supplier_business_name','id');
        $offer_prices = Transaction::where([['type','=','sell'],['status','=','approved']])->pluck('ref_no','id');
        $items=salesContractItem::pluck('name_of_item','id');
        if (request()->ajax()) {
    
                $contracts = salesContract::join('transactions','transactions.id','=','sales_contracts.offer_price_id')->
                select(['sales_contracts.number_of_contract','sales_contracts.id','sales_contracts.offer_price_id','sales_contracts.start_date',
                'sales_contracts.end_date','sales_contracts.status','sales_contracts.operation_order','sales_contracts.file',
                'transactions.contract_form as contract_form','transactions.contact_id','transactions.id as tra']);

                if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                    $contracts->where('sales_contracts.status', request()->input('status'));
                }
                if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                    $contracts->where('transactions.contract_form', request()->input('contract_form'));
                }
            return Datatables::of($contracts)

          
            ->editColumn('contact_id',function($row)use($contacts){
                $item = $contacts[$row->contact_id]??'';

                return $item;
            })
            ->addColumn('operation_order', function ($row) {
                if ($row->operation_order == 0) {
                    return '<button class="btn btn-xs btn-primary add-new-order-button">Add New Order</button>';
                } else {
                    return $row->operation_order;
                }
            })
            
            ->addColumn(
                'action',
                function ($row) {
                    $html = '';
                    $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/'.$row->file.'\'"><i class="fa fa-eye"></i> ' . __('sales::lang.contract_view') . '</button>';
                    $html .= '&nbsp;'; 
                    $html .= '<button class="btn btn-xs btn-warning btn-modal" data-container=".view_modal" data-href="' . route('offer.view', ['id' => $row->tra]) . '"><i class="fa fa-eye"></i> ' . __('sales::lang.offer_price_view') . '</button>';
                    $html .= '&nbsp;'; 
                    $html .= '<button class="btn btn-xs btn-danger delete_contract_button" data-href="' . route('contract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
            
                    return $html;
                }
            )
            
                
            
                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })
            
                ->rawColumns(['action','operation_order'])
                ->make(true);
                }
             
               
        
        return view('sales::contracts.index')->with(compact('offer_prices','items'));
    }
   

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }
   

    public function getContractValues(Request $request)
    {
        
         $offerPrice = $request->input('offer_price');
            $contact = Transaction::whereId($offerPrice)->first()->contact_id;
   
           
        $contract_signer = User::where([
            ['crm_contact_id', $contact],
            ['contact_user_type', 'contact_signer']
        ])->first();
        $contract_follower = User::where([
            ['crm_contact_id', $contact],
            ['contact_user_type', 'contract_follower']
        ])->first();
    
        
            

            return response()->json([
                'contract_follower' => $contract_follower,
                'contract_signer' => $contract_signer
            ]);
    }
        
    
    
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
    //  return $request;
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['offer_price', 'start_date', 'end_date','status','contract_items','is_renewable','notes','operation_order','file']);
            
            $input2['offer_price_id'] = $input['offer_price'];
            $input2['start_date'] = $input['start_date'];
            $input2['end_date'] = $input['end_date'];
            $input2['status'] = $input['status'];
            $input2['is_renwable'] = $input['is_renewable'];
            $input2['notes'] = $input['notes'];

            if ($request->has('operation_order')) {
                $input2['operation_order'] = $input['operation_order'];
            }
            
            $latestRecord = salesContract::orderBy('number_of_contract', 'desc')->first();
            if ($latestRecord) {

                $latestRefNo = $latestRecord->number_of_contract;
                $numericPart = (int)substr($latestRefNo, 3); 
                $numericPart++;
                $input2['number_of_contract'] = 'CR' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            } else {
                
                $input2['number_of_contract'] = 'CR0001';
            }
            $selectedItems = $request->input('contract_items');
            $selectedItems = array_filter($selectedItems, function ($item) {
                
                return $item !== null;
                
            });
            $input2['items_ids'] = json_encode(array_values($selectedItems));

            if ($request->hasFile('file')) {
            $file = request()->file('file');
            $filePath = $file->store('/salesContracts');
            
            $input2['file'] = $filePath;
            }
        
            salesContract::create($input2);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
             
            ];
        }
        $contacts=Contact::all()->pluck('supplier_business_name','id');
        $offer_prices = Transaction::where([['type','=','sell'],['status','=','approved']])->pluck('ref_no','id');
        $items=salesContractItem::pluck('name_of_item','id');
       return redirect()->route('saleContracts')->with(compact('offer_prices','items'));
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
        return view('sales::edit');
    }

    public function show($id)
    {
       if (! auth()->user()->can('user.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $offer = Transaction::findOrFail($id)
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select(
            'transactions.id as id',
            'transactions.transaction_date',
            'transactions.ref_no',
            'transactions.final_total as final_total',
            'transactions.is_direct_sale',
            'contacts.name as name',
            'contacts.mobile as mobile',
            'transactions.status as status',

        )
        ->get()[0];
    
      
        return view('sales::price_offer.show')->with(compact('offer'));
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            salesContract::where('id', $id)
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
