<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\Contact;
use App\Transaction;
use App\User;
use DB;
use App\CustomerGroup;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\SellingPriceGroup;
use App\TaxRate;
use Yajra\DataTables\Facades\DataTables;
use App\Account;
use App\Business;
use App\InvoiceScheme;
use App\TransactionSellLine;
use App\TypesOfService;


class OfferPriceController extends Controller
{   
    protected $contactUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $productUtil;
    protected $moduleUtil;
    protected $util;

    protected $statuses;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->statuses = [
            'approved' => [
                'name' => __('essentials::lang.approved'),
                'class' => 'bg-green',
            ],
            'cancelled' => [
                'name' => __('essentials::lang.cancelled'),
                'class' => 'bg-red',
            ],
            'transfared' => [
                'name' => 'Transfared',
                'class' => 'bg-blue',
            ],
            'under_study' => [
                'name' =>'Under Study',
                'class' => 'bg-yellow',
            ],
        ];

        $this->dummyPaymentLine = ['method' => '', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => '', ];

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
    ];
       
    }
    
    public function index()
    {
        // if (! auth()->user()->can('quotation.view_all') && ! auth()->user()->can('quotation.view_own')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');
        $can_crud_offer_price= auth()->user()->can('sales.crud_offer_prices');
        if (! $can_crud_offer_price) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
        
            
            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->select(
                    'transactions.id as id',
                    'transactions.transaction_date',
                    'transactions.ref_no',
                    'transactions.final_total',
                    'transactions.is_direct_sale',
                    'contacts.name',
                    'contacts.mobile',
                    'transactions.status as status',
                    

                );

            $sells->groupBy('transactions.id');
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $sells->where('status', request()->input('status'));
            }

          
            return Datatables::of($sells)
                ->editColumn('status', function ($row) {
                 //   error_log($this->statuses); // Debug the statuses array
                    error_log($row->status);
                        $status = '<span class="label '.$this->statuses[$row->status]['class'].'">'
                        .$this->statuses[$row->status]['name'].'</span>';
                        $status = '<a href="#" class="change_status" data-offer-id="'.$row->id.'" data-orig-value="'.$row->status.'" data-status-name="'.$this->statuses[$row->status]['name'].'"> '.$status.'</a>';
                        
                        return $status;
                    })
                ->addColumn(
                    'action', function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">'.
                                    __('messages.actions').
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    ';

                        if (auth()->user()->can('draft.update') || auth()->user()->can('quotation.update')) {
                            if ($row->is_direct_sale == 1) {
                                $html .= '<li>
                                            <a target="_blank" href="'.action([\App\Http\Controllers\SellController::class, 'edit'], [$row->id]).'">
                                                <i class="fas fa-edit"></i>'.__('messages.edit').'
                                            </a>
                                        </li>';
                            } else {
                                $html .= '<li>
                                            <a target="_blank" href="'.action([\App\Http\Controllers\SellPosController::class, 'edit'], [$row->id]).'">
                                                <i class="fas fa-edit"></i>'.__('messages.edit').'
                                            </a>
                                        </li>';
                            }
                        }

                        $html .= '<li>
                                    <a href="#" class="print-invoice" data-href="'.route('sell.printInvoice', [$row->id]).'"><i class="fas fa-print" aria-hidden="true"></i>'.__('messages.print').'</a>
                                </li>';

                      
                      

                        $html .= '</ul></div>';

                        return $html;
                    })
                ->removeColumn('id')
               
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br>@endif {{$name}}')
                ->filterColumn('conatct_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contacts.name', 'like', "%{$keyword}%")
                        ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view')) {
                            return  action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    }, ])
                ->rawColumns(['action', 'invoice_no', 'status','transaction_date', 'conatct_name'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $statuses = $this->statuses;
        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sales::price_offer.index')
                ->with(compact('business_locations','statuses' ,'customers', 'sales_representative'));
    }
    public function changeStatus(Request $request)
    {
        error_log($request->offer_id);
        error_log($request->status);

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) || ! auth()->user()->can('essentials.approve_leave')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['status', 'offer_id']);

            $offer = Transaction::find($input['offer_id']);

            $offer->status = $input['status'];
         
            $offer->save();

            $offer->status = $this->statuses[$offer->status]['name'];

        
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
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
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $sale_type = request()->get('sale_type', '');

      
        
        $can_create_offer_price= auth()->user()->can('sales.create_offer_price');
            if (! $can_create_offer_price) {
                abort(403, 'Unauthorized action.');
            }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (! $this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellController::class, 'index']));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_price_group_id = ! empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        if (! empty($default_location) && !empty($default_location->sale_invoice_scheme_id)) {
            $default_invoice_schemes = InvoiceScheme::where('business_id', $business_id)
                                        ->findorfail($default_location->sale_invoice_scheme_id);
        }
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $status = request()->get('status', 'quotation');

        $statuses = Transaction::sell_statuses();

        if ($sale_type == 'sales_order') {
            $status = 'ordered';
        }

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                                ->value('crm_settings');
            $crm_settings = ! empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (! empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }
   
        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        $leads=Contact::where('type','lead')->where('business_id',$business_id)->pluck('supplier_business_name','id');
        return view('sales::price_offer.create')
            ->with(compact(
               
                'business_details',
                'taxes','leads',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'default_datetime',
                'pos_settings',
                'invoice_schemes',
                'default_invoice_schemes',
                'types_of_service',
                'accounts',
                'shipping_statuses',
               'status',
                'sale_type',
                'statuses',
                'is_order_request_enabled',
                'users',
                'default_price_group_id',
                'change_return'
            ));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {  
        return $request->productData;
    try {
        $business_id = $request->session()->get('user.business_id');
        $offer = ['contract_form', 'contact_id', 'down_payment', 'transaction_date', 'final_total', 'status'];

        $offer_details = $request->only($offer);
        $offer_details['business_id'] = $business_id;
        $offer_details['created_by'] = $request->session()->get('user.id');
        $offer_details['type'] = 'sell';
        $latestRecord = Transaction::where('type', 'sell')->orderBy('ref_no', 'desc')->first();

        
        if ($latestRecord) {
            $latestRefNo = $latestRecord->ref_no;
            $numericPart = (int)substr($latestRefNo, 5); 
            $numericPart++;
            $offer_details['ref_no'] = 'QN' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);
        } else {
            // No previous records, start from 3000
            $offer_details['ref_no'] = 'QN0003000';
        }



       $client = Transaction::create($offer_details);
   
        $productIds = json_decode($request->productIds);
        $productData = json_decode($request->productData);
      
        if (count($productIds) === count($productData)) {
            foreach ($productIds as $key => $productId) {
              
                $data = json_decode($productData[$key]);
                $decodedData = [];
                foreach ($data as $item) {
                    error_log($item);
                    $decodedData[] = [
                        'salaryType' => $item->salaryType,
                        'amount' => $item->amount,
                    ];
                }
                return json_encode($decodedData);
                $transactionSellLine = new TransactionSellLine();
                $transactionSellLine->additional_allwances = json_encode($decodedData);
                $transactionSellLine->product_id= $productId;
                $transactionSellLine->transaction_id= $client->id;
        
                $transactionSellLine->save();
            }}
     
        $output = [
            'success' => 1,
            'msg' => __('sales::lang.client_added_success'),
            'client' => $client
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

        $output = [
            'success' => 0,
            'msg' => __('messages.something_went_wrong'),
        ];
    }

    return redirect()->route('price_offer');
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
    // public function updateStatus(Request $request)
    // {
    //     error_log('1111111111');
    //     $rowId = $request->input('rowId');
    //     $newStatus = $request->input('newStatus');

    //     // Update the status in the database using Eloquent (replace 'YourModel' with your actual model)
    //     Transaction::where('id', $rowId)->update(['status' => $newStatus]);

    //     return response()->json(['message' => 'Status updated successfully']);
    // }
    public function destroy($id)
    {
        //
    }
}
