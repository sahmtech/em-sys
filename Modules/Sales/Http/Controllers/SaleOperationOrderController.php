<?php

namespace Modules\Sales\Http\Controllers;

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
use DB;
use App\Transaction;
use App\Contact;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\SalesOrdersOperation;

class SaleOperationOrderController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
    
        $contracts = DB::table('sales_orders_operations')
        ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
        ->select('sales_contracts.number_of_contract as contract_number')
        ->get();

        if (request()->input('contract_id')) {
            $query->where('sales_orders_operations.sale_contract_id', request()->input('contract_id'));
        }
    
        if (request()->input('status_filter')) {
            $query->where('sales_orders_operations.operation_order_type', request()->input('status_filter'));
        }
      
  


        //     foreach ($products as $pro)
        //    {dd($pro);}

   
   
 
      $operations = DB::table('sales_orders_operations')
      ->join('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
      ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
      ->select(
          'sales_orders_operations.id as id',
          'sales_orders_operations.operation_order_no as operation_order_no',
          'contacts.name as contact_name',
          'sales_contracts.number_of_contract as contract_number',
          'sales_orders_operations.operation_order_type as operation_order_type',
          'sales_orders_operations.Status as Status'
      );
    //dd( $operations);

        if (request()->ajax()) {
         
        
            return Datatables::of($operations)
            ->addColumn('show_operation', function ($row) {
              
                $html = '';
                $html = '<a href="#" data-href="'.action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'show'], [$row->id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a>';
                return $html;
            })
        
            ->addColumn('action', function ($row) {
                    $html = '<a href="" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                    $html .= '&nbsp;'; 
                   // $html .= '<button class="btn btn-xs btn-danger delete_operation_button" data-href="' . route('sale.delete.order_operation', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
            
                    
                    
                     return $html;
           
                })

                ->rawColumns(['show_operation', 'action']) 
                ->removeColumn('id')
                ->make(true);
        }
        

        return view('sales::operation_order.index')->with(compact('contracts'));
      
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    
     public function getContracts(Request $request)
{
    $customerId = $request->input('customer_id');
    $business_id = $request->session()->get('user.business_id');
   
    $offer_prices = Transaction::where('contact_id', $customerId)
        ->where('business_id', $business_id)
        ->pluck('id');
    
    $contracts = [];
    foreach ($offer_prices as $key) {
        $contractIds = salesContract::where('offer_price_id', $key)
            ->where('status', 'valid')
            ->select('number_of_contract', 'id')
            ->get()
            ->toArray();

        // Add the contracts for this offer price to the $contracts array
        $contracts = array_merge($contracts, $contractIds);
    }
    
    return response()->json($contracts);
}



    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
     

        $leads=Contact::where('type','lead')
        
        ->where('business_id',$business_id)
        ->pluck('supplier_business_name','id');

        $agencies=Contact::where('type','agency')
        ->where('business_id',$business_id)
        ->pluck('supplier_business_name','id');

    
        return view('sales::operation_order.create')->with(compact('leads','agencies'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
           
        
           
            DB::transaction(function () use ($request) {
                $operation_order = [
                    'contact_id','sale_contract_id','agency_id','operation_order_no','operation_order_type', 
                    'Interview', 'Location','Delivery', 'Note', 'Status', 'Industry'
                ];
                $operation_details = $request->only($operation_order);
               // dd( $operation_details['operation_order_no']);
                $latestRecord = SalesOrdersOperation::orderBy('operation_order_no', 'desc')->first();
                
                if ( $latestRecord )
                {
                      $latestRefNo = $latestRecord->operation_order_no;
                        $numericPart = (int)substr($latestRefNo, 5); 
                        $numericPart++;
                        $operation_details['operation_order_no'] = 'POP' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
                    

                }
                else
                {$operation_details['operation_order_no'] = 'POP1111';}
              
             //dd($operation_details);
                $operation = SalesOrdersOperation::create( $operation_details );
             //  dd( $operation );
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
        
        return redirect()->route('sale.orderOperations')->with($output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        try {

            $operations = DB::table('sales_orders_operations')
            ->join('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
            ->where('sales_orders_operations.id','=',$id)
            ->select(
                'sales_orders_operations.id as id',
                'sales_orders_operations.operation_order_no as operation_order_no',
                'contacts.name as contact_name',
                'sales_contracts.number_of_contract as contract_number',
                'sales_orders_operations.operation_order_type as operation_order_type',
                'sales_orders_operations.Status as Status',
                'sales_orders_operations.Location as Location',
                'sales_orders_operations.Delivery as Delivery',
                'sales_orders_operations.Interview as Interview',
                'sales_orders_operations.Industry as Industry',
                'contacts.email as email',
            )->first();

            $transactionID=DB::table('sales_orders_operations')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
           ->join('transactions', 'sales_contracts.offer_price_id', '=', 'transactions.id')
            ->where('sales_orders_operations.id','=',$id)
            ->first();
         
            $products = DB::table('transaction_sell_lines')
            ->join('products', 'transaction_sell_lines.product_id', '=', 'products.id')
            ->leftJoin('essentials_countries', 'products.nationality_id', '=', 'essentials_countries.id')
            ->leftJoin('essentials_professions', 'products.profession_id', '=', 'essentials_professions.id')
            ->leftJoin('essentials_specializations', 'products.specialization_id', '=', 'essentials_specializations.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->where('transaction_id', '=', $transactionID->id)
            ->select('products.*', 'essentials_countries.nationality as nationality_name',
             'essentials_professions.name as profession_name', 'essentials_specializations.name as specialization_name',
             'units.actual_name as unit')
            ->get();
       
    
        
        //  dd($prod);


        }
        catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }
        
    
     
        return view('sales::operation_order.show')
        ->with(compact('operations','products'));
           
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

        $business_id = request()->session()->get('user.business_id');
      //  dd(  $business_id);
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $record = SalesOrdersOperation::find($id);
        if (!$record) {
          
            return redirect()->route('sale.orderOperations')->with('error', 'Record not found.');
        }
        try {

            $record->delete();
            $output = [
                'success' => 1,
                'msg' => __('sales::lang.operationOrder_deleted_success'),
            ];
    
           // return redirect()->route('sale.orderOperations')->with($output);
        }
        catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            
                $output = [
                    'success' => 0,
                    'msg' => $e->getMessage(),
                ];
            }
            return $output;
    }
}
