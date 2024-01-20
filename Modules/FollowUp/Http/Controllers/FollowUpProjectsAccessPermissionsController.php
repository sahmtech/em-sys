<?php

namespace Modules\FollowUp\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;

class FollowUpProjectsAccessPermissionsController extends Controller
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_projects_access_permissions = auth()->user()->can('followup.projects_access_permissions');
        if (!($is_admin || $can_followup_projects_access_permissions)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $projects = SalesProject::all()->pluck('name', 'id');
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds);
        });
        if (request()->ajax()) {

            return Datatables::of($users)

                ->editColumn('business_location_id', function ($row) {
                    $item = $business_locations[$row->business_location_id] ?? '';

                    return $item;
                })

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin,) {
                        $html = '';
                        // if ($is_admin  || $can_edit_employee_appointments) {
                        //     $html .= '<a  href="' . route('appointment.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                        //     '&nbsp;';
                        // }

                        // if ($is_admin  || $can_delete_employee_appointments) {
                        //     $html .= '&nbsp; <button class="btn btn-xs btn-danger delete_appointment_button" data-href="' . route('appointment.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        // }



                        return $html;
                    }
                )

                // ->filterColumn('user', function ($query, $keyword) {
                //     $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                // })
                // ->filterColumn('id_proof_number', function ($query, $keyword) {
                //     $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                // })
                // ->filterColumn('status', function ($query, $keyword) {
                //     $query->whereRaw("status  like ?", ["%{$keyword}%"]);
                // })

                ->removeColumn('id')
                ->rawColumns(['action', ])
                ->make(true);
        }



        return view('followup::projects_access_permissions.index');
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
        //
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
