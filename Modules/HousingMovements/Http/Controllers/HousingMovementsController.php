<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsDepartment;
use DB;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;


class HousingMovementsController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
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

        return view('housingmovements::index');
    }
    public function department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_department_employees = auth()->user()->can('housingmovements.housingmovements_view_department_employees');


        if (!($is_admin || $can_department_employees)) {
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
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'LIKE', '%سكن%')
                ->where('name', 'LIKE', '%حرك%');
        })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereIn('user_type', ['employee', 'manager'])->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
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

        return view('housingmovements::department_employees');
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