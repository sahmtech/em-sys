<?php

namespace Modules\HousingMovements\Http\Controllers;


use App\User;
use Carbon\Carbon;

use App\Request as UserRequest;
use App\RequestProcess;
use App\Utils\RequestUtil;
use App\Utils\ModuleUtil;
use Modules\CEOManagment\Entities\RequestsType;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsDepartment;

use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;
use Modules\Sales\Entities\SalesProject;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    protected $requestUtil;


    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }

    public function index()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housing_move_dashbord = auth()->user()->can('housingmovements.housing_move_dashbord');
        if (!($is_admin || $can_housing_move_dashbord)) {
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
        $EssentailsEmployeeOperation_emplyeeIds = EssentailsEmployeeOperation::whereIn('employee_id', $userIds)->where('operation_type', 'final_visa')->pluck('employee_id');
        $final_exit_count = User::whereIn('id', $userIds)->whereIn('id', $EssentailsEmployeeOperation_emplyeeIds)->where('user_type', 'worker')->where('status', 'inactive')->count();


        $reserved_shopping_count = HousingMovementsWorkerBooking::whereIn('user_id', $userIds)->count();
        $bookedWorker_ids = HousingMovementsWorkerBooking::whereIn('user_id', $userIds)->pluck('user_id');

        $HtrRoomsWorkersHistory_roomIds = HtrRoomsWorkersHistory::all()->pluck('room_id');

        $empty_rooms_count = HtrRoom::whereNotIn('id', $HtrRoomsWorkersHistory_roomIds)->count();

        $available_shopping_count = User::whereIn('id', $userIds)->where('user_type', 'worker')->whereNull('assigned_to')->whereNotIn('id', $bookedWorker_ids)->count();


        $business_id = request()->session()->get('user.business_id');


        //$can_change_status = auth()->user()->can('housingmovements.change_status');
        // $can_return_request = auth()->user()->can('housingmovements.return_the_request');
        // $can_show_request = auth()->user()->can('housingmovements.view_request');
        $allRequestTypes = RequestsType::pluck('type', 'id');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%سكن%')
            ->pluck('id')->toArray();


        $requestsProcess = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')
            ->groupBy('request_id');
        $leavesTypes = RequestsType::where('type', 'leavesAndDepartures')->pluck('id')->toArray();
        $leaves_count = UserRequest::leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')->whereIn('requests.related_to', $userIds)->whereIn('requests.request_type_id', $leavesTypes)->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('request_processes.superior_department_id', $departmentIds);
            })->count();
        $requestsProcess = UserRequest::select([
            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.reason',

            'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

            'wk_procedures.department_id as department_id', 'wk_procedures.can_return',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number',

        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
            // ->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('process.superior_department_id', $departmentIds);
            })


            ->whereIn('requests.related_to', $userIds)->whereNull('process.sub_status')
            ->where('users.status', '!=', 'inactive');


        if (request()->ajax()) {

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    return $allRequestTypes[$row->request_type_id];
                })
                ->editColumn('status', function ($row) {
                    $status = trans('request.' . $row->status);

                    return $status;
                })
                // ->editColumn('can_return', function ($row) use ($is_admin, $can_show_request) {
                //     $buttonsHtml = '';

                //     if ($is_admin || $can_show_request) {
                //         $buttonsHtml .= '<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' . $row->id . '">' . trans('essentials::lang.view_request') . '</button>';
                //     }

                //     return $buttonsHtml;
                // })

                ->rawColumns(['status', 'request_type_id'])


                ->make(true);
        }


        return view('housingmovements::dashboard.hm_dashboard', compact('empty_rooms_count', 'leaves_count', 'available_shopping_count', 'reserved_shopping_count', 'final_exit_count'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::create');
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
        return view('housingmovements::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('housingmovements::edit');
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