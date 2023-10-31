<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\Contact;
use Modules\InternationalRelations\Entities\IrDelegation;
use DB;

class OrderRequestController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;


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
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }

    //     $transactionID=DB::table('sales_orders_operations')
    //     ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
    //    ->join('transactions', 'sales_contracts.offer_price_id', '=', 'transactions.id')
    //     ->where('sales_orders_operations.id','=',1)
    //     ->first();
     
    //     $products = DB::table('transaction_sell_lines')
    //     ->join('sales_services', 'transaction_sell_lines.service_id', '=', 'sales_services.id')
    //     ->leftJoin('essentials_countries', 'sales_services.nationality_id', '=', 'essentials_countries.id')
    //     ->leftJoin('essentials_professions', 'sales_services.profession_id', '=', 'essentials_professions.id')
    //     ->leftJoin('essentials_specializations', 'sales_services.specialization_id', '=', 'essentials_specializations.id')
     
    //     ->where('transaction_id', '=', $transactionID->id)
    //     ->select('sales_services.*', 'essentials_countries.nationality as nationality_name','transaction_sell_lines.quantity',
    //      'essentials_professions.name as profession_name', 'essentials_specializations.name as specialization_name'
    //     )
    //     ->get();
  
   //   dd($agencies);     
        $operations = DB::table('sales_orders_operations')
        ->join('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
        ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
        ->where('sales_orders_operations.operation_order_type','=','External')
        ->select(
            'sales_orders_operations.id as id',
            'sales_orders_operations.operation_order_no as operation_order_no',
            'contacts.name as contact_name',
            'sales_contracts.number_of_contract as contract_number',
            'sales_orders_operations.operation_order_type as operation_order_type',
            'sales_orders_operations.Status as Status'
        );
        $contracts = DB::table('sales_orders_operations')
        ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
        ->select('sales_contracts.number_of_contract as contract_number')
        ->get();
        if (request()->input('number_of_contract')) {
            // dd(request()->input('number_of_contract'));
             $operations->where('sales_contracts.number_of_contract', request()->input('number_of_contract'));
         }
     
         if (request()->input('Status') && request()->input('Status') !== 'all') {
             $operations->where('sales_orders_operations.operation_order_type', request()->input('Status'));
          
         }
  
       // dd($operations);
       if (request()->ajax()) {
         
        
        return Datatables::of($operations)
        ->addColumn('Delegation', function ($row) {
          
            $html = '';
            $html = '<a href="#" data-href="'.action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'Delegation'], [$row->id]).'" class="btn btn-xs btn-warning btn-modal" data-container=".view_modal"><i class="fas fa-plus" aria-hidden="true"></i> '.__('internationalrelations::lang.Delegation').'</a>';
          //  $html .= '<button class="btn btn-xs btn-warning btn-modal" data-container=".view_modal" data-href="' . route('order_request.Delegation', ['id' => $row->id]) . '"><i class="fa fa-plus"></i> ' . __('internationalrelations::lang.Delegation') . '</button>';
            return $html;
        })
    

            ->rawColumns(['Delegation']) 
            ->removeColumn('id')
            ->make(true);
    }

        return view('internationalrelations::orderRequest.index')
        ->with(compact('contracts'));
    }

    
    public function Delegation($id)
    {
        $transactionID=DB::table('sales_orders_operations')
        ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
       ->join('transactions', 'sales_contracts.offer_price_id', '=', 'transactions.id')
        ->where('sales_orders_operations.id','=',$id)
        ->first();
     
        $products = DB::table('transaction_sell_lines')
        ->join('sales_services', 'transaction_sell_lines.service_id', '=', 'sales_services.id')
        ->leftJoin('essentials_countries', 'sales_services.nationality_id', '=', 'essentials_countries.id')
        ->leftJoin('essentials_professions', 'sales_services.profession_id', '=', 'essentials_professions.id')
        ->leftJoin('essentials_specializations', 'sales_services.specialization_id', '=', 'essentials_specializations.id')
     
        ->where('transaction_id', '=', $transactionID->id)
        ->select('sales_services.*', 'essentials_countries.nationality as nationality_name','transaction_sell_lines.quantity',
         'essentials_professions.name as profession_name', 'essentials_specializations.name as specialization_name'
        ,'transaction_sell_lines.id as t_id')
        ->get();
        $agencies=Contact::where('type','=','recruitment')->get();

        return view('internationalrelations::orderRequest.Delegation')->with(compact('products','agencies'));
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

     public function saveRequest(Request $request) {
        $data = $request->input('data');
       
        foreach ($data as $item) {
            $product_id = $item['product_id'];
            $agency_id = $item['agency_id'];
            $target_quantity = $item['target_quantity'];
    
            DB::table('ir_delegations')->insert([
                'transaction_sell_line_id' => $product_id,
                'agency_id' => $agency_id,
                'targeted_quantity' => $target_quantity,
            ]);
        }
    
        // Return a response if needed
        return response()->json(['message' => 'Data saved successfully']);
    }
    

    public function store(Request $request)
    {
      
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('internationalrelations::show');
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
