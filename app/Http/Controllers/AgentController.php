<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Category;
use App\Charts\CommonChart;
use App\Contact;
use App\Currency;
use App\Media;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\VariationLocationDetails;
use DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Str;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\salesContractItem;
use Modules\Sales\Entities\SalesProject;
use Spatie\Activitylog\Models\Activity;

class AgentController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $restUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }
    private function __chartOptions2()
    {
        return [
            'plotOptions' => [
                'pie' => [
                    'allowPointSelect' => true,
                    'cursor' => 'pointer',
                    'dataLabels' => [
                        'enabled' => false
                    ],
                    'showInLegend' => true,
                ],
            ],
        ];
    }
    private function getDocumentnumber($user, $documentType)
    {
        foreach ($user->OfficialDocument as $off) {
            if ($off->type == $documentType) {
                return $off->number;
            }
        }

        return ' ';
    }


    public function agentHome()
    {
        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (!empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
        $workers = User::where('user_type', 'worker')->whereIn('assigned_to', $projectsIds);
        $workers_count = $workers->count();
        $active_workers_count = $workers->where('status', 'active')->count();
        $inactive_workers_count = $workers->whereNot('status', 'active')->count();


        $chart = new CommonChart;
        $colors = [
            '#E75E82', '#37A2EC', '#FACD56', '#5CA85C', '#605CA8',
            '#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce',
            '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'
        ];
        $labels = [
            __('followup::lang.customer_home_active_workers_count'),
            __('followup::lang.customer_home_in_active_workers_count'),

        ];
        $values = [
            $active_workers_count,
            $inactive_workers_count,

        ];
        $chart->labels($labels)
            ->options($this->__chartOptions2())
            ->dataset(__('followup::lang.customer_home_workers_count'), 'pie', $values)
            ->color($colors);
        return view('custom_views.agents.agent_home', compact(
            'active_workers_count',
            'inactive_workers_count',
            'workers_count',
            'chart',
            'widgets',
            'common_settings'
        ));
    }


    public function agentProjects()
    {
        $business_id = request()->session()->get('user.business_id');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $SalesProjects = SalesProject::where('contact_id', $contact_id);
        $cities = EssentialsCity::forDropdown();
        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', FacadesDB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');
        if (request()->ajax()) {


            return Datatables::of($SalesProjects)
                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )

                ->addColumn(
                    'contact_location_name',
                    function ($row) {
                        return  $row->name;
                    }
                )
                ->addColumn(
                    'contact_location_city',
                    function ($row) use ($cities) {
                        if ($row->city) {
                            return  $cities[$row->city];
                        } else return null;
                    }
                )
                ->addColumn(
                    'contact_location_name_in_charge',
                    function ($row) use ($name_in_charge_choices) {

                        if ($row->name_in_charge) {
                            return $name_in_charge_choices[$row->name_in_charge];
                        } else return null;
                    }
                )
                ->addColumn(
                    'contact_location_phone_in_charge',
                    function ($row) {
                        return  $row->phone_in_charge;
                    }
                )
                ->addColumn(
                    'contact_location_email_in_charge',
                    function ($row) {
                        return $row->email_in_charge;
                    }
                )

                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';

                        $html .= '<a href="' . route('sale.editSaleProject', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                             &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_item_button" data-href="' . route('sale.destroySaleProject', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';


                        return $html;
                    }
                )
                ->filterColumn('contact_name', function ($query, $keyword) {

                    $query->whereHas('contact', function ($qu) use ($keyword) {
                        $qu->where('supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contact_location_name', function ($query, $keyword) {

                    $query->where('name', 'like', "%{$keyword}%");
                })


                ->rawColumns(['id', 'contact_location_email_in_charge', 'contact_location_phone_in_charge', 'contact_location_name_in_charge', 'contact_location_city', 'contact_location_name', 'contact_id', 'action'])
                ->make(true);
        }

        $cities = EssentialsCity::forDropdown();
        $contacts = Contact::pluck('supplier_business_name', 'id');
        return view('custom_views.agents.agent_projects')->with(compact('cities', 'contacts', 'name_in_charge_choices'));
    }


    public function agentContracts()
    {


        $contacts = SalesProject::all()->pluck('name', 'id');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
        if (request()->ajax()) {

            $contracts = salesContract::join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')
                ->select([
                    'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                    'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file',
                    'transactions.contract_form as contract_form', 'transactions.sales_project_id', 'transactions.id as tra'
                ])->whereIn('sales_project_id', $projectsIds);

            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $contracts->where('sales_contracts.status', request()->input('status'));
            }
            if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                $contracts->where('transactions.contract_form', request()->input('contract_form'));
            }
            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->sales_project_id] ?? '';

                    return $item;
                })


                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        $html .=  '  <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\ContractsController::class, 'showOfferPrice'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __('sales::lang.offer_price_view') . '</a>';
                        $html .= '&nbsp;';

                        if (!empty($row->file)) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->file . '\'"><i class="fa fa-eye"></i> ' . __('sales::lang.contract_view') . '</button>';
                        } else {
                            $html .= '<span class="text-warning">' . __('sales::lang.no_file_to_show') . '</span>';
                        }
                        $html .= '&nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_contract_button" data-href="' . route('contract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                        return $html;
                    }
                )



                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action'])
                ->make(true);
        }



        return view('custom_views.agents.agent_contracts');
    }

    public function agentWorker()
    {
        $business_id = request()->session()->get('user.business_id');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $contacts_fillter = SalesProject::where('contact_id', $contact_id)->pluck('name', 'id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields();
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
        $users = User::where('user_type', 'worker')->whereIn('assigned_to',  $projectsIds)
            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);

        $users->select(
            'users.id',
            'users.*',
            'users.id_proof_number',
            'users.nationality_id',
            'users.essentials_salary',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        );

        if (request()->ajax()) {
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

                $users = $users->where('users.assigned_to', request()->input('project_name'));
            }

            if (!empty(request()->input('status_fillter')) && request()->input('status_fillter') !== 'all') {

                $users = $users->where('users.status', request()->input('status_fillter'));
            }

            if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
                $start = request()->filter_start_date;
                $end = request()->filter_end_date;

                $users->whereHas('contract', function ($query) use ($start, $end) {
                    $query->whereDate('contract_end_date', '>=', $start)
                        ->whereDate('contract_end_date', '<=', $end);
                });
            }
            if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {

                $users = $users->where('users.nationality_id', request()->nationality);
            }

            return Datatables::of($users)

                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                })
                ->addColumn('residence_permit_expiration', function ($user) {
                    $residencePermitDocument = $user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();
                    if ($residencePermitDocument) {

                        return optional($residencePermitDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })

                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })
                ->addColumn('admissions_date', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_date ?? ' ';
                })

                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })

                ->addColumn('profession', function ($row) use ($appointments, $professions) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $professions[$professionId] ?? '';

                    return $professionName;
                })



                ->addColumn('specialization', function ($row) use ($appointments2, $specializations) {
                    $specializationId = $appointments2[$row->id] ?? '';
                    $specializationName = $specializations[$specializationId] ?? '';

                    return $specializationName;
                })->addColumn('bank_code', function ($user) {

                    $bank_details = json_decode($user->bank_details);
                    return $bank_details->bank_code ?? ' ';
                })
                ->addColumn('worker', function ($user) {
                    return $user->worker;
                })



                ->rawColumns([
                    'nationality', 'worker',
                    'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'
                ])
                ->make(true);
        }

        return view('custom_views.agents.agent_workers')->with(compact('contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
    }

    public function showAgentWorker($id)
    {

        $business_id = request()->session()->get('user.business_id');

        $user = User::with(['contactAccess', 'assignedTo', 'OfficialDocument', 'proposal_worker'])
            ->find($id);



        $documents = null;


        if (!empty($user->proposal_worker_id)) {

            $officialDocuments = $user->OfficialDocument;
            $workerDocuments = $user->proposal_worker?->worker_documents;
            $documents = $officialDocuments->merge($workerDocuments);
        } else {
            $documents = $user->OfficialDocument;
        }





        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();


        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('profession_id');

        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)->name;
        } else {
            $profession = "";
        }

        $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        if ($specializationId !== null) {
            $specialization = EssentialsSpecialization::find($specializationId)->name;
        } else {
            $specialization = "";
        }


        $user->profession = $profession;
        $user->specialization = $specialization;


        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);

        $users = User::forDropdown($business_id, false);

        $activities = Activity::forSubject($user)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $nationality_id = $user->nationality_id;
        $nationality = "";

        if (!empty($nationality_id)) {
            $nationality = EssentialsCountry::select('nationality')->where('id', '=', $nationality_id)->first();
        }

        return view('custom_views.agents.show_agent_worker')->with(compact(
            'user',
            'view_partials',
            'users',
            'activities',
            'bank_name',
            'admissions_to_work',
            'Qualification',
            'Contract',
            'nationalities',
            'nationality',
            'documents',
        ));
    }

































    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id);

            $total_ledger_discount = $this->transactionUtil->getTotalLedgerDiscount($business_id, $start, $end);

            $purchase_details['purchase_due'] = $purchase_details['purchase_due'] - $total_ledger_discount['total_purchase_discount'];

            $transaction_types = [
                'purchase_return', 'sell_return', 'expense',
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id
            );

            $total_purchase_inc_tax = !empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];

            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase_inc_tax;
            $output['total_purchase_return'] = $total_purchase_return_inc_tax;
            $output['total_purchase_return_paid'] = $this->transactionUtil->getTotalPurchaseReturnPaid($business_id, $start, $end, $location_id);

            $total_sell_inc_tax = !empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = !empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;
            $output['total_sell_return_paid'] = $this->transactionUtil->getTotalSellReturnPaid($business_id, $start, $end, $location_id);

            $output['total_sell'] = $total_sell_inc_tax;
            $output['total_sell_return'] = $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'] - $total_ledger_discount['total_sell_discount'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            //NET = TOTAL SALES - INVOICE DUE - EXPENSE
            $output['net'] = $output['total_sell'] - $output['invoice_due'] - $output['total_expense'];

            return $output;
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = VariationLocationDetails::join(
                'product_variations as pv',
                'variation_location_details.product_variation_id',
                '=',
                'pv.id'
            )
                ->join(
                    'variations as v',
                    'variation_location_details.variation_id',
                    '=',
                    'v.id'
                )
                ->join(
                    'products as p',
                    'variation_location_details.product_id',
                    '=',
                    'p.id'
                )
                ->leftjoin(
                    'business_locations as l',
                    'variation_location_details.location_id',
                    '=',
                    'l.id'
                )
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('p.business_id', $business_id)
                ->where('p.enable_stock', 1)
                ->where('p.is_inactive', 0)
                ->whereNull('v.deleted_at')
                ->whereNotNull('p.alert_quantity')
                ->whereRaw('variation_location_details.qty_available <= p.alert_quantity');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('variation_location_details.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('variation_location_details.location_id', request()->input('location_id'));
            }

            $products = $query->select(
                'p.name as product',
                'p.type',
                'p.sku',
                'pv.name as product_variation',
                'v.name as variation',
                'v.sub_sku',
                'l.name as location',
                'variation_location_details.qty_available as stock',
                'u.short_name as unit'
            )
                ->groupBy('variation_location_details.id')
                ->orderBy('stock', 'asc');

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product . ' (' . $row->sku . ')';
                    } else {
                        return $row->product . ' - ' . $row->product_variation . ' - ' . $row->variation . ' (' . $row->sub_sku . ')';
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;

                    return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>' . (float) $stock . '</span> ' . $row->unit;
                })
                ->removeColumn('sku')
                ->removeColumn('sub_sku')
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                ->leftJoin(
                    'transaction_payments as tp',
                    'transactions.id',
                    '=',
                    'tp.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'purchase')
                ->where('transactions.payment_status', '!=', 'paid')
                ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'c.supplier_business_name',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $due . '</span>';
                })
                ->addColumn('action', '@can("purchase.create") <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="btn btn-xs btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endcan')
                ->removeColumn('supplier_business_name')
                ->editColumn('supplier', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$supplier}}')
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return  '<a href="#" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->ref_no . '</a>';
                    }

                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                ->leftJoin(
                    'transaction_payments as tp',
                    'transactions.id',
                    '=',
                    'tp.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.payment_status', '!=', 'paid')
                ->whereNotNull('transactions.pay_term_number')
                ->whereNotNull('transactions.pay_term_type')
                ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as customer',
                'c.supplier_business_name',
                'transactions.invoice_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $due . '</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return  '<a href="#" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->invoice_no . '</a>';
                    }

                    return $row->invoice_no;
                })
                ->addColumn('action', '@if(auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="btn btn-xs btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endif')
                ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$customer}}')
                ->removeColumn('supplier_business_name')
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }



    public function loadMoreNotifications()
    {
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

        if (request()->input('page') == 1) {
            auth()->user()->unreadNotifications->markAsRead();
        }
        $notifications_data = $this->commonUtil->parseNotifications($notifications);

        return view('layouts.partials.notification_list', compact('notifications_data'));
    }

    /**
     * Function to count total number of unread notifications
     *
     * @return json
     */
    public function getTotalUnreadNotifications()
    {
        $unread_notifications = auth()->user()->unreadNotifications;
        $total_unread = $unread_notifications->count();

        $notification_html = '';
        $modal_notifications = [];
        foreach ($unread_notifications as $unread_notification) {
            if (isset($data['show_popup'])) {
                $modal_notifications[] = $unread_notification;
                $unread_notification->markAsRead();
            }
        }
        if (!empty($modal_notifications)) {
            $notification_html = view('home.notification_modal')->with(['notifications' => $modal_notifications])->render();
        }

        return [
            'total_unread' => $total_unread,
            'notification_html' => $notification_html,
        ];
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
                'padding' => 20,
            ],
        ];
    }

    public function getCalendar()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->restUtil->is_admin(auth()->user(), $business_id);
        $is_superadmin = auth()->user()->can('superadmin');
        if (request()->ajax()) {
            $data = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => ($is_admin || $is_superadmin) && !empty(request()->user_id) ? request()->user_id : auth()->user()->id,
                'location_id' => !empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id,
                'events' => request()->events ?? [],
                'color' => '#007FFF',
            ];
            $events = [];

            if (in_array('bookings', $data['events'])) {
                $events = $this->restUtil->getBookingsForCalendar($data);
            }

            $module_events = $this->moduleUtil->getModuleData('calendarEvents', $data);

            foreach ($module_events as $module_event) {
                $events = array_merge($events, $module_event);
            }

            return $events;
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }

        $event_types = [
            'bookings' => [
                'label' => __('restaurant.bookings'),
                'color' => '#007FFF',
            ],
        ];
        $module_event_types = $this->moduleUtil->getModuleData('eventTypes');
        foreach ($module_event_types as $module_event_type) {
            $event_types = array_merge($event_types, $module_event_type);
        }

        return view('home.calendar')->with(compact('all_locations', 'users', 'event_types'));
    }

    public function showNotification($id)
    {
        $notification = DatabaseNotification::find($id);

        $data = $notification->data;

        $notification->markAsRead();

        return view('home.notification_modal')->with([
            'notifications' => [$notification],
        ]);
    }

    public function attachMediasToGivenModel(Request $request)
    {
        if ($request->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $model_id = $request->input('model_id');
                $model = $request->input('model_type');
                $model_media_type = $request->input('model_media_type');

                DB::beginTransaction();

                //find model to which medias are to be attached
                $model_to_be_attached = $model::where('business_id', $business_id)
                    ->findOrFail($model_id);

                Media::uploadMedia($business_id, $model_to_be_attached, $request, 'file', false, $model_media_type);

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                DB::rollBack();

                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getUserLocation($latlng)
    {
        $latlng_array = explode(',', $latlng);

        $response = $this->moduleUtil->getLocationFromCoordinates($latlng_array[0], $latlng_array[1]);

        return ['address' => $response];
    }
}
