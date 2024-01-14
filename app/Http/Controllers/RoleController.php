<?php

namespace App\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use App\Contact;
use App\SellingPriceGroup;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * All Utils instance.
     */
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (!($is_admin || auth()->user()->can('roles.view'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $roles = Role::where('business_id', $business_id)
                ->select(['name', 'id', 'is_default', 'business_id']);

            return DataTables::of($roles)
                ->addColumn('action', function ($row) use ($is_admin) {
                    if (!$row->is_default || $row->name == 'Cashier#' . $row->business_id) {
                        $action = '';
                        if (($is_admin  || auth()->user()->can('roles.update'))) {
                            $action .= '<a href="' . action([\App\Http\Controllers\RoleController::class, 'editOrCreateAccessRole'], [$row->id]) . '" class="btn btn-success btn-xs">' . __('messages.update_access_role') . '</a>';

                            $action .= '&nbsp
                            <a href="' . action([\App\Http\Controllers\RoleController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                        }
                        if (($is_admin  || auth()->user()->can('roles.delete'))) {
                            $action .= '&nbsp
                                <button data-href="' . action([\App\Http\Controllers\RoleController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_role_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $action;
                    } else {
                        return '';
                    }
                })
                ->editColumn('name', function ($row) use ($business_id) {
                    $role_name = str_replace('#' . $business_id, '', $row->name);
                    if (in_array($role_name, ['Admin', 'Cashier'])) {
                        $role_name = __('lang_v1.' . $role_name);
                    }

                    return $role_name;
                })
                ->removeColumn('id')
                ->removeColumn('is_default')
                ->removeColumn('business_id')
                ->rawColumns([1])
                ->make(false);
        }

        return view('role.index');
    }

    public function editOrCreateAccessRole($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin  || auth()->user()->can('roles.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $accessRole = AccessRole::where('role_id', $id)->first();
        if (!$accessRole) {
            $accessRole = new AccessRole();
            $accessRole->role_id = $id;
            $accessRole->save();
        }
        $accessRoleProjects = AccessRoleProject::where('access_role_id',  $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
        $accessRoleBusinesses = AccessRoleBusiness::where('access_role_id',  $accessRole->id)->pluck('business_id')->unique()->toArray();
        //$contacts = Contact::with('salesProject')->select(['id', 'supplier_business_name'])->get();
        $businesses = Business::with('contacts.salesProjects')->get();
        return view('role.edit_create_access_role')
            ->with(compact('accessRole', 'accessRoleProjects', 'accessRoleBusinesses', 'businesses'));
    }
    public function updateAccessRole(Request $request, $roleId)
    {
        $projectsIds = $request->projects ?? [];
        AccessRoleBusiness::where('access_role_id', $roleId)->delete();
        if (!empty($projectsIds)) {
            AccessRoleBusiness::where('access_role_id', $roleId)->delete();
            foreach ($projectsIds as $projectsId) {
                AccessRoleProject::create([
                    'access_role_id' =>  $roleId,
                    'sales_project_id' => $projectsId,
                ]);
            }
        }
        $businessIds = $request->businesses ?? [];
        AccessRoleBusiness::where('access_role_id', $roleId)->delete();
        if (!empty($businessIds)) {

            foreach ($businessIds as $businessId) {
                AccessRoleBusiness::create([
                    'access_role_id' =>  $roleId,
                    'business_id' => $businessId,
                ]);
            }
        }
        $output = [
            'success' => 1,
            'msg' => __('user.role_updated'),
        ];
        return redirect('roles')->with('status', $output);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin  || auth()->user()->can('roles.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();

        $temp = $this->moduleUtil->getModuleData('user_permissions');
        $module_permissions = [];
  
        foreach ($temp as $temp_item) {
            foreach($temp_item as $permission_item ){
                 $module_permissions[] = $permission_item;
            }
           
        }
      //  return $module_permissions;
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('role.create')
            ->with(compact('selling_price_groups', 'module_permissions', 'common_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('roles.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $role_name = $request->input('name');
            $permissions = $request->input('permissions');
            $business_id = $request->session()->get('user.business_id');

            $count = Role::where('name', $role_name . '#' . $business_id)
                ->where('business_id', $business_id)
                ->count();
            if ($count == 0) {
                $is_service_staff = 0;
                if ($request->input('is_service_staff') == 1) {
                    $is_service_staff = 1;
                }

                $role = Role::create([
                    'name' => $role_name . '#' . $business_id,
                    'business_id' => $business_id,
                    'is_service_staff' => $is_service_staff,
                ]);

                //Include selling price group permissions
                $spg_permissions = $request->input('radio_option');
                if (!empty($spg_permissions)) {
                    foreach ($spg_permissions as $spg_permission) {
                        $permissions[] = $spg_permission;
                    }
                }

                $radio_options = $request->input('radio_option');
                if (!empty($radio_options)) {
                    foreach ($radio_options as $key => $value) {
                        $permissions[] = $value;
                    }
                }

                $this->__createPermissionIfNotExists($permissions);

                if (!empty($permissions)) {
                    $role->syncPermissions($permissions);
                }
                $output = [
                    'success' => 1,
                    'msg' => __('user.role_added'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('user.role_already_exists'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('roles')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('roles.update'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $role = Role::where('business_id', $business_id)
            ->with(['permissions'])
            ->find($id);
        $role_permissions = [];
        foreach ($role->permissions as $role_perm) {
            $role_permissions[] = $role_perm->name;
        }

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();

        $module_permissions = $this->moduleUtil->getModuleData('user_permissions');

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('role.edit')
            ->with(compact('role', 'role_permissions', 'selling_price_groups', 'module_permissions', 'common_settings'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('roles.update'))) {
            //temp  abort(403, 'Unauthorized action.');
        }


        try {
            $role_name = $request->input('name');
            $permissions = $request->input('permissions');
            $business_id = $request->session()->get('user.business_id');

            $count = Role::where('name', $role_name . '#' . $business_id)
                ->where('id', '!=', $id)
                ->where('business_id', $business_id)
                ->count();
            if ($count == 0) {
                $role = Role::findOrFail($id);

                if (!$role->is_default || $role->name == 'Cashier#' . $business_id) {
                    if ($role->name == 'Cashier#' . $business_id) {
                        $role->is_default = 0;
                    }

                    $is_service_staff = 0;
                    if ($request->input('is_service_staff') == 1) {
                        $is_service_staff = 1;
                    }
                    $role->is_service_staff = $is_service_staff;
                    $role->name = $role_name . '#' . $business_id;
                    $role->save();

                    //Include selling price group permissions
                    $spg_permissions = $request->input('spg_permissions');
                    if (!empty($spg_permissions)) {
                        foreach ($spg_permissions as $spg_permission) {
                            $permissions[] = $spg_permission;
                        }
                    }

                    $radio_options = $request->input('radio_option');
                    if (!empty($radio_options)) {
                        foreach ($radio_options as $key => $value) {
                            $permissions[] = $value;
                        }
                    }

                    $this->__createPermissionIfNotExists($permissions);

                    if (!empty($permissions)) {
                        $role->syncPermissions($permissions);
                    }

                    $output = [
                        'success' => 1,
                        'msg' => __('user.role_updated'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('user.role_is_default'),
                    ];
                }
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('user.role_already_exists'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('roles')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('roles.delete'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $role = Role::where('business_id', $business_id)->find($id);

                if (!$role->is_default || $role->name == 'Cashier#' . $business_id) {
                    $role->delete();
                    $output = [
                        'success' => true,
                        'msg' => __('user.role_deleted'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('user.role_is_default'),
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Creates new permission if doesn't exist
     *
     * @param  array  $permissions
     * @return void
     */
    private function __createPermissionIfNotExists($permissions)
    {
        $exising_permissions = Permission::whereIn('name', $permissions)
            ->pluck('name')
            ->toArray();

        $non_existing_permissions = array_diff($permissions, $exising_permissions);

        if (!empty($non_existing_permissions)) {
            foreach ($non_existing_permissions as $new_permission) {
                $time_stamp = \Carbon::now()->toDateTimeString();
                Permission::create([
                    'name' => $new_permission,
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
