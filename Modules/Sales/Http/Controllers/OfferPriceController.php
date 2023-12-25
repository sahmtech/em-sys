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
use Carbon\Carbon;
use Modules\Sales\Entities\SalesProject;
use Modules\Sales\Entities\salesOfferPricesCost;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\Writer\HTML;

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
                'name' => __('sales::lang.approved'),
                'class' => 'bg-green',
            ],
            'cancelled' => [
                'name' => __('sales::lang.cancelled'),
                'class' => 'bg-red',
            ],

            'under_study' => [
                'name' => __('sales::lang.under_study'),
                'class' => 'bg-yellow',
            ],
        ];

        $this->dummyPaymentLine = [
            'method' => '', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '',
        ];

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

        $business_id = request()->session()->get('user.business_id');
        $can_crud_offer_price = auth()->user()->can('sales.crud_offer_prices');
        if (!$can_crud_offer_price) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'under_study')
            ->select(
                'transactions.id as id',
                'transactions.location_id as location_id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'transactions.final_total',
                'transactions.is_direct_sale',
                'contacts.supplier_business_name as supplier_business_name',
                'contacts.mobile as mobile',
                'contacts.name as name',
                'transactions.status as status',


            );

        if (request()->ajax()) {




            $sells->groupBy('transactions.id');
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $sells->where('status', request()->input('status'));
            }


            return Datatables::of($sells)
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    $status = '<a href="#" class="change_status" data-offer-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';

                    return $status;
                })
                ->editColumn('location_id', function ($row) use ($business_locations) {
                    $item = $business_locations[$row->location_id] ?? '';

                    return $item;
                })
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                    <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'print'], [$row->id]) . '" class="btn-modal" data-container=".view_modal">
                                    <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                                    </a>
                                    </li>';





                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')

                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('sales_projects.name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action', 'invoice_no', 'status', 'transaction_date', 'supplier_business_name'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        $statuses = $this->statuses;
        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sales::price_offer.index')
            ->with(compact('business_locations', 'statuses', 'customers', 'sales_representative'));
    }

    public function accepted_offer_prices()
    {

        $business_id = request()->session()->get('user.business_id');
        $can_crud_offer_price = auth()->user()->can('sales.crud_offer_prices');
        if (!$can_crud_offer_price) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'approved')
            ->select(
                'transactions.id as id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'transactions.final_total',
                'transactions.is_direct_sale',
                'contacts.supplier_business_name as supplier_business_name',
                'contacts.mobile as mobile',
                'contacts.name as name',


            );

        if (request()->ajax()) {




            $sells->groupBy('transactions.id');
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $sells->where('status', request()->input('status'));
            }


            return Datatables::of($sells)


                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                    <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal">
                                    <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                                    </a>
                                    </li>';

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')

                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('sales_projects.name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action', 'invoice_no', 'status', 'transaction_date', 'supplier_business_name'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        $statuses = $this->statuses;
        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sales::price_offer.accepted_offer_prices')
            ->with(compact('business_locations', 'statuses', 'customers', 'sales_representative'));
    }

    public function unaccepted_offer_prices()
    {

        $business_id = request()->session()->get('user.business_id');
        $can_crud_offer_price = auth()->user()->can('sales.crud_offer_prices');
        if (!$can_crud_offer_price) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'cancelled')
            ->select(
                'transactions.id as id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'transactions.final_total',
                'transactions.is_direct_sale',
                'contacts.supplier_business_name as supplier_business_name',
                'contacts.mobile as mobile',
                'contacts.name as name',


            );

        if (request()->ajax()) {




            $sells->groupBy('transactions.id');
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $sells->where('status', request()->input('status'));
            }


            return Datatables::of($sells)


                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                    <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal">
                                    <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                                    </a>
                                    </li>';




                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')

                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('sales_projects.name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action', 'invoice_no', 'status', 'transaction_date', 'supplier_business_name'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        $statuses = $this->statuses;
        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sales::price_offer.unaccepted_offer_prices')
            ->with(compact('business_locations', 'statuses', 'customers', 'sales_representative'));
    }


    public function changeStatus(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['status', 'offer_id']);

            $offer = Transaction::find($input['offer_id']);
            $contact = $offer->contact_id;


            $offer->status = $input['status'];

            $offer->save();

            $offer->status = $this->statuses[$offer->status]['name'];

            if ($request->input('status') == "approved") {
                Contact::where('id', $contact)->update(['type' => 'converted']);
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
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

        $can_create_offer_price = auth()->user()->can('sales.create_offer_price');
        if (!$can_create_offer_price) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellController::class, 'index']));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        // $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        // $bl_attributes = $business_locations['attributes'];
        // $business_locations = $business_locations['locations'];

        $default_location = null;
        // foreach ($business_locations as $id => $name) {
        //     $default_location = BusinessLocation::findOrFail($id);
        //     break;
        // }

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

        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        if (!empty($default_location) && !empty($default_location->sale_invoice_scheme_id)) {
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
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        $leads = Contact::where('type', 'qualified')->pluck('supplier_business_name', 'id');

        return view('sales::price_offer.create')
            ->with(compact(

                'business_details',
                'taxes',
                'leads',
                'walk_in_customer',
                // 'business_locations',
                // 'bl_attributes',
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


    public function create_offer_price_qualified_contacts($id)
    {

        $sale_type = request()->get('sale_type', '');

        $can_create_offer_price = auth()->user()->can('sales.create_offer_price');
        if (!$can_create_offer_price) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellController::class, 'index']));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        // $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        // $bl_attributes = $business_locations['attributes'];
        // $business_locations = $business_locations['locations'];

        $default_location = null;
        // foreach ($business_locations as $id => $name) {
        //     $default_location = BusinessLocation::findOrFail($id);
        //     break;
        // }

        //    $default_location = BusinessLocation::findOrFail($id);
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

        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        if (!empty($default_location) && !empty($default_location->sale_invoice_scheme_id)) {
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
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        $leads = Contact::where('id', $id)->pluck('supplier_business_name', 'id');

        return view('sales::price_offer.create')
            ->with(compact(
                'id',
                'business_details',
                'taxes',
                'leads',
                'walk_in_customer',
                // 'business_locations',
                // 'bl_attributes',
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


        try {
            $business_id = $request->session()->get('user.business_id');
            $offer = ['contract_form', 'location_id', 'down_payment', 'transaction_date', 'final_total'];
            $transactionDate = Carbon::createFromFormat('m/d/Y h:i A', $request->input('transaction_date'));
            $offer_details = $request->only($offer);
            //   $offer_details['location_id'] = $request->input('location_id');
            $offer_details['contact_id'] = $request->input('contact_id');
            $offer_details['business_id'] = $business_id;
            $offer_details['transaction_date'] = $transactionDate;
            $offer_details['created_by'] = $request->session()->get('user.id');
            $offer_details['type'] = 'sell';
            $offer_details['sub_type'] = 'service';
            $offer_details['is_quotation'] = 1;
            $offer_details['total_worker_number'] = $request->input('quantityArrDisplay');
            $offer_details['business_fees'] = $request->input('fees_input');
            $offer_details['total_worker_monthly'] = $request->input('total_monthly_for_all_workers2');
            $offer_details['contract_duration'] = $request->input('contract_duration');
            $offer_details['total_contract_cost'] = $request->input('total_contract_cost');
            $offer_details['status'] = 'under_study';



            $latestRecord = Transaction::where('sub_type', 'service')->orderBy('ref_no', 'desc')->first();


            if ($latestRecord) {
                $latestRefNo = $latestRecord->ref_no;
                $numericPart = (int)substr($latestRefNo, 5);
                $numericPart++;
                $offer_details['ref_no'] = 'QN' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);
            } else {

                $offer_details['ref_no'] = 'QN0003000';
            }



            $client = Transaction::create($offer_details);
            if ($request->contract_form == "monthly_cost") {
                $updatedData = json_decode($request->input('updated_data'), true);
                foreach ($updatedData as $data) {
                    SalesOfferPricesCost::create([
                        'cost_id' => $data['id'],
                        'amount' => $data['amount'],
                        'duration_by_month' => $data['duration_by_month'],
                        'monthly_cost' => $data['amount'] / $data['duration_by_month'],
                        'offer_price_id' => $client->id,

                    ]);
                }
            }
            if ($request->input('status') == "approved") {
                Contact::where('id', $request->contact_id)->update(['type' => 'converted']);
            }
            $productIds = json_decode($request->input('productIds'));
            $quantityArr = json_decode($request->input('quantityArr'));
            $productData = json_decode($request->input('productData'), true);

            if (count($productIds) === count($productData)) {
                foreach ($productIds as $key => $productId) {
                    $data = $productData[$key];
                    $quantity = $quantityArr[$key];

                    error_log($quantity);

                    $transactionSellLine = new TransactionSellLine;
                    $transactionSellLine->additional_allwances = json_encode($data);
                    $transactionSellLine->service_id = $productId;
                    $transactionSellLine->quantity = $quantity;
                    $transactionSellLine->operation_remaining_quantity = $quantity;
                    $transactionSellLine->transaction_id = $client->id;

                    $transactionSellLine->save();
                }
            }
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



    public function print($id)
    {

        try {
            $business_id = request()->session()->get('user.business_id');

            $query = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->with(['sales_person', 'contact:id,supplier_business_name,mobile', 'sell_lines', 'sell_lines.service'])

                ->select(
                    'id',
                    'business_id',
                    'location_id',
                    'status',
                    'contact_id',
                    'ref_no',
                    'final_total',
                    'down_payment',
                    'contract_form',
                    'transaction_date'

                )->get()[0];

            $phpWord = new PhpWord();


            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(public_path('word_templates/fixed_cost.docx'));
            // $templatePath = public_path('word_templates/fixed_cost.docx');
            //$templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->setValue('CONTACTS',    $query->contact->supplier_business_name ?? '');
            $templateProcessor->setValue('CONTACTS_EN',  $query->contact->english_name ?? '');
            $templateProcessor->setValue('PROF', $query->sell_lines->first()['service']['profession']['name'] ?? '');
            $templateProcessor->setValue('SALARY',   $query->sell_lines->first()['service']['service_price'] ?? '');
            $templateProcessor->setValue('FOOD', '' ?? '');
            $templateProcessor->setValue('TRANS', '' ?? '');
            $templateProcessor->setValue('ACCO', '' ?? '');
            $templateProcessor->setValue('OTHERS', '' ?? '');
            $templateProcessor->setValue('GENDER', __('sales::lang.' . $query->sell_lines->first()['service']['gender']) ?? '');
            $templateProcessor->setValue('Q', $query->sell_lines->first()->quantity ?? '');
            $templateProcessor->setValue('TPEL', $query->sell_lines->first()['service']['monthly_cost_for_one'] ?? '');
            $templateProcessor->setValue('NATIONALITY', $query->sell_lines->first()['service']['nationality']['nationality'] ?? '');
            $templateProcessor->setValue('DURATION',  $query->contract?->contract_duration ?? '');
            // $templateProcessor->setValue('TUNVAT', $query->contract?->total_contract_cost ?? '');
            // $templateProcessor->setValue('VAT', ($query->contract?->total_contract_cost??0) * 15 / 100 ?? '');
            // $templateProcessor->setValue('TOTAL', $query->contract?->total_contract_cost??0 +  ($query->contract?->total_contract_cost??0) * 15 / 100 ?? '');
            $templateProcessor->setValue('FOOD_ALLOW', '' ?? '');


            $templateProcessor->setValue('ACCO_TRANS', '' ?? '');
            $templateProcessor->setValue('UNIFORM', '' ?? '');
            $templateProcessor->setValue('RECRUIT', '' ?? '');


            $templateProcessor->setValue('PRE_PAY', $query->down_payment ?? '');
            $templateProcessor->setValue('BANK_GUARANTEE', '' ?? '');


            $templateProcessor->setValue('CREATED_BY', $query->sales_person->first_name ?? '');
            $templateProcessor->setValue('CREATED_BY_EN', $query->sales_person->english_name ?? '');
            // $outputPath = public_path('generated_document.docx');
            // $templateProcessor->save($outputPath);
            // $outputPath = public_path('generated_document.docx');
            // $templateProcessor->saveAs($outputPath);
            // return response()->download($outputPath);
            $outputPath = public_path('generated_document.docx');
            // Convert the Word document to PDF using Dompdf
            $phpWord = IOFactory::load($outputPath);
            $htmlWriter = new HTML($phpWord);
            $htmlPath = storage_path('generated_document.html');
            $htmlWriter->save($htmlPath);

            // Convert HTML to PDF using Dompdf
            $pdf = PDF::loadHtmlFile($htmlPath);
            $pdfPath = public_path('generated_document.pdf');
            $pdf->save($pdfPath);

            // Return the PDF as a response or download it
            return response()->download($pdfPath, 'generated_document.pdf');
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }



    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {

        $business_id = request()->session()->get('user.business_id');

        $query = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(['contact:id,supplier_business_name,mobile', 'sell_lines', 'sell_lines.service'])

            ->select(
                'id',
                'business_id',
                'location_id',
                'status',
                'contact_id',
                'ref_no',
                'final_total',
                'down_payment',
                'contract_form',
                'transaction_date'

            )->get()[0];



        return view('sales::price_offer.show')
            ->with(compact('query'));
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
        $offer_price = Transaction::find($id);
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $leads = Contact::where('type', 'qualified')->pluck('supplier_business_name', 'id');
        return view('sales::price_offer.edit')->with(compact('offer_price', 'business_locations', 'leads'));
    }


    public function update(Request $request, $id)
    {

        try {

            $offer = ['contract_form', 'location_id', 'down_payment', 'transaction_date', 'status'];

            $offer_details = $request->only($offer);


            $offer_details['location_id'] = $request->input('location_id');
            $offer_details['sales_project_id'] = $request->input('contact_id');
            $offer_details['transaction_date'] = $request->input('transaction_date');
            $offer_details['created_by'] = $request->session()->get('user.id');


            Transaction::where('id', $id)->update($offer_details);

            $output = [
                'success' => 1,
                'msg' => __('sales::lang.client_updated_success'),

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
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */

    public function destroy($id)
    {
        //
    }
}
