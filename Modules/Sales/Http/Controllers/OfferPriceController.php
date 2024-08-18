<?php

namespace Modules\Sales\Http\Controllers;

use App\SentNotification;
use App\SentNotificationsUser;
use Illuminate\Contracts\Support\Renderable;
use Modules\Essentials\Entities\EssentialsCity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\Contact;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
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
use App\Template;
use App\TransactionSellLine;
use App\TypesOfService;
use Carbon\Carbon;
use Modules\Sales\Entities\salesOfferPricesCost;
use PhpOffice\PhpWord\PhpWord;
use DOMDocument;
use Modules\Essentials\Entities\EssentialsDepartment;

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
            'method' => '',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_change_offer_price_status = auth()->user()->can('sales.change_offer_price_status');
        $can_approve_offer_price = auth()->user()->can('sales.approve_offer_price');
        $can_print_offer_price = auth()->user()->can('sales.print_offer_price');


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
                'transactions.is_approved as is_approved',
                'transactions.approved_by as approved_by',




            );
        $all_users = User::select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        if (request()->ajax()) {




            $sells->groupBy('transactions.id');
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $sells->where('status', request()->input('status'));
            }

            return Datatables::of($sells)
                ->editColumn('status', function ($row) use ($is_admin, $can_change_offer_price_status) {
                    if ($is_admin || $can_change_offer_price_status) {
                        $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                            . $this->statuses[$row->status]['name'] . '</span>';
                        $status = '<a href="#" class="change_status" data-offer-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    } else {
                        $status = $row->status;
                    }
                    return $status;
                })
                ->editColumn('is_approved', function ($row) use ($is_admin, $users, $can_approve_offer_price) {
                    if ($row->is_approved == 1) {

                        $approvedBy = $users[$row->approved_by];
                        return $approvedBy ? $approvedBy : 'Unknown';
                    } else {

                        if ($is_admin || $can_approve_offer_price) {

                            return '<form action="' . route('offer.approve', $row->id) . '" method="POST" style="display:inline;">
                                        ' . csrf_field() . method_field('PATCH') . '
                                               <button type="submit" class="btn btn-primary" onclick="return confirm(\'Are you sure you want to approve this offer?\')">' . __('sales::lang.approve_offer') . '</button>
                                    </form>';
                        } else {
                            return 'Not Authorized';
                        }
                    }
                })

                ->editColumn('location_id', function ($row) use ($business_locations) {
                    $item = $business_locations[$row->location_id] ?? '';

                    return $item;
                })

                // ->addColumn(
                //     'action',
                //     function ($row)  use ($is_admin, $can_print_offer_price) {
                //         $html = '';

                //         if ($is_admin || $can_print_offer_price) {
                //             $html = '<a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'print'], [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal">
                //             <i class="fas fa-download" aria-hidden="true"></i>' . __('sales::lang.view & print') . '
                //             </a>';
                //         }
                //         return $html;
                //     }
                // )

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

                ->rawColumns(['action', 'invoice_no', 'status', 'transaction_date', 'supplier_business_name', 'is_approved'])
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
        $can_print_offer_price = auth()->user()->can('sales.print_offer_price');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_approve_offer_price = auth()->user()->can('sales.approve_offer_price');

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
                'transactions.is_approved as is_approved',


            );

        if (request()->ajax()) {




            $sells->groupBy('transactions.id');
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $sells->where('status', request()->input('status'));
            }


            return Datatables::of($sells)


                // ->addColumn(
                //     'action',
                //     function ($row) use ($is_admin, $can_print_offer_price) {
                //         $html = '';
                //         if ($is_admin || $can_print_offer_price) {
                //             $html = '<div class="btn-group">
                //                 <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                //                     data-toggle="dropdown" aria-expanded="false">' .
                //                 __('messages.actions') .
                //                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                //                     </span>
                //                 </button>
                //                 <ul class="dropdown-menu dropdown-menu-right" role="menu">
                //                     <li>
                //                     <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal">
                //                     <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                //                     </a>
                //                     </li>';

                //             $html .= '</ul></div>';
                //         }

                //         return $html;
                //     }
                // )
                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_print_offer_price, $can_approve_offer_price) {
                        $html = '';
                        if ($row->is_approved == 1) {
                            if ($is_admin || $can_print_offer_price) {
                                $html = '<a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'print'], [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal">
                            <i class="fas fa-download" aria-hidden="true"></i>' . __('sales::lang.view & print') . '
                            </a>';
                            }
                        } else {
                            if ($is_admin || $can_approve_offer_price) {

                                return '<form action="' . route('offer.approve', $row->id) . '" method="POST" style="display:inline;">
                                            ' . csrf_field() . method_field('PATCH') . '
                                                   <button type="submit" class="btn btn-primary" onclick="return confirm(\'Are you sure you want to approve this offer?\')">' . __('sales::lang.approve_offer') . '</button>
                                        </form>';
                            }
                        }
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
        $can_print_offer_price = auth()->user()->can('sales.print_offer_price');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
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
                    function ($row)  use ($is_admin, $can_print_offer_price) {
                        $html = '';
                        if ($is_admin || $can_print_offer_price) {
                            $html = '<a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'print'], [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal">
                        <i class="fas fa-download" aria-hidden="true"></i>' . __('sales::lang.view & print') . '
                        </a>';
                        }
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
            $input = $request->only(['status', 'offer_id', 'note']);

            $offer = Transaction::find($input['offer_id']);
            $contact = $offer->contact_id;


            $offer->status = $input['status'];
            $offer->additional_notes = $input['note'];


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
            $accounts = Account::forDropdown($business_id, true, false, false);
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

        $leads = Contact::where('type', 'qualified')
            ->whereDoesntHave('transactions', function ($query) {
                $query->where('status', 'under_study');
            })

            ->pluck('supplier_business_name', 'id');

        $cities = EssentialsCity::forDropdown();
        return view('sales::price_offer.create')
            ->with(compact(

                'business_details',
                'taxes',
                'leads',
                'walk_in_customer',
                'cities',
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
        $cities = EssentialsCity::forDropdown();

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
            $accounts = Account::forDropdown($business_id, true, false, false);
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
                'cities',
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

        //   return $request;
        try {
            $business_id = $request->session()->get('user.business_id');

            $offer = ['contract_form',  'down_payment', 'issue_date'];


            //     $transactionDate = Carbon::createFromFormat('m/d/Y h:i A', $request->input('transaction_date'));

            //  $transactionDateInput = $request->input('transaction_date');
            //  $formattedDate = null;


            //  try {
            // Attempt to parse the date with the expected format
            //       $transactionDate = Carbon::createFromFormat('m/d/Y H:i', $transactionDateInput);
            //   } catch (\Exception $e) {
            // Handle the case where the format does not match
            //       return response()->json(['error' => 'Invalid date format'], 400);
            // }
            //  $formattedDate = $transactionDate->format('m/d/Y h:i A');
            $offer_details = $request->only($offer);



            //   $offer_details['location_id'] = $request->input('location_id');
            $offer_details['contact_id'] = $request->input('contact_id');
            $offer_details['business_id'] = $business_id;
            $offer_details['transaction_date'] = $request->input('issue_date');
            $offer_details['created_by'] = $request->session()->get('user.id');
            $offer_details['type'] = 'sell';
            $offer_details['sub_type'] = 'service';
            $offer_details['is_quotation'] = 1;
            $offer_details['total_worker_number'] = $request->input('quantityArrDisplay');
            $offer_details['final_total'] = $request->input('total_contract_cost');
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

            $productIds = json_decode($request->input('productIds'));
            $quantityArr = json_decode($request->input('quantityArr'));
            $productData = json_decode($request->input('productData'), true);

            if (count($productIds) === count($productData)) {
                foreach ($productIds as $key => $productId) {
                    $data = $productData[$key];
                    $quantity = $quantityArr[$key];



                    $transactionSellLine = new TransactionSellLine;
                    $transactionSellLine->additional_allwances = json_encode($data);
                    $transactionSellLine->service_id = $productId;
                    $transactionSellLine->quantity = $quantity;
                    $transactionSellLine->operation_remaining_quantity = $quantity;
                    $transactionSellLine->transaction_id = $client->id;

                    $transactionSellLine->save();
                }
            }
            $contacts = Contact::all()->pluck('supplier_business_name', 'id');
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%مبيعات%')
                ->pluck('id')->toArray();
            error_log(json_encode($departmentIds));
            $rolesIds = DB::table('roles')
                ->where('name', 'LIKE', '%مبيعات%')->pluck('id')->toArray();
            error_log(json_encode($rolesIds));

            $users = User::whereHas('roles', function ($query) use ($rolesIds) {
                $query->whereIn('id', $rolesIds);
            })->whereIn('essentials_department_id', $departmentIds)->where('user_type', 'manager');
            $user_ids = $users->pluck('id')->toArray();
            error_log(json_encode($user_ids));

            $to =  $users->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
                ->pluck('full_name')->toArray();
            if (!empty($user_ids)) {

                $sentNotification = SentNotification::create([
                    'via' => 'dashboard',
                    'type' => 'GeneralManagementNotification',
                    'title' =>  $contacts[$client->contact_id],
                    'msg' => __('sales::lang.new offer price') . ' ' . $client->ref_no,
                    'sender_id' => auth()->user()->id,
                    'to' => json_encode($to),
                ]);
            }
            foreach ($user_ids as $user_id) {
                SentNotificationsUser::create([
                    'sent_notifications_id' => $sentNotification->id,
                    'user_id' => $user_id,
                ]);
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
                ->first();

            $phpWord = new PhpWord();

            if ($query->contract_form == 'operating_fees') {
                $template = Template::with('sections')->where('id', 1)->first();
                $sections = $template->sections->sortBy('order');

                $replacements = [
                    '${DATE}' => Carbon::parse($query->transaction_date)->format('Y-m-d'),
                    '${DATE_EN}' => Carbon::parse($query->transaction_date)->format('d-m-Y'),
                    '${CONTACTS}' => $query->contact->supplier_business_name ?? '',
                    '${CONTACTS_EN}' => $query->contact->english_name ?? '',
                    '${PRE_PAY}' => $query->down_payment ?? '',
                    '${PRE_PAY_EN}' => $query->down_payment ?? '',
                    '${BANK_GURANTEE}' => '' ?? '',
                    '${BANK_GURANTEE_EN}' => '' ?? '',
                    '${CREATED_BY}' => 'إدارة المبيعات',
                    '${CREATED_BY_EN}' => 'Sells Department',
                ];

                foreach ($replacements as $placeholder => $value) {
                    $template->primary_header = str_replace($placeholder, $value,  $template->primary_header);
                    $template->primary_footer = str_replace($placeholder, $value,  $template->primary_footer);
                    foreach ($sections as $section) {
                        $section->header_left = str_replace($placeholder, $value, $section->header_left);
                        $section->header_right = str_replace($placeholder, $value, $section->header_right);
                        if ($section->content) {
                            $section->content = str_replace($placeholder, $value, $section->content);
                        } else {
                            $section->content_left = str_replace($placeholder, $value, $section->content_left);
                            $section->content_right =  str_replace($placeholder, $value, $section->content_right);
                        }
                    }
                }



                foreach ($sections as  $section) {
                    if ($section->content) {
                        $htmlString = $section->content;

                        $firstStartPos = strpos($htmlString, '<tr');
                        $firstEndPos = strpos($htmlString, '</tr>', $firstStartPos) + 5; // Include length of '</tr>'
                        $startPos = strpos($htmlString, '<tr', $firstEndPos);
                        $endPos = strpos($htmlString, '</tr>', $startPos) + 5; // Include length of '</tr>'
                        $firstRowHtml = substr($htmlString, $startPos, $endPos - $startPos);
                        $columnCount =  substr_count($firstRowHtml, '<td');


                        if ($columnCount > 8) {
                            $original_clone =  $firstRowHtml;
                            $i = 1;
                            $final_rows = '';
                            foreach ($query->sell_lines as $sell_line) {
                                $clone =   $original_clone;
                                $food = 0;
                                $housing = 0;
                                $transportaions = 0;
                                $others = 0;
                                $uniform = 0;
                                $recruit = 0;

                                $food_allowance_exist = false;
                                $housing_allowance_exist = false;
                                $transportation_allowance_exist = false;
                                $other_allowances_exist = false;
                                $uniform_allowance_exist = false;
                                $recruit_allowance_exist = false;
                                foreach (json_decode($sell_line['service']['additional_allwances']) as $allwance) {

                                    if (is_object($allwance) && property_exists($allwance, 'type') && property_exists($allwance, 'amount')) {

                                        if ($allwance->type == 'food_allowance') {

                                            if ($allwance->payment_type == 'cash') {
                                                $food = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $food = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $food = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $food_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'housing_allowance') {

                                            if ($allwance->payment_type == 'cash') {
                                                $housing = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $housing = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $housing = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $housing_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'transportation_allowance') {

                                            if ($allwance->payment_type == 'cash') {
                                                $transportaions = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $transportaions = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $transportaions = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $transportation_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'other_allowances') {

                                            if ($allwance->payment_type == 'cash') {
                                                $others = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $others = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $others = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $other_allowances_exist = true;
                                        }
                                        if ($allwance->type == 'uniform_allowance') {

                                            if ($allwance->payment_type == 'cash') {
                                                $uniform = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $uniform = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $uniform = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $uniform_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'recruit_allowance') {

                                            if ($allwance->payment_type == 'cash') {
                                                $recruit = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $recruit = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $recruit = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $recruit_allowance_exist = true;
                                        }
                                    }
                                }
                                if ($food_allowance_exist == false) {
                                    $food = __('sales::lang.undefiend');
                                }
                                if ($housing_allowance_exist == false) {
                                    $housing = __('sales::lang.undefiend');
                                }
                                if ($transportation_allowance_exist == false) {
                                    $transportaions = __('sales::lang.undefiend');
                                }
                                if ($other_allowances_exist == false) {
                                    $others = __('sales::lang.undefiend');
                                }
                                if ($uniform_allowance_exist == false) {
                                    $uniform = __('sales::lang.undefiend');
                                }
                                if ($recruit_allowance_exist == false) {
                                    $recruit = __('sales::lang.undefiend');
                                }
                                $replacements2 = [
                                    '${R}' => $i,
                                    '${A}' => $sell_line['service']['profession']['name'] ?? '',
                                    '${B}' =>  number_format($sell_line['service']['service_price'] ?? 0, 0, '.', '') . ' SR',
                                    '${C}' => $food,
                                    '${D}' => $transportaions,
                                    '${E}' => $housing,
                                    '${F}' => $others,
                                    '${G}' => __('sales::lang.' . $sell_line['service']['gender']) ?? '',
                                    '${H}' => $sell_line->quantity ?? 0,
                                    '${I}' => number_format($query->total_worker_monthly / $query->total_worker_number ?? 0, 2, '.', '') . ' SR',
                                    '${J}' => $sell_line['service']['nationality']['nationality'] ?? '',
                                    '${K}' => $query->contract_duration ?? __('sales::lang.undefiend'),
                                    '${L}' => number_format($query->total_worker_monthly, 2, '.', '') . ' SR',
                                    '${M}' => number_format(($query->total_worker_monthly ?? 0) * 15 / 100 ?? '', 2, '.', '') . ' SR',
                                    '${N}' =>  number_format(($query->total_worker_monthly ?? 0) +  (($query->total_worker_monthly) * 15 / 100 ?? 0), 2, '.', '') . ' SR',

                                    //$sell_line['service']['monthly_cost_for_one'] * $sell_line->quantity
                                ];



                                foreach ($replacements2 as $placeholder => $value) {
                                    $clone = str_replace($placeholder, $value,   $clone);
                                    // $htmlString = substr_replace($htmlString,   $clone, $endPos, 0);
                                    // $endPos += strlen($clone);
                                }
                                $final_rows .= $clone;
                                $i++;
                            }
                            $htmlString = substr_replace($htmlString,      $final_rows, $endPos, 0);
                            $htmlString = substr_replace($htmlString, '', $startPos, $endPos - $startPos);
                            $section->content = $htmlString;
                        }
                    }
                }




                return view('sales::price_offer.print')->with(compact('template', 'sections'));
            } else if ($query->contract_form == 'monthly_cost') {
                $template = Template::with('sections')->where('id', 2)->first();
                $sections = $template->sections->sortBy('order');
                $food = 0;
                $housing = 0;
                $transportaions = 0;
                $others = 0;
                $uniform = 0;
                $recruit = 0;

                foreach ($sections as $section) {
                    if ($section->content) {
                        $htmlString = $section->content;
                        $firstStartPos = strpos($htmlString, '<tr');
                        $firstEndPos = strpos($htmlString, '</tr>', $firstStartPos) + 5;
                        $startPos = strpos($htmlString, '<tr', $firstEndPos);
                        $endPos = strpos($htmlString, '</tr>', $startPos) + 5;
                        $firstRowHtml = substr($htmlString, $startPos, $endPos - $startPos);
                        $columnCount = substr_count($firstRowHtml, '<td');
                        if ($columnCount > 8) {
                            $original_clone = $firstRowHtml;
                            $i = 1;
                            $final_rows = '';
                            foreach ($query->sell_lines as $sell_line) {
                                $clone = $original_clone;
                                $food_allowance_exist = false;
                                $housing_allowance_exist = false;
                                $transportation_allowance_exist = false;
                                $other_allowances_exist = false;
                                $uniform_allowance_exist = false;
                                $recruit_allowance_exist = false;
                                foreach (json_decode($sell_line['service']['additional_allwances']) as $allwance) {
                                    if (is_object($allwance) && property_exists($allwance, 'type') && property_exists($allwance, 'amount')) {
                                        if ($allwance->type == 'food_allowance') {
                                            if ($allwance->payment_type == 'cash') {
                                                $food = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $food = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $food = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $food_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'housing_allowance') {
                                            if ($allwance->payment_type == 'cash') {
                                                $housing = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $housing = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $housing = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $housing_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'transportation_allowance') {
                                            if ($allwance->payment_type == 'cash') {
                                                $transportaions = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $transportaions = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $transportaions = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $transportation_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'other_allowances') {
                                            if ($allwance->payment_type == 'cash') {
                                                $others = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $others = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $others = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $other_allowances_exist = true;
                                        }
                                        if ($allwance->type == 'uniform_allowance') {
                                            if ($allwance->payment_type == 'cash') {
                                                $uniform = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $uniform = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $uniform = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $uniform_allowance_exist = true;
                                        }
                                        if ($allwance->type == 'recruit_allowance') {
                                            if ($allwance->payment_type == 'cash') {
                                                $recruit = $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_emdadat') {
                                                $recruit = __('sales::lang.insured_by_emdadat') . ':' . $allwance->amount . ' SR';
                                            } else if ($allwance->payment_type == 'insured_by_the_customer') {
                                                $recruit = __('sales::lang.insured_by_the_customer') . ':' . $allwance->amount . ' SR';
                                            }
                                            $recruit_allowance_exist = true;
                                        }
                                    }
                                }
                                if ($food_allowance_exist == false) {
                                    $food = __('sales::lang.undefiend');
                                }
                                if ($housing_allowance_exist == false) {
                                    $housing = __('sales::lang.undefiend');
                                }
                                if ($transportation_allowance_exist == false) {
                                    $transportaions = __('sales::lang.undefiend');
                                }
                                if ($other_allowances_exist == false) {
                                    $others = __('sales::lang.undefiend');
                                }
                                if ($uniform_allowance_exist == false) {
                                    $uniform = __('sales::lang.undefiend');
                                }
                                if ($recruit_allowance_exist == false) {
                                    $recruit = __('sales::lang.undefiend');
                                }

                                $replacements2 = [
                                    '${R}' => $i,
                                    '${A}' => $sell_line['service']['profession']['name'] ?? '',

                                    '${B}' =>  number_format($sell_line['service']['service_price'] ?? 0, 0, '.', '') . ' SR',

                                    '${C}' => $food,
                                    '${D}' => $transportaions,
                                    '${E}' => $housing,
                                    '${F}' => $others,
                                    '${G}' => __('sales::lang.' . $sell_line['service']['gender']) ?? '',
                                    '${H}' => $sell_line->quantity ?? __('sales::lang.undefiend'),
                                    '${I}' => $query->total_worker_monthly / $query->total_worker_number . ' SR' ?? __('sales::lang.undefiend'),
                                    '${J}' => $sell_line['service']['nationality']['nationality'] ?? '',
                                    '${K}' => $query->contract_duration ?? __('sales::lang.undefiend'),

                                    '${L}' =>  number_format($query->total_worker_monthly, 2, '.', '') . ' SR',
                                    '${M}' =>  number_format(($query->total_worker_monthly ?? 0) * 15 / 100 ?? '', 2, '.', '') . ' SR',
                                    '${N}' => number_format(($query->final_total ?? 0), 2, '.', '') . ' SR',

                                ];
                                foreach ($replacements2 as $placeholder => $value) {
                                    $clone = str_replace($placeholder, $value, $clone);
                                }
                                $final_rows .= $clone;
                                $i++;
                            }
                            $htmlString = substr_replace($htmlString, $final_rows, $endPos, 0);
                            $htmlString = substr_replace($htmlString, '', $startPos, $endPos - $startPos);
                            $section->content = $htmlString;
                        }
                    }
                }

                $replacements = [
                    '${DATE}' => Carbon::parse($query->transaction_date)->format('Y-m-d'),
                    '${DATE_EN}' => Carbon::parse($query->transaction_date)->format('d-m-Y'),
                    '${CONTACTS}' => $query->contact->supplier_business_name ?? '',
                    '${CONTACTS_EN}' => $query->contact->english_name ?? '',
                    '${FOOD_ALLOW}' => $food == 0 ? "no" : "yes",
                    '${ACCO_TRANS}' => ($transportaions == 0 && $housing == 0) ? "no" : "yes",
                    '${UNIFORM}' => $uniform == 0 ? "no" : "yes",
                    '${RECRUIT}' => $recruit == 0 ? "no" : "yes",
                    '${PRE_PAY}' => $query->down_payment ?? '',
                    '${PRE_PAY_EN}' => $query->down_payment ?? '',
                    '${BANK_GURANTEE}' => '' ?? '',
                    '${BANK_GURANTEE_EN}' => '' ?? '',
                    '${CREATED_BY}' => 'إدارة المبيعات',
                    '${CREATED_BY_EN}' => 'Sells Department',
                ];

                foreach ($replacements as $placeholder => $value) {
                    $template->primary_header = str_replace($placeholder, $value, $template->primary_header);
                    $template->primary_footer = str_replace($placeholder, $value, $template->primary_footer);
                    foreach ($sections as $section) {
                        $section->header_left = str_replace($placeholder, $value, $section->header_left);
                        $section->header_right = str_replace($placeholder, $value, $section->header_right);
                        if ($section->content) {
                            $section->content = str_replace($placeholder, $value, $section->content);
                        } else {
                            $section->content_left = str_replace($placeholder, $value, $section->content_left);
                            $section->content_right = str_replace($placeholder, $value, $section->content_right);
                        }
                    }
                }

                return view('sales::price_offer.print')->with(compact('template', 'sections'));
            }
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
        error_log($id);
        return $id;
        $business_id = request()->session()->get('user.business_id');

        $query = Transaction::where('id', $id)
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
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
    public function approve($id)
    {

        $offer = Transaction::findOrFail($id);


        $offer->is_approved = 1;
        $offer->approved_by = auth()->user()->id;
        $offer->save();

        return redirect()->back()->with('success', 'Offer approved successfully.');
    }
}
