<?php

namespace Modules\Sales\Http\Controllers;

use App\Charts\CommonChart;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\SalesOrdersOperation;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;
use DB;
class SalesController extends Controller
{

    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
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
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
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
        $workers = User::where('user_type', 'worker');
        $workers_count = $workers->count();
        $active_workers_count = $workers->where('status', 'active')->count();
        $inactive_workers_count = $workers->whereNot('status', 'active')->count();
        $under_study_price_offers = Transaction::where([['business_id', $user->business_id], ['type', 'sell'], ['sub_type', 'service'], ['status', 'under_study']])->count();


        $chart = new CommonChart;
        $colors = [
            '#E75E82', '#37A2EC', '#FACD56', '#5CA85C', '#605CA8',
            '#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce',
            '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'
        ];
        $labels = [
            __('sales::lang.active_workers_count'),
            __('sales::lang.inactive_workers_count'),

        ];
        $values = [
            $active_workers_count,
            $inactive_workers_count,

        ];
        $chart->labels($labels)
            ->options($this->__chartOptions2())
            ->dataset(__('sales::lang.workers_count'), 'pie', $values)
            ->color($colors);

        return view('sales::index', compact(
            'active_workers_count',
            'inactive_workers_count',
            'workers_count',
            'under_study_price_offers',
            'chart',
            'widgets',
            'common_settings'
        ));
    }

    public function sales_department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_sales_view_department_employees = auth()->user()->can('sales.sales_view_department_employees');


        if (!($is_admin || $can_sales_view_department_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%مبيعات%')
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds);
        })->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"),
            'users.id_proof_number',
        ]);
        if (request()->ajax()) {

            return Datatables::of($users)

                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'full_name',
                    function ($row) {
                        return $row->full_name;
                    }
                )
                ->addColumn(
                    'id_proof_number',
                    function ($row) {
                        return $row->id_proof_number;
                    }
                )
                ->addColumn(
                    'appointment',
                    function ($row) {
                        return $row->appointment?->profession->name ?? '';
                    }
                )


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment'])
                ->make(true);
        }

        return view('sales::sales_department_employees');
    }

    public function getOperationAvailableContracts()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $business_id =  $user->business_id;

        $offer_prices = Transaction::where('business_id', $business_id)
            ->select('id', 'ref_no')->get();

        $contracts = [];

        foreach ($offer_prices as $key) {

            $contract = salesContract::where('offer_price_id', $key->id)
                ->where('status', 'valid')
                ->select('number_of_contract', 'id')
                ->first();

            if ($contract) {
                $contractQuantity = TransactionSellLine::where('transaction_id', $key->id)->sum('quantity');
                $salesOrdersQuantity = SalesOrdersOperation::where('sale_contract_id', $contract->id)->sum('orderQuantity');

                $totalQuantity = $contractQuantity - $salesOrdersQuantity;
                $contract->refNo = $key->ref_no;
                if ($totalQuantity > 0) {
                    $contract->totalQuantity = $totalQuantity;
                    $contracts[] = $contract;
                }
            }
        }


        return Datatables::of($contracts)
            ->addColumn('number_of_contract', function ($row) {
                return $row->number_of_contract;
            })
            ->addColumn('total_quantity', function ($row) {

                return $row->totalQuantity;
            })
            ->addColumn('ref_no', function ($row) {

                return $row->refNo;
            })

            ->rawColumns(['number_of_contract', 'total_quantity', 'ref_no'])
            ->removeColumn('id')
            ->make(true);
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
        //
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
    public function destroy($id)
    {
        //
    }
}
