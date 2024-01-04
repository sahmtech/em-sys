<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


use App\BusinessLocation;
use App\User;

class EssentialsDepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }


    public function treeIndex()
    {
        $business_id = request()->session()->get('user.business_id');


        $can_crud_organizational_structure = auth()->user()->can('essentials.crud_organizational_structure');
        if (!$can_crud_organizational_structure) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user());


        $departments = EssentialsDepartment::where('parent_department_id', '=', 0)
            ->where('business_id', '=', $business_id)->get();
        $alldepartments = EssentialsDepartment::pluck('name', 'id')
            ->where('business_id', '=', $business_id)->all();

        return view('essentials::settings.partials.departments.index',  compact('departments', 'alldepartments'));
    }

    public function storeNode(Request $request)
    {
        $order = EssentialsDepartment::first();
        $business_id = request()->session()->get('user.business_id');

        if (is_null($order)) {
            $newNode = EssentialsDepartment::create([
                'name' => $request->input('new_text'),
                'parent_department_id' => 0,
                'level' => 1,
                'business_id' => $business_id
            ]);

            return response()->json(['message' => 'Node added successfully'], 200);
        } else {


            $Pid = $request->input('parent_id');
            $level = $request->input('level');

            $newNode = EssentialsDepartment::create([
                'name' => $request->input('new_text'),
                'parent_department_id' => $Pid,
                'level' => $level + 1,
                'business_id' => $business_id
            ]);

            return response()->json(['message' => 'Node added successfully'], 200);
        }
    }

    public function updateNode(Request $request, $id)
    {
        $newText = $request->input('new_text');

        $model = EssentialsDepartment::findOrFail($id);

        $model->name =  $newText;

        $model->save();


        return response()->json(['message' => 'Node edited successfully']);
    }


    private function deleteNodeRecursively($node)
    {
        foreach ($node->childs as $child) {
            $this->deleteNodeRecursively($child);
        }

        $node->delete();
    }

    public function deletenode($id)
    {

        $node = EssentialsDepartment::find($id);

        if (!$node) {
            return response()->json(['error' => 'Node not found'], 404);
        } else {

            $this->deleteNodeRecursively($node);

            return response()->json(['message' => 'Node and its children deleted successfully']);
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');


        $can_crud_depatments = auth()->user()->can('essentials.crud_departments');
        if (!$can_crud_depatments) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user());
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $parent_departments = EssentialsDepartment::where('is_main', '1')->pluck('name', 'id');
        if (request()->ajax()) {
            $depatments = DB::table('essentials_departments')
                ->where('business_id', '=', $business_id)
                ->select(['id', 'name', 'level', 'is_main', 'parent_department_id', 'business_id', 'is_active'])
                ->orderBy('id', 'asc');


            return Datatables::of($depatments)
                ->editColumn('parent_department_id', function ($row) use ($departments) {
                    $item = $departments[$row->parent_department_id] ?? '';

                    return $item;
                })
                ->addColumn('manager_name', function ($row) {

                    $manager = DB::table('essentials_employee_appointmets')
                        ->join('users', 'essentials_employee_appointmets.employee_id', '=', 'users.id')
                        ->where('essentials_employee_appointmets.department_id', $row->id)
                        ->where('essentials_employee_appointmets.type', 'appoint')
                        ->where('users.user_type', 'manager')
                        ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"))
                        ->first();

                    return $manager ? $manager->user : '<button type="button" class="btn btn-xs btn-primary open-modal" data-toggle="modal" data-target="#addAppointmentModal" data-row-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> ' . __('essentials::lang.add_manager') . '</button>';
                })
                ->addColumn('delegatingManager_name', function ($row) {

                    $delegatingManager = DB::table('essentials_employee_appointmets')
                        ->join('users', 'essentials_employee_appointmets.employee_id', '=', 'users.id')
                        ->where('essentials_employee_appointmets.department_id', $row->id)
                        ->where('essentials_employee_appointmets.type', 'delegating')
                        ->where('users.user_type', 'manager')
                        ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"))
                        ->first();

                    return $delegatingManager ? $delegatingManager->user : '<button type="button" class="btn btn-xs btn-success open-modal" data-toggle="modal" data-target="#addDelegatingModal" data-row-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> ' . __('essentials::lang.manager_delegating') . '</button>';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('dep.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                            //   $html .='<button type="button" class="btn btn-xs btn-primary open-modal" data-toggle="modal" data-target="#editDepartment" data-row-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                            $html .= '<button type="button" class="btn btn-xs btn-primary open-modal" data-toggle="modal" data-target="#editDepartment" data-row-id="' . $row->id . '" data-info-route="' . route('getDepartmentInfo', $row->id) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';

                            '&nbsp;';
                            $html .= '<button class="btn btn-xs btn-danger delete_department_button" data-href="' . route('department.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action', 'manager_name', 'delegatingManager_name'])
                ->make(true);
        }
        $query = User::where('business_id', $business_id)->where('users.user_type', '=', 'manager');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as 
     full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $business_locations = BusinessLocation::all()->where('business_id', $business_id)->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        return view('essentials::settings.partials.departments.index')->with(compact('parent_departments', 'users', 'departments', 'business_locations', 'specializations', 'professions'));
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());



        try {
            EssentialsDepartment::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
    public function store(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());



        try {
            $input = $request->only(['name', 'level', 'is_main', 'address']);


            $input2['name'] = $input['name'];
            $input2['level'] = $input['level'];
            if ($request->parent_level != Null) {
                $input2['parent_department_id'] = $request->parent_level;
            } else {
                $input2['parent_department_id'] = '0';
            }
            $input2['is_main'] = $input['is_main'];
            $input2['address'] = $input['address'];
            $input2['business_id'] = $business_id;


            EssentialsDepartment::create($input2);
            $count = Role::where('name', $input['name'] . '#' . $business_id)
                ->where('business_id', $business_id)
                ->count();
            if ($count == 0) {

                $is_service_staff = 0;
                if ($request->input('is_service_staff') == 1) {
                    $is_service_staff = 1;
                }
                $role = Role::create([
                    'name' => $input['name'] . '#' . $business_id,
                    'business_id' => $business_id,
                    'is_service_staff' => $is_service_staff,
                ]);
                $permission = Permission::where('name', 'dashboard.data')->first()->id;
                $role->syncPermissions($permission);
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('user.role_already_exists'),
                ];
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('departments');
    }
    public function storeManager($id, Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());



        try {
            $input = $request->only(['employee', 'start_date', 'profession', 'specialization']);

            $input2['employee_id'] = $input['employee'];
            $input2['department_id'] = $id;
            $input2['start_from'] = $input['start_date'];
            $input2['profession_id'] = $input['profession'];
            $input2['specialization_id'] = $input['specialization'];
            $input2['type'] = 'appoint';


            EssentialsEmployeeAppointmet::create($input2);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }

    public function manager_delegating($id, Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());



        try {
            $input = $request->only(['employee', 'profession', 'specialization', 'start_date', 'end_date']);

            $input2['employee_id'] = $input['employee'];
            $input2['department_id'] = $id;

            $input2['profession_id'] = $input['profession'];
            $input2['specialization_id'] = $input['specialization'];
            $input2['start_from'] = $input['start_date'];
            $input2['end_at'] = $input['end_date'];
            $input2['type'] = 'delegating';



            EssentialsEmployeeAppointmet::create($input2);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }
    public function getDepartmentInfo($id)
    {
        $department = EssentialsDepartment::find($id);
        $manager = DB::table('essentials_employee_appointmets')
            ->join('users', 'essentials_employee_appointmets.employee_id', '=', 'users.id')
            ->where('essentials_employee_appointmets.department_id', $id)
            ->where('essentials_employee_appointmets.type', 'appoint')
            ->where('users.user_type', 'manager')
            ->select(
                'users.id as id',
                'essentials_employee_appointmets.profession_id as profession_id',
                'essentials_employee_appointmets.profession_id as specialization_id',
                'essentials_employee_appointmets.start_from as start_from',
            )
            ->first();
        $delegate = DB::table('essentials_employee_appointmets')
            ->join('users', 'essentials_employee_appointmets.employee_id', '=', 'users.id')
            ->where('essentials_employee_appointmets.department_id', $id)
            ->where('essentials_employee_appointmets.type', 'delegating')
            ->where('users.user_type', 'manager')
            ->select(
                'users.id as id',
                'essentials_employee_appointmets.profession_id as profession_id',
                'essentials_employee_appointmets.profession_id as specialization_id',
                'essentials_employee_appointmets.start_from as start_from',
                'essentials_employee_appointmets.end_at as end_at',

            )
            ->first();
        return response()->json([
            'name' => $department->name,
            'level' => $department->level,
            'is_main' => $department->is_main,
            'parent_department_id' => $department->parent_department_id,
            'address' => $department->address,
            'is_active' => $department->is_active,
            'manager' => $manager ? $manager->id : null,
            'profession_id' => $manager ? $manager->profession_id : null,
            'specialization_id' => $manager ? $manager->specialization_id : null,
            'manager_start_from' => $manager ? $manager->start_from : null,
            'delegate' => $delegate ? $delegate->id : null,
            'delegate_profession_id' => $delegate ? $delegate->profession_id : null,
            'delegate_specialization_id' => $delegate ? $delegate->specialization_id : null,
            'delegate_start_from' => $delegate ? $delegate->start_from : null,
            'delegate_end_at' => $delegate ? $delegate->end_at : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $department = EssentialsDepartment::find($id);

            $departmentData = [
                'name' => $request->filled('name') ? $request->input('name') : $department->name,
                'level' => $request->filled('level') ? $request->input('level') : $department->level,
                'is_main' => $request->filled('is_main') ? $request->input('is_main') : $department->is_main,
                'parent_department_id' => ($request->filled('level') && $request->input('level') == 'first_level') ? null : ($request->filled('parent_level') ? $request->input('parent_level') : $department->parent_department_id),
                'address' => $request->filled('address') ? $request->input('address') : $department->address,
                'is_active' => $request->filled('is_active') ? $request->input('is_active') : $department->is_active,
            ];

            if (!empty($departmentData)) {
                EssentialsDepartment::where('id', $id)->update($departmentData);
            }

            $managerId = $request->input('manager');
            $delegateId = $request->input('delegate');
            if ($managerId) {

                $managerAppointment = EssentialsEmployeeAppointmet::where('employee_id', $managerId)
                    ->where('department_id', $id)
                    ->where('type', 'appoint')
                    ->first();

                if ($managerAppointment) {

                    $managerAppointment->update([
                        'profession_id' => $request->filled('profession') ? $request->input('profession') : $managerAppointment->profession_id,
                        'specialization_id' => $request->filled('specialization') ? $request->input('specialization') : $managerAppointment->specialization_id,
                        'start_from' => $request->filled('start_date') ? $request->input('start_date') : $managerAppointment->start_from,
                    ]);
                } else {


                    EssentialsEmployeeAppointmet::create([
                        'employee_id' => $managerId,
                        'department_id' => $id,
                        'type' => 'appoint',
                        'profession_id' => $request->filled('profession') ? $request->input('profession') : null,
                        'specialization_id' => $request->filled('specialization') ? $request->input('specialization') : null,
                        'start_from' => $request->filled('start_date') ? $request->input('start_date') : null,
                    ]);
                }
            }
            if ($delegateId) {


                $delegateAppointment = EssentialsEmployeeAppointmet::where('employee_id', $delegateId)
                    ->where('department_id', $id)
                    ->where('type', 'delegating')
                    ->first();

                if ($delegateAppointment) {


                    $delegateAppointment->update([
                        'profession_id' => $request->filled('profession2') ? $request->input('profession2') : $delegateAppointment->profession_id,
                        'specialization_id' => $request->filled('specialization2') ? $request->input('specialization2') : $delegateAppointment->specialization_id,
                        'start_from' => $request->filled('start_date2') ? $request->input('start_date2') : $delegateAppointment->start_from,
                        'end_at' => $request->filled('end_date2') ? $request->input('end_date2') : $delegateAppointment->end_at,
                    ]);
                } else {


                    EssentialsEmployeeAppointmet::create([
                        'employee_id' => $delegateId,
                        'department_id' => $id,
                        'type' => 'delegating',
                        'profession_id' => $request->filled('profession2') ? $request->input('profession2') : null,
                        'specialization_id' => $request->filled('specialization2') ? $request->input('specialization2') : null,
                        'start_from' => $request->filled('start_date2') ? $request->input('start_date2') : null,
                        'end_at' => $request->filled('end_date2') ? $request->input('end_date2') : null,
                    ]);
                }
            }

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
        return $output;
    }

    public function show($id)
    {

        $department = EssentialsDepartment::find($id);

        $manager = DB::table('essentials_employee_appointmets')
            ->join('users', 'essentials_employee_appointmets.employee_id', '=', 'users.id')
            ->where('essentials_employee_appointmets.department_id', $id)
            ->where('essentials_employee_appointmets.type', 'appoint')
            ->where('users.user_type', 'manager')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as managername"),
                'essentials_employee_appointmets.profession_id as profession_id',
                'essentials_employee_appointmets.profession_id as specialization_id',
                'essentials_employee_appointmets.start_from as start_from',
            )
            ->first();

        $delegate = DB::table('essentials_employee_appointmets')
            ->join('users', 'essentials_employee_appointmets.employee_id', '=', 'users.id')
            ->where('essentials_employee_appointmets.department_id', $id)
            ->where('essentials_employee_appointmets.type', 'delegating')
            ->where('users.user_type', 'manager')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as delegatename"),
                'essentials_employee_appointmets.profession_id as profession_id',
                'essentials_employee_appointmets.profession_id as specialization_id',
                'essentials_employee_appointmets.start_from as start_from',
                'essentials_employee_appointmets.end_at as end_at',

            )
            ->first();
        $parentDepartment = EssentialsDepartment::find($department->parent_department_id);
        $parentDepartmentName = $parentDepartment ? $parentDepartment->name : null;

        $professionName = $manager ? EssentialsProfession::find($manager->profession_id)->name : null;
        $specializationName = $manager ? EssentialsProfession::find($manager->specialization_id)->name : null;

        $professionNamedelegate = $delegate ? EssentialsProfession::find($delegate->profession_id)->name : null;
        $specializationNamedelegate = $delegate ? EssentialsProfession::find($delegate->specialization_id)->name : null;

        return view('essentials::settings.partials.departments.show', [
            'name' => $department->name,
            'level' => $department->level,
            'is_main' => $department->is_main,
            'parent_department_id' => $parentDepartmentName,
            'address' => $department->address,
            'is_active' => $department->is_active,

            'manager' => $manager ? $manager->managername : null,
            'profession_id' => $professionName,
            'specialization_id' => $specializationName,
            'manager_start_from' => $manager ? $manager->start_from : null,

            'delegate' => $delegate ? $delegate->delegatename : null,
            'delegate_profession_id' => $professionNamedelegate,
            'delegate_specialization_id' => $specializationNamedelegate,
            'delegate_start_from' => $delegate ? $delegate->start_from : null,
            'delegate_end_at' => $delegate ? $delegate->end_at : null,
        ]);
    }
}
