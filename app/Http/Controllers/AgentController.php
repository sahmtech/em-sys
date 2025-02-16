<?php

namespace App\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\BusinessLocation;
use App\Category;
use App\Charts\CommonChart;
use App\Company;
use App\Contact;
use App\Media;
use App\Request as UserRequest;
use App\RequestAttachment;
use App\RequestProcess;
use App\SentNotification;
use App\SentNotificationsUser;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\VariationLocationDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\CEOManagment\Entities\ProcedureTask;
use Modules\CEOManagment\Entities\RequestProcedureTask;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\ToDo;
use Modules\Essentials\Entities\UserLeaveBalance;
use Modules\OperationsManagmentGovernment\Entities\AssetAssessment;
use Modules\OperationsManagmentGovernment\Entities\ProjectZone;
use Modules\OperationsManagmentGovernment\Entities\WaterWeight;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\SalesProject;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

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

    protected $requestUtil;

    protected $statuses;

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
        RestaurantUtil $restUtil,
        RequestUtil $requestUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;

        $this->requestUtil = $requestUtil;
        $this->statuses = [
            'approved' => [
                'name' => __('request.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name' => __('request.rejected'),
                'class' => 'bg-red',
            ],
            'pending' => [
                'name' => __('request.pending'),
                'class' => 'bg-yellow',
            ],
        ];
    }
    private function __chartOptions2()
    {
        return [
            'plotOptions' => [
                'pie' => [
                    'allowPointSelect' => true,
                    'cursor' => 'pointer',
                    'dataLabels' => [
                        'enabled' => false,
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

    public function project_zones()
    {
        $is_admin = auth()->user()->hasRole('Admin#1');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id = $user->crm_contact_id;


        if (request()->ajax()) {
            $zones = ProjectZone::where('contact_id', $contact_id);

            return DataTables::of($zones)
                ->editColumn('project', function ($row) {
                    return SalesProject::where('id', $row->project_id)->first()?->name ?? '-';
                })
                ->editColumn('contact', function ($row) {
                    return Contact::where('id', $row->contact_id)->first()?->supplier_business_name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button data-id="' . $row->id . '" class="btn btn-xs btn-info edit_zone">
                            <i class="fas fa-edit"></i> ' . __("messages.edit") . '
                        </button>
                        <button data-href="' . route('operationsmanagmentgovernment.zone.delete', ['id' => $row->id]) . '" 
                            class="btn btn-xs btn-danger delete_zone">
                            <i class="fa fa-trash"></i> ' . __("messages.delete") . '
                        </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $projects = SalesProject::pluck('name', 'id')->toArray();
        $contacts = Contact::pluck('supplier_business_name', 'id')->toArray();
        return view('custom_views.agents.zone.index', compact('projects', 'contacts'));
    }

    public function asset_assessment()
    {
        $is_admin = auth()->user()->hasRole('Admin#1');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id = $user->crm_contact_id;

        $zones = ProjectZone::where('contact_id', $contact_id)->pluck('name', 'id');
        $ids = ProjectZone::where('contact_id', $contact_id)->pluck('id')->toArray();
        $assets = AssetAssessment::whereIn('zone_id', $ids)->with(['zone.project.contact']);

        if (request()->ajax()) {
            return DataTables::of($assets)
                ->addColumn('project', function ($row) {
                    return $row->project?->name ?? '-';
                })
                ->addColumn('zone', function ($row) {
                    return $row->zone?->name ?? '-';
                })

                ->rawColumns(['action',      'project'])
                ->make(true);
        }

        $contacts = Contact::pluck('supplier_business_name', 'id')->toArray();
        return view('custom_views.agents.assets.index', compact('zones', 'contacts'));
    }

    public function water_reports()
    {
        $is_admin = auth()->user()->hasRole('Admin#1');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id = $user->crm_contact_id;

        $projects = SalesProject::where('contact_id', $contact_id)->pluck('id')->toArray();
        $WaterWeights = WaterWeight::whereIn('project_id', $projects);
        if (request()->ajax()) {
            return DataTables::of($WaterWeights)
                ->editColumn('company', function ($row) {
                    return $row->Company?->name ?? '-';
                })
                ->editColumn('project_id', function ($row) {
                    $tmp = SalesProject::where('id', $row->project_id)->first()?->name ?? '';
                    return $tmp ?? '-';
                })
                ->editColumn('driver', function ($row) {
                    return $row->driver;
                })
                ->editColumn('plate_number', function ($row) {
                    return $row->plate_number ?? '-';
                })
                ->editColumn('weight_type', function ($row) {
                    return __('operationsmanagmentgovernment::lang.' . $row->weight_type);
                })
                ->editColumn('sample_result', function ($row) {
                    return $row->sample_result ?? '-';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : '-';
                })
                ->editColumn('created_by', function ($row) {
                    $tmp = User::where('id', $row->created_by)->first();
                    return  $tmp?->first_name . ' ' .  $tmp?->last_ame ?? '';
                })
                ->addColumn('file', function ($row) {
                    if ($row->file_path) {
                        $fileUrl = asset('storage/' . $row->file_path);
                        return '<a href="' . $fileUrl . '" target="_blank" class="btn btn-xs btn-info">
                                    <i class="fa fa-file"></i> ' . __('home.view_attach') . '
                                </a>';
                    }
                    return '';
                })

                ->rawColumns(['action', 'file'])
                ->make(true);
        }
        return view('custom_views.agents.water.index', compact('WaterWeights',));
    }

    public function agentHome()
    {
        try {
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
            $contact_id = $user->crm_contact_id;
            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $workers = User::where('user_type', 'worker')->whereIn('assigned_to', $projectsIds);
            $workers_count = $workers->count();
            $active_workers_count = $workers
                ->where('status', 'active')
                ->count();
            $inactive_workers_count = $workers->whereNot('status', 'active')->count();

            $chart = new CommonChart;
            $colors = [
                '#ec268f',
                '#37A2EC',
                '#FACD56',
                '#5CA85C',
                '#605CA8',
                '#2f7ed8',
                '#0d233a',
                '#8bbc21',
                '#910000',
                '#1aadce',
                '#492970',
                '#f28f43',
                '#77a1e5',
                '#c42525',
                '#a6c96a',
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
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }

    public function agentProjects()
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id = $user->crm_contact_id;
            $SalesProjects = SalesProject::where('contact_id', $contact_id);
            $cities = EssentialsCity::forDropdown();
            $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
            $all_users = $query->select('id', FacadesDB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
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
                            return $row->name;
                        }
                    )
                    ->addColumn(
                        'contact_location_city',
                        function ($row) use ($cities) {
                            if ($row->city) {
                                return $cities[$row->city];
                            } else {
                                return null;
                            }
                        }
                    )
                    ->addColumn(
                        'contact_location_name_in_charge',
                        function ($row) use ($name_in_charge_choices) {

                            if ($row->name_in_charge) {
                                return $name_in_charge_choices[$row->name_in_charge];
                            } else {
                                return null;
                            }
                        }
                    )
                    ->addColumn(
                        'contact_location_phone_in_charge',
                        function ($row) {
                            return $row->phone_in_charge;
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

                            $html .= '<a href="' . route('sale.editSaleProject', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
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
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }

    public function agentBills()
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id = $user->crm_contact_id;
            $transactions = Transaction::where('contact_id', $contact_id)->get();
            $companies = Company::pluck('name', 'id');
            $bills = [];
            foreach ($transactions as $transaction) {
                if ($transaction->company_id) {
                    $tmp = User::where('id', $transaction->created_by)?->first();
                    $bills[] = [
                        'id' => $transaction->id,
                        'invoice_no' => $transaction->invoice_no,
                        'transaction_date' => $transaction->transaction_date,
                        'company' => $companies[$transaction->company_id],
                        'type' => $transaction->type,
                        'status' => $transaction->status,
                        'payment_status' => $transaction->payment_status,
                        "tax_amount" => $transaction->tax_amount,
                        "discount_amount" => $transaction->discount_amount,
                        'final_total' => $transaction->final_total,
                        'created_by' => ($tmp?->first_name ?? '') . ' ' . ($tmp?->mid_name ?? '') . ' ' . ($tmp?->last_name ?? ''),
                        'created_at' => Carbon::parse($transaction->created_at)->format(('Y-m-d')),
                    ];
                }
            }

            if (request()->ajax()) {

                return Datatables::of($bills)
                    ->make(true);
            }
            return view('custom_views.agents.agent_bills');
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }

    public function agentContracts()
    {

        try {
            $contacts = Contact::all()->pluck('supplier_business_name', 'id');
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id = $user->crm_contact_id;

            if (request()->ajax()) {

                $contracts = salesContract::join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')
                    ->select([
                        'sales_contracts.number_of_contract',
                        'sales_contracts.id',
                        'sales_contracts.offer_price_id',
                        'sales_contracts.start_date',
                        'sales_contracts.end_date',
                        'sales_contracts.status',
                        'sales_contracts.file',
                        'transactions.contract_form as contract_form',
                        'transactions.contact_id',
                        'transactions.id as tra',
                    ])->where('transactions.contact_id', $contact_id);

                if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                    $contracts->where('sales_contracts.status', request()->input('status'));
                }
                if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                    $contracts->where('transactions.contract_form', request()->input('contract_form'));
                }
                return Datatables::of($contracts)

                    ->editColumn('contact_id', function ($row) use ($contacts) {
                        $item = $contacts[$row->contact_id] ?? '';

                        return $item;
                    })

                    ->addColumn(
                        'action',
                        function ($row) {
                            $html = '';
                            $html .= '  <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\ContractsController::class, 'showOfferPrice'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __('sales::lang.offer_price_view') . '</a>';
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
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }

    public function agentWorker()
    {
        try {

            $business_id = request()->session()->get('user.business_id');
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id = $user->crm_contact_id;

            $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::where('contact_id', $contact_id)->pluck('name', 'id')->toArray();

            $nationalities = EssentialsCountry::nationalityForDropdown();
            $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
            $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
            $categories = Category::all()->pluck('name', 'id');
            $departments = EssentialsDepartment::all()->pluck('name', 'id');
            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');
            $status_filltetr = $this->moduleUtil->getUserStatus();

            $user = User::where('id', auth()->user()->id)->first();
            $contact_id = $user->crm_contact_id;
            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $users = User::where('user_type', 'worker')->whereIn('users.assigned_to', $projectsIds)
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

                    if (request()->input('project_name') == 'none') {
                        $users = $users->whereNull('users.assigned_to');
                    } else {
                        $users = $users->where('users.assigned_to', request()->input('project_name'));
                    }
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
                    ->addColumn('contact_name', function ($user) {
                        return $user->contact_name;
                    })
                    ->filterColumn('worker', function ($query, $keyword) {
                        $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                    })
                    ->filterColumn('residence_permit', function ($query, $keyword) {
                        $query->whereRaw("id_proof_number like ?", ["%{$keyword}%"]);
                    })

                    ->rawColumns([
                        'contact_name',
                        'nationality',
                        'worker',
                        'residence_permit_expiration',
                        'residence_permit',
                        'admissions_date',
                        'contract_end_date',
                    ])
                    ->make(true);
            }

            return view('custom_views.agents.agent_workers')->with(compact('contacts_fillter', 'status_filltetr', 'nationalities'));
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }

    public function showAgentWorker($id)
    {
        try {
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

            // $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
            // if ($specializationId !== null) {
            //     $specialization = EssentialsSpecialization::find($specializationId)->name;
            // } else {
            //     $specialization = "";
            // }

            $user->profession = $profession;
            //   $user->specialization = $specialization;

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
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }
    private function getTypePrefix($request_type_id)
    {
        $prefix = RequestsType::where('id', $request_type_id)->first()->prefix;
        return $prefix;
    }
    public function makeToDo($request, $business_id)
    {

        $created_by = $request->created_by;

        $request_type = RequestsType::where('id', $request->request_type_id)->first()->type;
        $input['business_id'] = $business_id;
        $input['company_id'] = $business_id;
        $input['created_by'] = $created_by;
        $input['task'] = "طلب جديد";
        $input['date'] = Carbon::now();
        $input['priority'] = 'high';
        $input['description'] = $request_type;
        $input['status'] = !empty($input['status']) ? $input['status'] : 'new';

        $process = RequestProcess::where('request_id', $request->id)->latest()->first();
        $users = [];
        $userCompanyId = User::where('id', $request->related_to)->first()->company_id;
        $acessRoleCompany = AccessRoleCompany::where('company_id', $userCompanyId)->pluck('access_role_id')->toArray();
        $rolesFromAccessRoles = AccessRole::whereIn('id', $acessRoleCompany)->pluck('role_id')->toArray();

        $procedure = $process->procedure_id;
        $department_id = WKProcedure::where('id', $procedure)->first()->department_id;
        $viewRequestPermission = $this->getViewRequestsPermission($department_id);
        if ($viewRequestPermission) {
            $permission_id = Permission::with('roles')->where('name', $viewRequestPermission)->first();
            $rolesIds = $permission_id->roles->pluck('id')->toArray();
            $users = User::whereHas('roles', function ($query) use ($rolesIds, $rolesFromAccessRoles) {
                $query->whereIn('id', $rolesIds)->whereIn('id', $rolesFromAccessRoles);
            })->where('essentials_department_id', $department_id);
        }

        $input['task_id'] = $request->request_no;

        $to_dos = ToDo::create($input);
        $usersData = $users->get();

        $to_dos->users()->sync($usersData);

        $user_ids = $users->pluck('id')->toArray();
        $to = $users->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
            ->pluck('full_name')->toArray();
        if (!empty($user_ids)) {
            $to = [];
            $userName = User::where('id', $request->related_to)->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
                ->pluck('full_name')->toArray()[0];
            $sentNotification = SentNotification::create([
                'via' => 'dashboard',
                'type' => 'GeneralManagementNotification',
                'title' => $input['task'],
                'msg' => __('request.' . $request_type) . ' ' . $userName,
                'sender_id' => auth()->user()->id,
                'to' => json_encode($to),
            ]);
            // $details = new stdClass();
            // $details->title =  $input['task'];
            // $details->message = $request_type;

            foreach ($user_ids as $user_id) {
                SentNotificationsUser::create([
                    'sent_notifications_id' => $sentNotification->id,
                    'user_id' => $user_id,
                ]);
                // User::where('id', $user_id)->first()?->notify(new GeneralNotification($details, false, true));
            }
        }
    }
    // public function storeAgentRequests(Request $request)
    // {

    //     try {
    //         $business_id = request()->session()->get('user.business_id');
    //         $attachmentPath = $request->attachment ? $request->attachment->store('/requests_attachments') : null;
    //         $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
    //         $end_date = $request->end_date ?? $request->return_date;
    //         $today = Carbon::today();
    //         $var = RequestsType::where('id', $request->type)->first();
    //         $type = $var->type;
    //         $customer_department = $var->customer_department;

    //         if ($startDate && $type != 'escapeRequest') {
    //             $startDateCarbon = Carbon::parse($startDate);
    //             if ($startDateCarbon->lt($today)) {
    //                 $message = __('request.time_is_gone');
    //                 return redirect()->back()->withErrors([$message]);
    //             }
    //             if ($end_date) {

    //                 $endDateCarbon = Carbon::parse($end_date);

    //                 if ($startDateCarbon->gt($endDateCarbon)) {
    //                     $message = __('request.start_date_after_end_date');
    //                     return redirect()->back()->withErrors([$message]);
    //                 }
    //             }
    //         }

    //         if ($type == 'leavesAndDepartures' && is_null($request->leaveType)) {
    //             $output = [
    //                 'success' => false,
    //                 'msg' => __('request.please select the type of leave'),
    //             ];
    //             return redirect()->back()->withErrors([$output['msg']]);
    //         }

    //         $requestTypeFor = RequestsType::findOrFail($request->type)->for;
    //         $createdByUser = auth()->user();
    //         $createdBy_type = $createdByUser->user_type;
    //         $createdBy_department = $createdByUser->essentials_department_id;

    //         $success = 1;

    //         foreach ($request->user_id as $userId) {
    //             $count_of_users = count($request->user_id);
    //             if ($userId === null) continue;
    //             $isExists = UserRequest::where('related_to', $userId)->where('request_type_id', $request->type)->where('status', 'pending')->first();
    //             if ($isExists && count($request->user_id) == 1) {
    //                 $output = [
    //                     'success' => 0,
    //                     'msg' => __('request.this_user_has_this_request_recently'),
    //                 ];
    //                 return redirect()->back()->withErrors([$output['msg']]);
    //             }
    //             if (!$isExists) {

    //                 if ($type == "exitRequest") {

    //                     $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $userId)->first()->contract_end_date ?? null;
    //                 }
    //                 if ($type == "leavesAndDepartures") {
    //                     $leaveBalance = UserLeaveBalance::where([
    //                         'user_id' => $userId,
    //                         'essentials_leave_type_id' => $request->leaveType,
    //                     ])->first();

    //                     if (!$leaveBalance || $leaveBalance->amount == 0) {
    //                         if ($count_of_users == 1) {
    //                             $messageKey = !$leaveBalance ? 'this_user_cant_ask_for_leave_request' : 'this_user_has_not_enough_leave_balance';
    //                             $message = __("request.$messageKey");
    //                             DB::rollBack();
    //                             return redirect()->back()->withErrors([$message]);
    //                         }
    //                         continue;
    //                     } else {

    //                         $startDate = Carbon::parse($startDate);
    //                         $endDate = Carbon::parse($end_date);
    //                         $daysRequested = $startDate->diffInDays($endDate) + 1;

    //                         if ($daysRequested > $leaveBalance->amount) {
    //                             if ($count_of_users == 1) {
    //                                 $message = __("request.this_user_has_not_enough_leave_balance");
    //                                 DB::rollBack();
    //                                 return redirect()->back()->withErrors([$message]);
    //                             }
    //                             continue;
    //                         }
    //                     }
    //                 }
    //                 if ($type == 'cancleContractRequest' && !empty($request->main_reason)) {

    //                     $contract = EssentialsEmployeesContract::where('employee_id', $userId)->firstOrFail();
    //                     if (is_null($contract->wish_id)) {
    //                         if ($count_of_users == 1) {
    //                             $output = [
    //                                 'success' => false,
    //                                 'msg' => __('request.no_wishes_found'),
    //                             ];

    //                             return redirect()->back()->withErrors([$output['msg']]);
    //                         }
    //                         continue;
    //                     }
    //                     if (now()->diffInMonths($contract->contract_end_date) > 1) {
    //                         if ($count_of_users == 1) {
    //                             $output = [
    //                                 'success' => false,
    //                                 'msg' => __('request.contract_expired'),
    //                             ];

    //                             return redirect()->back()->withErrors([$output['msg']]);
    //                         }
    //                         continue;
    //                     }
    //                 }
    //                 $Request = new UserRequest;

    //                 $Request->request_no = $this->generateRequestNo($request->type);
    //                 $Request->related_to = $userId;
    //                 $Request->request_type_id = $request->type;
    //                 $Request->start_date = $startDate;
    //                 $Request->end_date = $end_date;
    //                 $Request->reason = $request->reason;
    //                 $Request->note = $request->note;
    //                 $Request->attachment = $attachmentPath;
    //                 $Request->essentials_leave_type_id = $request->leaveType;
    //                 $Request->escape_time = $request->escape_time;
    //                 $Request->installmentsNumber = $request->installmentsNumber;
    //                 $Request->monthlyInstallment = $request->monthlyInstallment;
    //                 $Request->advSalaryAmount = $request->amount;
    //                 $Request->created_by = auth()->user()->id;
    //                 $Request->insurance_classes_id = $request->ins_class;
    //                 $Request->baladyCardType = $request->baladyType;
    //                 $Request->resCardEditType = $request->resEditType;
    //                 $Request->workInjuriesDate = $request->workInjuriesDate;
    //                 $Request->contract_main_reason_id = $request->main_reason;
    //                 $Request->contract_sub_reason_id = $request->sub_reason;
    //                 $Request->visa_number = $request->visa_number;
    //                 $Request->atmCardType = $request->atmType;
    //                 $Request->authorized_entity = $request->authorized_entity;
    //                 $Request->commissioner_info = $request->commissioner_info;
    //                 $Request->trip_type = $request->trip_type;
    //                 $Request->Take_off_location = $request->Take_off_location;
    //                 $Request->destination = $request->destination;
    //                 $Request->weight_of_furniture = $request->weight_of_furniture;
    //                 $Request->date_of_take_off = $request->date_of_take_off;
    //                 $Request->time_of_take_off = $request->time_of_take_off;
    //                 $Request->return_date = $request->return_date_of_trip;

    //                 $Request->job_title_id = $request->job_title;
    //                 $Request->specialization_id = $request->profession;
    //                 $Request->nationality_id = $request->nationlity;
    //                 $Request->number_of_salary_inquiry = $request->number_of_salary_inquiry;

    //                 $Request->sale_project_id = $request->project_name;
    //                 $Request->interview_date = $request->interview_date;
    //                 $Request->interview_time = $request->interview_time;
    //                 $Request->interview_place = $request->interview_place;

    //                 $Request->save();

    //                 if ($attachmentPath) {
    //                     RequestAttachment::create([
    //                         'request_id' => $Request->id,
    //                         'file_path' => $attachmentPath,
    //                     ]);
    //                 }
    //                 if ($Request) {
    //                     $process = null;

    //                     $procedure = WkProcedure::where('business_id', $business_id)
    //                         ->where('request_type_id', $request->type)->where('start', 1)->where('department_id', $customer_department)->first();

    //                     if ($createdBy_type == 'manager' || $createdBy_type == 'admin') {

    //                         $nextProcedure = WkProcedure::where('business_id', $business_id)->where('request_type_id', $request->type)
    //                             ->where('department_id', $procedure->next_department_id)->first();

    //                         $process =   RequestProcess::create([

    //                             'request_id' => $Request->id,
    //                             'procedure_id' => $nextProcedure ? $nextProcedure->id : null,
    //                             'status' => 'pending',

    //                         ]);
    //                         if ($nextProcedure->action_type = 'task') {
    //                             $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
    //                             foreach ($procedureTasks as $task) {
    //                                 $requestTasks = new RequestProcedureTask();
    //                                 $requestTasks->request_id = $Request->id;
    //                                 $requestTasks->procedure_task_id = $task->id;
    //                                 $requestTasks->save();
    //                             }
    //                         }
    //                     } else {

    //                         $process = RequestProcess::create([

    //                             'request_id' => $Request->id,
    //                             'procedure_id' => $procedure ? $procedure->id : null,
    //                             'status' => 'pending',

    //                         ]);
    //                     }

    //                     if (!$process) {

    //                         RequestAttachment::where('request_id', $Request->id)->delete();
    //                         $Request->delete();
    //                     }
    //                 } else {

    //                     $success = 0;
    //                 }
    //             }
    //         }

    //         if ($success) {
    //             $this->makeToDo($Request, $business_id);
    //             $output = [
    //                 'success' => 1,
    //                 'msg' => __('messages.added_success'),
    //             ];
    //             return redirect()->back()->with('success', $output['msg']);
    //         } else {
    //             $output = [
    //                 'success' => 0,
    //                 'msg' => __('messages.something_went_wrong'),
    //             ];
    //             return redirect()->back()->withErrors([$output['msg']]);
    //         }
    //     } catch (\Exception $e) {
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    //         error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    //         $output = [
    //             'success' => false,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }
    // }
    public function storeAgentRequests(Request $request)
    {
        DB::beginTransaction();

        try {

            $attachmentPath = $request->hasFile('attachment') ? $request->attachment->store('/requests_attachments') : null;
            $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
            $end_date = $request->end_date ?? $request->return_date;
            $today = Carbon::today();
            $requestType = RequestsType::findOrFail($request->type);
            $type = $requestType->type;
            $customer_department = $requestType->customer_department;

            if ($this->isInvalidDateRange($type, $startDate, $end_date, $today)) {
                return redirect()->back()->withErrors([__('request.time_is_gone')]);
            }

            if ($type == 'leavesAndDepartures' && is_null($request->leaveType)) {
                return redirect()->back()->withErrors([__('request.please select the type of leave')]);
            }

            $createdByUser = auth()->user();
            $createdBy_type = $createdByUser->user_type;

            foreach ($request->user_id as $userId) {
                if ($userId === null) {
                    continue;
                }

                $business_id = User::where('id', $userId)->first()->business_id;
                if ($this->hasPendingRequest($userId, $request->type, $request->user_id)) {
                    return redirect()->back()->withErrors([__('request.this_user_has_this_request_recently')]);
                }

                if (!$this->processUserRequest($userId, $request, $type, $startDate, $end_date, $customer_department, $createdBy_type, $business_id, $attachmentPath)) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([__('messages.something_went_wrong')]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', __('messages.added_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            error_log($e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            return redirect()->back()->withErrors([__('messages.something_went_wrong')]);
        }
    }

    private function isInvalidDateRange($type, $startDate, $end_date, $today)
    {
        if ($startDate && $type != 'escapeRequest') {
            $startDateCarbon = Carbon::parse($startDate);
            if ($startDateCarbon->lt($today)) {
                return true;
            }
            if ($end_date) {
                $endDateCarbon = Carbon::parse($end_date);
                if ($startDateCarbon->gt($endDateCarbon)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasPendingRequest($userId, $requestTypeId, $userIds)
    {
        $isExists = UserRequest::where('related_to', $userId)
            ->where('request_type_id', $requestTypeId)
            ->where('status', 'pending')
            ->first();

        return $isExists && count($userIds) == 1;
    }

    private function processUserRequest($userId, $request, $type, $startDate, $end_date, $customer_department, $createdBy_type, $business_id, $attachmentPath)
    {
        if ($type == "exitRequest") {
            $startDate = DB::table('essentials_employees_contracts')
                ->where('employee_id', $userId)
                ->first()
                ->contract_end_date ?? null;
        }

        if ($type == "leavesAndDepartures" && !$this->validateLeaveBalance($userId, $request, $startDate, $end_date, $request->user_id)) {
            return false;
        }

        if ($type == 'cancleContractRequest' && !$this->validateContractCancellation($userId, $request, $request->user_id)) {
            return false;
        }

        $newRequest = $this->createUserRequest($request, $userId, $startDate, $end_date, $attachmentPath);

        if ($attachmentPath) {
            error_log('attachmentPath');
            RequestAttachment::create([
                'request_id' => $newRequest->id,
                'file_path' => $attachmentPath,
            ]);
        }

        return $this->processRequestProcedure($newRequest, $request->type, $business_id, $customer_department, $createdBy_type);
    }

    private function validateLeaveBalance($userId, $request, $startDate, $end_date, $userIds)
    {
        $leaveBalance = UserLeaveBalance::where([
            'user_id' => $userId,
            'essentials_leave_type_id' => $request->leaveType,
        ])->first();

        if (!$leaveBalance || $leaveBalance->amount == 0) {
            if (count($userIds) == 1) {
                $messageKey = !$leaveBalance ? 'this_user_cant_ask_for_leave_request' : 'this_user_has_not_enough_leave_balance';
                return redirect()->back()->withErrors([__("request.$messageKey")]);
            }
            return false;
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        if ($daysRequested > $leaveBalance->amount) {
            if (count($userIds) == 1) {
                return redirect()->back()->withErrors([__("request.this_user_has_not_enough_leave_balance")]);
            }
            return false;
        }
        return true;
    }

    private function validateContractCancellation($userId, $request, $userIds)
    {
        $contract = EssentialsEmployeesContract::where('employee_id', $userId)->firstOrFail();
        if (is_null($contract->wish_id)) {
            if (count($userIds) == 1) {
                return redirect()->back()->withErrors([__('request.no_wishes_found')]);
            }
            return false;
        }
        if (now()->diffInMonths($contract->contract_end_date) > 1) {
            if (count($userIds) == 1) {
                return redirect()->back()->withErrors([__('request.contract_expired')]);
            }
            return false;
        }
        return true;
    }

    private function createUserRequest($request, $userId, $startDate, $end_date, $attachmentPath)
    {
        error_log($userId);
        return UserRequest::create([
            'request_no' => $this->generateRequestNo($request->type),
            'related_to' => $userId,
            'request_type_id' => $request->type,
            'start_date' => $startDate,
            'end_date' => $end_date,
            'reason' => $request->reason,
            'note' => $request->note,
            'attachment' => $attachmentPath,
            'essentials_leave_type_id' => $request->leaveType,
            'escape_time' => $request->escape_time,
            'installmentsNumber' => $request->installmentsNumber,
            'monthlyInstallment' => $request->monthlyInstallment,
            'advSalaryAmount' => $request->amount,
            'created_by' => auth()->user()->id,
            'insurance_classes_id' => $request->ins_class,
            'baladyCardType' => $request->baladyType,
            'resCardEditType' => $request->resEditType,
            'workInjuriesDate' => $request->workInjuriesDate,
            'contract_main_reason_id' => $request->main_reason,
            'contract_sub_reason_id' => $request->sub_reason,
            'visa_number' => $request->visa_number,
            'atmCardType' => $request->atmType,
            'authorized_entity' => $request->authorized_entity,
            'commissioner_info' => $request->commissioner_info,
            'trip_type' => $request->trip_type,
            'Take_off_location' => $request->Take_off_location,
            'destination' => $request->destination,
            'weight_of_furniture' => $request->weight_of_furniture,
            'date_of_take_off' => $request->date_of_take_off,
            'time_of_take_off' => $request->time_of_take_off,
            'return_date' => $request->return_date_of_trip,
            'job_title_id' => $request->job_title,
            'specialization_id' => $request->profession,
            'nationality_id' => $request->nationlity,
            'number_of_salary_inquiry' => $request->number_of_salary_inquiry,
            'sale_project_id' => $request->project_name,
            'interview_date' => $request->interview_date,
            'interview_time' => $request->interview_time,
            'interview_place' => $request->interview_place,
        ]);
        error_log('request stored');
    }

    private function processRequestProcedure($request, $requestTypeId, $business_id, $customer_department, $createdBy_type)
    {
        error_log('processRequestProcedure');
        $procedure = WkProcedure::where('business_id', $business_id)
            ->where('request_type_id', $requestTypeId)
            ->where('start', 1)
            ->where('department_id', $customer_department)
            ->first();
        error_log($procedure->id);
        if ($createdBy_type == 'manager' || $createdBy_type == 'admin') {
            $nextProcedure = WkProcedure::where('business_id', $business_id)
                ->where('request_type_id', $requestTypeId)
                ->where('department_id', $procedure->next_department_id)
                ->first();

            $process = RequestProcess::create([
                'request_id' => $request->id,
                'procedure_id' => $nextProcedure ? $nextProcedure->id : null,
                'status' => 'pending',
            ]);

            if ($nextProcedure && $nextProcedure->action_type == 'task') {
                $this->createRequestProcedureTasks($request->id, $nextProcedure->id);
            }
            $this->makeToDo($request, $business_id);
        } else {
            error_log($request->id);
            $process = RequestProcess::create([
                'request_id' => $request->id,
                'procedure_id' => $procedure ? $procedure->id : null,
                'status' => 'pending',
            ]);
            $this->makeToDo($request, $business_id);
        }

        if (!$process) {
            RequestAttachment::where('request_id', $request->id)->delete();
            $request->delete();
            return false;
        }

        return true;
    }
    private function createRequestProcedureTasks($requestId, $procedureId)
    {
        $procedureTasks = ProcedureTask::where('procedure_id', $procedureId)->get();
        foreach ($procedureTasks as $task) {
            RequestProcedureTask::create([
                'request_id' => $requestId,
                'procedure_task_id' => $task->id,
            ]);
        }
    }
    public function generateRequestNo($request_type_id)
    {
        $type = RequestsType::where('id', $request_type_id)->first()->type;
        $RequestsTypes = RequestsType::where('type', $type)->pluck('id')->toArray();

        $latestRecord = UserRequest::whereIn('request_type_id', $RequestsTypes)->orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $prefix = $this->getTypePrefix($request_type_id);
            $numericPart = (int) substr($latestRefNo, strlen($prefix));
            $numericPart++;
            $input['request_no'] = $prefix . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $input['request_no'] = $this->getTypePrefix($request_type_id) . '0001';
        }

        return $input['request_no'];
    }
    public function agentRequests()
    {

        $user = User::where('id', auth()->user()->id)->first();

        $allRequestTypes = RequestsType::pluck('type', 'id');

        // $requestTypes = RequestsType::where('start_from_customer', 1)
        //     ->get()
        //     ->mapWithKeys(function ($requestType) {
        //         return [$requestType->id => $requestType->type];
        //     })
        //     ->unique()
        //     ->toArray();
        $requestTypes = RequestsType::where('start_from_customer', 1)->where('for', 'worker')
            ->get()
            ->map(function ($requestType) {
                return [
                    'id' => $requestType->id,
                    'type' => $requestType->type,
                    'for' => $requestType->for,
                ];
            })
            ->toArray();

        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $statuses = $this->statuses;
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->pluck('reason', 'id');
        $contact_id = $user->crm_contact_id;
        $saleProjects = SalesProject::where('contact_id', $contact_id)->pluck('name', 'id');

        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();

        $created_users = User::select(
            'id',
            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name")
        )->pluck('full_name', 'id');
        // error_log($projectsIds[0]);
        $users = User::where('user_type', 'worker')
            ->whereIn('users.assigned_to', $projectsIds)
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'inactive')
                            ->whereIn('sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })
            ->pluck('id')
            ->unique()
            ->toArray();

        $all_users = User::whereIn('id', $users)
            ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))
            ->get();

        $all_users = $all_users->pluck('full_name', 'id');

        $requestsProcess = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->whereNull('sub_status')->groupBy('request_id');

        $requestsProcess = UserRequest::select([

            'requests.request_no',
            'requests.id',
            'requests.request_type_id',
            'requests.is_new',
            'requests.created_at',
            'requests.created_by',
            'requests.reason',

            'process.id as process_id',
            'process.status',
            'process.note as note',
            'process.procedure_id as procedure_id',
            'process.superior_department_id as superior_department_id',

            'wk_procedures.action_type as action_type',
            'wk_procedures.department_id as department_id',
            'wk_procedures.can_return',
            'wk_procedures.start as start',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'users.id_proof_number',
            'users.assigned_to',
            'users.id as userId',
            'users.company_id',

        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftjoin('procedure_tasks', 'procedure_tasks.procedure_id', '=', 'wk_procedures.id')
            ->leftjoin('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
            ->leftjoin('request_procedure_tasks', function ($join) {
                $join->on('request_procedure_tasks.procedure_task_id', '=', 'procedure_tasks.id')
                    ->on('request_procedure_tasks.request_id', '=', 'requests.id');
            })
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')

            ->whereIn('requests.related_to', $users)
            ->groupBy('requests.id')->orderBy('requests.created_at', 'desc');

        if (request()->input('status') && request()->input('status') !== 'all') {

            $requestsProcess->where('process.status', request()->input('status'));
        }

        if (request()->input('type') && request()->input('type') !== 'all') {

            $types = RequestsType::where('type', request()->input('type'))->pluck('id')->toArray();
            $requestsProcess->whereIn('requests.request_type_id', $types);
        }
        if (request()->input('company') && request()->input('company') !== 'all') {
            error_log(request()->input('company'));
            $requestsProcess->where('users.company_id', request()->input('company'));
        }
        if (request()->input('project') && request()->input('project') !== 'all') {
            error_log(request()->input('project'));
            $requestsProcess->where('users.assigned_to', request()->input('project'));
        }

        $userId = auth()->id();

        $requests = $requestsProcess
            ->where('requests.created_by', $userId)
            ->get();

        foreach ($requests as $request) {
            $tasksDetails = DB::table('request_procedure_tasks')
                ->join('procedure_tasks', 'procedure_tasks.id', '=', 'request_procedure_tasks.procedure_task_id')
                ->join('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
                ->where('procedure_tasks.procedure_id', $request->procedure_id)
                ->where('request_procedure_tasks.request_id', $request->id)
                ->select('tasks.description', 'request_procedure_tasks.id', 'request_procedure_tasks.procedure_task_id', 'tasks.link', 'request_procedure_tasks.isDone', 'procedure_tasks.procedure_id')
                ->get();

            $request->tasksDetails = $tasksDetails;
        }

        if (request()->ajax()) {

            return DataTables::of($requests ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    if ($row->request_type_id) {
                        return $allRequestTypes[$row->request_type_id];
                    }
                })
                ->editColumn('assigned_to', function ($row) use ($saleProjects) {
                    if ($row->assigned_to) {
                        return $saleProjects[$row->assigned_to];
                    } else {
                        return '';
                    }
                })
                ->editColumn('id_proof_number', function ($row) {
                    if ($row->id_proof_number) {
                        $expiration_date = optional(
                            DB::table('essentials_official_documents')
                                ->where('employee_id', $row->userId)
                                ->where('type', 'residence_permit')
                                ->where('is_active', 1)
                                ->first()
                        )->expiration_date;

                        return $row->id_proof_number . '<br>' . $expiration_date;
                    } else {
                        return '';
                    }
                })
                ->addColumn('created_user', function ($row) use ($created_users) {

                    return $created_users[$row->created_by];
                })
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        $status = trans('request.' . $row->status);

                        return $status;
                    }
                })

                ->editColumn('can_return', function ($row) {
                    $buttonsHtml = '';

                    $buttonsHtml .= '<button class="btn btn-success btn-sm btn-view-request-details" data-request-id="' . $row->id . '">' . trans('request.view_request_details') . '</button>';
                    $buttonsHtml .= '<button class="btn btn-xs btn-view-activities" style="background-color: #6c757d; color: white;" data-request-id="' . $row->id . '">' . trans('request.view_activities') . '</button>';

                    return $buttonsHtml;
                })

                ->rawColumns(['status', 'request_type_id', 'can_return', 'id_proof_number', 'created_user', 'assigned_to'])

                ->make(true);
        }

        $companies = Company::pluck('name', 'id');
        $all_status = ['approved', 'pending', 'rejected'];
        return view('custom_views.agents.requests.allRequest')->with(compact(
            'users',
            'all_users',
            'requestTypes',
            'statuses',
            'main_reasons',
            'classes',
            'saleProjects',
            'leaveTypes',
            'job_titles',
            'specializations',
            'nationalities',
            'allRequestTypes',
            'all_status',
            'companies'
        ));
    }
    public function getViewRequestsPermission($department)
    {
        $departments = [
            'followup' => ['names' => ['%متابعة%'], 'permission' => 'followup.view_followup_requests'],
            'accounting' => ['names' => ['%حاسب%', '%مالي%'], 'permission' => 'accounting.view_accounting_requests'],
            'workcard' => ['names' => ['%حكومية%'], 'permission' => 'essentials.view_workcards_request'],
            'hr' => ['names' => ['%بشرية%'], 'permission' => 'essentials.view_HR_requests'],
            'employee_affairs' => ['names' => ['%موظف%'], 'permission' => 'essentials.view_employees_affairs_requests'],
            'insurance' => ['names' => ['%تأمين%'], 'permission' => 'essentials.crud_insurance_requests'],
            'payroll' => ['names' => ['%رواتب%'], 'permission' => 'essentials.view_payroll_requests'],
            'housing' => ['names' => ['%سكن%'], 'permission' => 'housingmovements.crud_htr_requests'],
            'international_relations' => ['names' => ['%دولي%'], 'permission' => 'internationalrelations.view_ir_requests'],
            'legal' => ['names' => ['%قانوني%'], 'permission' => 'legalaffairs.view_legalaffairs_requests'],
            'sales' => ['names' => ['%مبيعات%'], 'permission' => 'sales.view_sales_requests'],
            'ceo' => ['names' => ['%تنفيذ%'], 'permission' => 'ceomanagment.view_CEO_requests'],
            'general' => ['names' => ['%مجلس%', '%عليا%'], 'permission' => 'generalmanagement.view_president_requests'],
        ];

        foreach ($departments as $dept => $info) {
            $deptIds = EssentialsDepartment::where(function ($query) use ($info) {
                foreach ($info['names'] as $name) {
                    $query->orWhere('name', 'like', $name);
                }
            })->pluck('id')->toArray();

            if (in_array($department, $deptIds)) {

                return $info['permission'];
            }
        }

        return null;
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
                'purchase_return',
                'sell_return',
                'expense',
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
                        return '<a href="#" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]) . '"
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
                        return '<a href="#" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]) . '"
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
