<?php

namespace Modules\FollowUp\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Contact;
use App\Transaction;
use App\User;
use App\ContactLocation;
use Modules\Sales\Entities\SalesProject;
use DataTables;
use DB;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentailsReasonWish;

class FollowUpContractsWishesController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */


    public function index()
    {
        $business_id = request()->session()->get('user.business_id');



        //  $can_crud_projects = auth()->user()->can('followup.crud_projects');
        //  if (!$can_crud_projects) {
        //     //temp  abort(403, 'Unauthorized action.');
        //  }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        
        $workers = User::whereIn('users.id',$userIds)->join('sales_projects', 'users.assigned_to', '=', 'sales_projects.id')
            ->join('contacts', 'sales_projects.contact_id', '=', 'contacts.id')
            ->leftjoin('essentials_employees_contracts', 'essentials_employees_contracts.employee_id', 'users.id')
            ->where('users.user_type', 'worker')
            ->whereNotNull('essentials_employees_contracts.wish_id')
            ->select(
                'users.id',
                'users.emp_number as emp_number',
                'users.first_name as first_name',
                'users.mid_name as mid_name ',
                'users.last_name as last_name',
                'users.id_proof_number as residency',
                'sales_projects.name as project_name',
                'essentials_employees_contracts.contract_start_date as contract_start_date',
                'essentials_employees_contracts.contract_end_date as contract_end_date',
                'essentials_employees_contracts.wish_id as wish',
                'essentials_employees_contracts.wish_id as wish_file',
            );


        if (!empty(request()->input('wish_status_filter'))) {
            $workers->where('essentials_employees_contracts.wish_id', request()->input('wish_status_filter'));
        }

        if (!empty(request()->input('project_name_filter'))) {
            $workers->where('sales_projects.id', request()->input('project_name_filter'));
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (request()->ajax()) {

            return DataTables::of($workers)
                ->addColumn('name', function ($row) {
                    return $row->first_name . ' ' . $row->mid_name . ' ' . $row->last_name;
                })
                ->editColumn('wish', function ($row) {

                    $wishReason = EssentailsReasonWish::where('id', $row->wish)->value('reason');

                    return $wishReason;
                })

                ->editColumn('action', function ($row) use($is_admin) {
                    if (!empty($row->wish)) {
                        if (($is_admin  || auth()->user()->can('followup.change_wish'))) {
                            $button = '<a href="#" class="btn btn-xs btn-success change-status-btn" data-toggle="modal"
                        data-target="#change_status_modal" data-employee-id="' . $row->id . '"  data-orig-value="' . $row->wish . '">
                        ' . __('followup::lang.change_wish') . '
                    </a>';
                        }
                        return $button;
                    }
                })


                ->filterColumn('project_name', function ($query, $keyword) {
                    $query->whereHas('contactLocations', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->filterColumn('residency', function ($query, $keyword) {
                    $query->whereRaw("residency  like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $wishes = EssentailsReasonWish::where('type', 'wish')
            ->where('employee_type', 'worker')
            ->pluck('reason', 'id');
        $projects = SalesProject::pluck('name', 'id');

        $employees = User::where('users.user_type', 'worker')
            ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
             ' - ',COALESCE(users.id_proof_number,'')) as full_name"), 'users.id',)->get();

        return view('followup::contracts_wishes.index', compact('projects', 'wishes', 'employees'));
    }

    public function getWishFile($employeeId)
    {
        try {


            $emp_wish = EssentialsEmployeesContract::where('employee_id', $employeeId)->first();

            if (!empty($emp_wish)) {
                $wishFile = $emp_wish->wish_file;
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return response()->json(['success' => true, 'wish_file' => $wishFile]);
    }


    public function add_wish(Request $request)
    {
        $selectedEmployeeId = $request->input('employees');
        $employee_type = $request->input('employee_type');
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        try {
            $employeeId = $request->input('employee_id');
            $wish = $request->input('wish');
            $wishFile = $request->file('file');

            $emp_wish = EssentialsEmployeesContract::where('employee_id',   $selectedEmployeeId)->first();
            $emp_wish->wish_id = $wish;

            if (!empty($wishFile)) {
                if (request()->hasFile('file')) {
                    $file = request()->file('file');
                    $filePath = $file->store('/employeeContracts');


                    $emp_wish->wish_file = $filePath;
                }
            }


            $emp_wish->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('contracts_wishes');
    }
    public function changeWish(Request $request)
    {
        try {

            $employeeId = $request->input('employee_id');
            $wish = $request->input('wish');
            $wishFile = $request->file('file');

            $emp_wish = EssentialsEmployeesContract::where('employee_id', $employeeId)->first();
            $emp_wish->wish_id = $wish;

            if (!empty($wishFile)) {
                if (request()->hasFile('file')) {
                    $file = request()->file('file');
                    $filePath = $file->store('/employeeContracts');


                    $emp_wish->wish_file = $filePath;
                }
            }


            $emp_wish->save();

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

        return response()->json($output);
    }



    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('followup::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
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
        return view('followup::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('followup::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
    }
}