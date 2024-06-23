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
use Illuminate\Support\Facades\DB;
use Modules\FollowUp\Entities\FollowupUserAccessProject;

class FollowupUserAccessProjectController extends Controller
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
        $can_add_user_project_access_permissions = auth()->user()->can('followup.add_user_project_access_permissions');


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
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%متابعة%')
                    ->orWhere(function ($query) {
                        $query->where('name', 'LIKE', '%تشغيل%')
                            ->where('name', 'LIKE', '%أعمال%');
                    })->orWhere(function ($query) {
                        $query->where('name', 'LIKE', '%تشغيل%')
                            ->where('name', 'LIKE', '%شركات%');
                    });
            })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
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
                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_add_user_project_access_permissions) {
                        $html = '';

                        if ($is_admin  || $can_add_user_project_access_permissions) {

                            $html .= '<a href="#" class="btn btn-xs btn-primary add_access_project_btn" data-id="' . $row->id . '" data-url="' . route('getUserProjectsPermissions', ['userId' => $row->id]) . '">' . __('followup::lang.edit_project') . '</a>&nbsp;';
                        }
                        return $html;
                    }
                )

                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment', 'action'])
                ->make(true);
        }



        return view('followup::projects_access_permissions.index')->with(compact('projects',));
    }

    public function getUserProjectsPermissions($userId)
    {
        $followupUserAccessProject = FollowupUserAccessProject::where('user_id', $userId)->pluck('sales_project_id')->unique()->toArray();
        $selected_projects = [];
        if ($followupUserAccessProject && !empty($followupUserAccessProject)) {
            $selected_projects = json_encode(SalesProject::whereIn('id', $followupUserAccessProject)->pluck('name', 'id'));
        }
        return response()->json(['projects' => $selected_projects]);
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
        $success = true;

        $projects_ids = $request->projects_ids;
        if ($projects_ids) {
            FollowupUserAccessProject::where('user_id', $request->user_id)->delete();
            foreach ($projects_ids as $project_id) {
                FollowupUserAccessProject::create([
                    'user_id' => $request->user_id,
                    'sales_project_id' => $project_id,
                ]);
            }
        }

        if ($success) {
            $output = $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
            return redirect()->back()->with(['status' => $output]);
        } else {
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->route('projects_access_permissions')->with(['status' => $output]);
        }
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
