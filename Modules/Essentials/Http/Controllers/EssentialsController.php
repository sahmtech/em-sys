<?php

namespace Modules\Essentials\Http\Controllers;

use App\Charts\CommonChart;
use App\Utils\ModuleUtil;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;

use DB;
use Illuminate\Support\Carbon;

class EssentialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        if (!(auth()->user()->can('essentials.essentials_dashboard') && auth()->user()->can('essentials.view_work_cards'))) {
            return redirect()->route('essentials_word_cards_dashboard');
        }
        $business_id = request()->session()->get('user.business_id');

        $num_employee_staff = User::where('business_id', $business_id)->where(function ($query) {
            $types = ['worker', 'employee', 'manager'];

            foreach ($types as $type) {
                $query->orWhere('user_type', 'like', '%' . $type . '%');
            }
        })->count();
        $num_workers = User::where('business_id', $business_id)->where('user_type', 'like', '%worker%')->count();
        $num_employees = User::where('business_id', $business_id)->where('user_type', 'like', '%employee%')->count();


        $num_managers = User::where('business_id', $business_id)->where('user_type', 'like', '%manager%')->count();
        $chart = new CommonChart;
        $colors = [
            '#E75E82', '#37A2EC', '#FACD56', '#5CA85C', '#605CA8',
            '#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce',
            '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'
        ];
        $labels = [__('user.worker'), __('user.employee'), __('user.manager')];
        $values = [$num_workers, $num_employees, $num_managers];
        $chart->labels($labels)
            ->options($this->__chartOptions())
            ->dataset(__('user.employee_staff'), 'pie', $values)
            ->color($colors);

        return view('essentials::index', compact('chart', 'num_employee_staff', 'num_workers', 'num_employees', 'num_managers'));
    }

    public function word_cards_dashboard()
    {

        $business_id = request()->session()->get('user.business_id');

    

        $expiryDateThreshold = Carbon::now()->addDays(15)->toDateString();
        $sixtyday=Carbon::now()->addDays(60)->toDateString();

        $last15_expire_date_residence = EssentialsOfficialDocument::where('type', 'residence_permit')
                ->where('expiration_date', '<=', $expiryDateThreshold)
                ->count();
        
        $today = Carbon::now()->toDateString();

     
        $all_ended_residency_date = EssentialsOfficialDocument::where('type', 'residence_permit')
                    ->where('expiration_date', '<', $today )  // Adjusted to check for expiration dates in the past
                   ->count();

        $escapeRequest = FollowupWorkerRequest::with('user')->where('type', 'escapeRequest')
        ->whereHas('user', function ($query) {
            $query->where('user_type', 'worker');
        })
                    ->where('end_date', '<=', $sixtyday)
                    ->count();


        $vacationrequest = FollowupWorkerRequest::with('user')->where('type', 'leavesAndDepartures') 
        ->whereHas('user', function ($query) {
            $query->where('user_type', 'worker');
        })
        ->count();

        $final_visa = EssentailsEmployeeOperation::where('operation_type', 'final_visa') 
        ->whereHas('user', function ($query) {
            $query->where('user_type', 'worker');
        })
        ->count();


        $late_vacation = FollowupWorkerRequest::with(['user'])
                    ->where('type', 'leavesAndDepartures')
                    ->where('type', 'returnRequest') 
                    ->whereHas('user', function ($query)  {
                        
                        $query->where('status', 'vecation');
                    })
                    ->where('end_date', '<', now()) 
                    ->count();
                



        return view('essentials::work_cards_index')
        ->with(compact('last15_expire_date_residence' ,
        'all_ended_residency_date','escapeRequest','vacationrequest','final_visa','late_vacation'
        ));
    }


    private function __chartOptions()
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










    public function getLeaveStatusData()
    {
        $business_id = request()->session()->get('user.business_id');


        $rawLeaveStatusData = FollowupWorkerRequest::where('type', 'leavesAndDepartures')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();


        $leaveStatusData = [];
        foreach ($rawLeaveStatusData as $status => $count) {
            $translatedLabel = trans('lang_v1.' . $status);
            $leaveStatusData[$translatedLabel] = $count;
        }

        $data = [
            'labels' => array_keys($leaveStatusData),
            'values' => array_values($leaveStatusData),
        ];
        

        return response()->json($data);
    }



    public function getContractStatusData()
    {
        $business_id = request()->session()->get('user.business_id');

        $totalContracts = EssentialsEmployeesContract::count();

        $expiredContractsByEndDate = EssentialsEmployeesContract::whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<', now())
            ->count();

        $expiredContractsByProbation = EssentialsEmployeesContract::whereNotNull('probation_period')
            ->where(function ($query) {
                $query->whereNull('contract_end_date')
                    ->orWhere(function ($endDateSubquery) {
                        $endDateSubquery->whereNotNull('contract_start_date')
                            ->whereRaw('DATE_ADD(contract_start_date, INTERVAL probation_period MONTH) < NOW()');
                    });
            })
            ->count();

        $data = [
            'labels' => [
                __('essentials::lang.expired_contracts'),
                __('essentials::lang.remaining_contracts'),
            ],
            'values' => [
                ($totalContracts > 0) ? ($expiredContractsByEndDate / $totalContracts * 100) : 0,
                ($totalContracts > 0) ? ($expiredContractsByProbation / $totalContracts * 100) : 0,
            ],
        ];

        return response()->json($data);
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy()
    {
    }
}
