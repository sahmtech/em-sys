<?php

namespace Modules\CEOManagment\Http\Controllers;

use App\Business;
use App\Request as UserRequest;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsProceduresEscalation;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\ProcedureTask;
use Modules\CEOManagment\Entities\ProcedureEscalation;
use Modules\CEOManagment\Entities\Task;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\CEOManagment\Entities\RequestProcedureTask;


class WkProcedureController extends Controller
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

    public function employeesProcedures()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delete_procedures = auth()->user()->can('ceomanagment.delete_procedure');
        $can_edit_procedures = auth()->user()->can('ceomanagment.edit_procedure');


        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');

        $escalates_departments = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%')
                    ->orWhere('name', 'like', '%تنفيذ%');
            })
            ->pluck('name', 'id')->toArray();
        $requestsType = RequestsType::where('for', 'employee')->pluck('type', 'id');
        $actualTypes = WkProcedure::distinct()->where('request_owner_type', 'employee')->pluck('request_type_id')->toArray();

        $missingTypes = array_diff_key($requestsType->toArray(), array_flip($actualTypes));

        $procedures = WkProcedure::where('business_id', $business_id)->where('request_owner_type', 'employee')
            ->groupBy('request_type_id')
            ->with('department');

        if (request()->ajax()) {

            return DataTables::of($procedures)
                ->editColumn('request_type_id', function ($row) use ($requestsType) {
                    $item = $requestsType[$row->request_type_id] ?? '';

                    return $item;
                })
                ->addColumn('steps', function ($procedure) {
                    try {
                        $stepsData = WkProcedure::where('request_type_id', $procedure->request_type_id)
                            ->with(['department', 'nextDepartment'])->where('start', 1)
                            ->orderBy('start', 'desc')
                            ->get();

                        $stepsFormatted = [];

                        foreach ($stepsData as $step) {
                            $sequence = [];
                            $end = false;
                            $loopStep = $step;
                            while (!$end) {
                                $departmentName = $loopStep->department->name;
                                $sequence[] = $departmentName;
                                $end = $loopStep->end;

                                if (!$end) {
                                    $loopStep = WkProcedure::where('department_id', $loopStep->next_department_id)
                                        ->where('request_type_id', $loopStep->request_type_id)
                                        ->with(['department', 'nextDepartment'])
                                        ->first();
                                }
                            }

                            $sequenceString = implode(' -> ', $sequence);
                            $stepsFormatted[] = "<li>{$sequenceString}</li>";
                        }
                        return '<ul>' . implode('', $stepsFormatted) . '</ul>';
                    } catch (\Exception $e) {
                        error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                        return '';
                    }
                })->addColumn('action', function ($row) use ($is_admin, $can_delete_procedures, $can_edit_procedures) {
                    $html = '';
                    if ($is_admin || $can_edit_procedures) {

                        $html .= '<a href="#" class="btn btn-xs btn-primary edit-procedure" data-id="' . $row->id . '" data-url="' . route('getProcedure', ['procedure_id' => $row->id]) . '">' . __('messages.edit') . '</a>&nbsp;';
                    }
                    if ($is_admin || $can_delete_procedures) {
                        $html .= '<button class="btn btn-xs btn-danger delete_procedure_button" data-href="' . route('procedure.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }
                    return $html;
                })
                ->rawColumns(['steps', 'action'])
                ->make(true);
        }
        $tasks = Task::all()->pluck('description', 'id');
        return view('ceomanagment::work_flow.employees')->with(compact('departments', 'tasks', 'escalates_departments', 'missingTypes'));
    }

    public function workersProcedures()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delete_procedures = auth()->user()->can('ceomanagment.delete_procedure');
        $can_edit_procedures = auth()->user()->can('ceomanagment.edit_procedure');


        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');

        $escalates_departments = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')->orWhere('name', 'like', '%عام%');
            })
            ->pluck('name', 'id')->toArray();
        $requestsType = RequestsType::where('for', 'worker')->pluck('type', 'id');
        $actualTypes = WkProcedure::distinct()->where('request_owner_type', 'worker')->pluck('request_type_id')->toArray();

        $missingTypes = array_diff_key($requestsType->toArray(), array_flip($actualTypes));

        $procedures = WkProcedure::where('business_id', $business_id)->where('request_owner_type', 'worker')
            ->groupBy('request_type_id')
            ->with('department');

        if (request()->ajax()) {

            return DataTables::of($procedures)
                ->editColumn('request_type_id', function ($row) use ($requestsType) {
                    $item = $requestsType[$row->request_type_id] ?? '';

                    return $item;
                })
                ->addColumn('steps', function ($procedure) {
                    try {
                        $stepsData = WkProcedure::where('request_type_id', $procedure->request_type_id)
                            ->with(['department', 'nextDepartment'])->where('start', 1)
                            ->orderBy('start', 'desc')
                            ->get();

                        $stepsFormatted = [];

                        foreach ($stepsData as $step) {
                            $sequence = [];
                            $end = false;
                            $loopStep = $step;
                            while (!$end) {
                                $departmentName = $loopStep->department->name;
                                $sequence[] = $departmentName;
                                $end = $loopStep->end;

                                if (!$end) {
                                    $loopStep = WkProcedure::where('department_id', $loopStep->next_department_id)
                                        ->where('request_type_id', $loopStep->request_type_id)
                                        ->with(['department', 'nextDepartment'])
                                        ->first();
                                }
                            }

                            $sequenceString = implode(' -> ', $sequence);
                            $stepsFormatted[] = "<li>{$sequenceString}</li>";
                        }
                        return '<ul>' . implode('', $stepsFormatted) . '</ul>';
                    } catch (\Exception $e) {

                        return '';
                    }
                })->addColumn('action', function ($row) use ($is_admin, $can_delete_procedures, $can_edit_procedures) {
                    $html = '';
                    if ($is_admin || $can_edit_procedures) {

                        $html .= '<a href="#" class="btn btn-xs btn-primary edit-procedure" data-id="' . $row->id . '" data-url="' . route('getProcedure', ['procedure_id' => $row->id]) . '">' . __('messages.edit') . '</a>&nbsp;';
                    }
                    if ($is_admin || $can_delete_procedures) {
                        $html .= '<button class="btn btn-xs btn-danger delete_procedure_button" data-href="' . route('procedure.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }
                    return $html;
                })
                ->rawColumns(['steps', 'action'])
                ->make(true);
        }
        $tasks = Task::all()->pluck('description', 'id');
        return view('ceomanagment::work_flow.workers')->with(compact('departments', 'tasks', 'escalates_departments', 'missingTypes'));
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function getProcedure($procedure_id)
    {

        $procedureType = WkProcedure::where('id', $procedure_id)->first()->request_type_id;

        $procedures = WkProcedure::with('department')->where('request_type_id', $procedureType)->get();
        $superior_dep =  RequestsType::where('id', $procedureType)->first()->goes_to_superior;
        error_log($superior_dep);
        foreach ($procedures as $procedure) {
            $escalations = ProcedureEscalation::where('procedure_id', $procedure->id)->get();
            $tasks = ProcedureTask::where('procedure_id', $procedure->id)->get();


            $taskIds = $tasks->pluck('task_id');

            $taskDescriptions = Task::whereIn('id', $taskIds)->get(['id', 'description']);


            $procedure->escalations = $escalations;
            $procedure->tasks = $taskDescriptions;
        }


        return response()->json(['procedures' => $procedures, 'superior_dep' => $superior_dep]);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function storeWorkerProcedure(Request $request)
    {

        $type = $request->input('type');
        $steps = $request->input('step');

        \DB::beginTransaction();
        try {

            $add_modal_department_id_start = $request->add_modal_department_id_start;

            $check_repeated = [];
            foreach ($add_modal_department_id_start as $start_dep) {
                $check_repeated[] = $start_dep;
            }
            foreach ($steps  as $index => $step) {
                $check_repeated[] = $step['add_modal_department_id_steps'][0];
            }
            if (count($check_repeated) !== count(array_unique($check_repeated))) {
                throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
            }

            $previousStepIds = [];
            foreach ($add_modal_department_id_start as $start_dep) {
                if ($start_dep) {
                    $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                    $workflowStep = WkProcedure::create([
                        'request_type_id' => $type,
                        'request_owner_type' => 'worker',
                        'department_id' => $start_dep,
                        'business_id' => $business_id,
                        'next_department_id' => null,
                        'start' => 1,
                        'end' => 0,
                        'can_reject' => 1,
                        'can_return' =>  null,
                    ]);
                    $previousStepIds[] = $workflowStep->id;
                }
            }

            foreach ($steps  as $index => $step) {
                $start_dep = $step['add_modal_department_id_steps'][0];
                $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                $workflowStep = WkProcedure::create([
                    'request_type_id' => $type,
                    'request_owner_type' => 'worker',
                    'department_id' => $start_dep,
                    'business_id' => $business_id,
                    'next_department_id' => null,
                    'start' => 0,
                    'end' => $index === count($steps) - 1 ? 1 : 0,
                    'can_reject' => $step['add_modal_can_reject_steps'][0] ?? 0,
                    'can_return' => $step['add_modal_can_return_steps'][0] ?? 0,
                    'action_type' => $step['action_type'] ?? null,
                ]);
                if (isset($step['tasks']) && $step['action_type'] === 'task') {
                    foreach ($step['tasks'] as $taskId) {
                        if (!is_null($taskId)) {
                            ProcedureTask::create([
                                'procedure_id' => $workflowStep->id,
                                'task_id' => $taskId,
                            ]);
                        }
                    }
                }
                foreach ($previousStepIds as $id) {
                    WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
                }
                $previousStepIds = [];
                $previousStepIds[] = $workflowStep->id;
                if (!(empty($step['add_modal_escalates_to_steps']) || empty($step['add_modal_escalates_after_steps']))) {
                    foreach ($step['add_modal_escalates_to_steps'] as $key => $escalationDept) {
                        ProcedureEscalation::create([
                            'procedure_id' => $workflowStep->id,
                            'escalates_to' => $escalationDept,
                            'escalates_after' => $step['add_modal_escalates_after_steps'][$key] ?? null,
                        ]);
                    }
                }
            }


            \DB::commit();

            $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            \DB::rollBack();
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' =>  $e->getMessage()];
        }

        return redirect()->route('workersProcedures')->with(['status' => $output]);
    }


    public function storeEmployeeProcedure(Request $request)
    {

        $type = $request->input('type');
        $steps = $request->input('step');


        \DB::beginTransaction();
        try {


            $check_repeated = [];
            foreach ($steps  as $index => $step) {
                $check_repeated[] = $step['add_modal_department_id_steps'][0];
            }
            if (count($check_repeated) !== count(array_unique($check_repeated))) {
                throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
            }
            RequestsType::where('id', $type)->update(['goes_to_superior' => $request->superior_department]);

            $previousStepIds = [];
            foreach ($steps  as $index => $step) {
                $start_dep = $step['add_modal_department_id_steps'][0];
                $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                $workflowStep = WkProcedure::create([
                    'request_type_id' => $type,
                    'request_owner_type' => 'employee',
                    'department_id' => $start_dep,
                    'business_id' => $business_id,
                    'next_department_id' => null,
                    'start' => $index === 0 ? 1 : 0,
                    'end' => $index === count($steps) - 1 ? 1 : 0,
                    'can_reject' => $step['add_modal_can_reject_steps'][0] ?? 0,
                    'can_return' => $step['add_modal_can_return_steps'][0] ?? 0,
                    'action_type' => $step['action_type'] ?? null,
                ]);
                if (isset($step['tasks']) && $step['action_type'] === 'task') {
                    foreach ($step['tasks'] as $taskId) {
                        if (!is_null($taskId)) {
                            ProcedureTask::create([
                                'procedure_id' => $workflowStep->id,
                                'task_id' => $taskId,
                            ]);
                        }
                    }
                }

                foreach ($previousStepIds as $id) {
                    WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
                }
                $previousStepIds = [];
                $previousStepIds[] = $workflowStep->id;
                if (!(empty($step['add_modal_escalates_to_steps']) || empty($step['add_modal_escalates_after_steps']))) {
                    foreach ($step['add_modal_escalates_to_steps'] as $key => $escalationDept) {
                        ProcedureEscalation::create([
                            'procedure_id' => $workflowStep->id,
                            'escalates_to' => $escalationDept,
                            'escalates_after' => $step['add_modal_escalates_after_steps'][$key] ?? null,
                        ]);
                    }
                }
            }


            \DB::commit();

            $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            \DB::rollBack();
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' =>  $e->getMessage()];
        }

        return redirect()->route('employeesProcedures')->with(['status' => $output]);
    }


    public function update(Request $request, $id)
    {
        //return $request;
        try {
            $type = WkProcedure::where('id', $id)->first()->request_type_id;
            $requests = UserRequest::where('request_type_id', $type)->get();
            if ($requests->count() != 0) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.cant_edit_procedure_it_have_requests'),
                ];
                return redirect()->back()->with(['status' => $output]);
            }
            $procedureType = WkProcedure::where('id', $id)->first()->request_type_id;
            $for = WkProcedure::where('id', $id)->first()->request_owner_type;


            $type = $procedureType;

            $steps = $request->input('step');

            $edit_modal_department_id_start = $request->edit_modal_department_id_start;

            $check_repeated = [];
            if ($edit_modal_department_id_start) {
                foreach ($edit_modal_department_id_start as $start_dep) {
                    $check_repeated[] = $start_dep;
                }
            }
            if ($steps) {
                foreach ($steps  as $index => $step) {
                    $check_repeated[] = $step['edit_modal_department_id_steps'][0];
                }
            }

            if (count($check_repeated) !== count(array_unique($check_repeated))) {
                throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
            }

            $procedures = WkProcedure::where('request_type_id', $procedureType)->get();
            if ($procedures) {
                foreach ($procedures as $procedure) {

                    ProcedureTask::where('procedure_id', $procedure->id)->delete();
                    ProcedureEscalation::where('procedure_id', $procedure->id)->delete();
                    $procedure->delete();
                }
            }
            $previousStepIds = [];
            if ($edit_modal_department_id_start) {
                foreach ($edit_modal_department_id_start as $start_dep) {
                    if ($start_dep) {
                        $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                        $workflowStep = WkProcedure::create([
                            'request_type_id' => $type,
                            'request_owner_type' => $for,
                            'department_id' => $start_dep,
                            'business_id' => $business_id,
                            'next_department_id' => null,
                            'start' => 1,
                            'end' => 0,
                            'can_reject' => null,
                            'can_return' =>  null,
                        ]);
                        $previousStepIds[] = $workflowStep->id;
                    }
                }
            }

            if ($steps) {
                $index = 0;
                foreach ($steps  as $step) {

                    error_log($index);
                    $start_dep = $step['edit_modal_department_id_steps'][0];
                    $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                    $workflowStep = WkProcedure::create([
                        'request_type_id' => $type,
                        'request_owner_type' => $for,
                        'department_id' => $start_dep,
                        'business_id' => $business_id,
                        'next_department_id' => null,
                        'start' => 0,
                        'end' => $index === count($steps) - 1 ? 1 : 0,
                        'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
                        'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
                        'action_type' => $step['edit_action_type'] ?? null,
                    ]);
                    if (isset($step['edit_tasks']) && $step['edit_action_type'] === 'task') {
                        foreach ($step['edit_tasks'] as $taskId) {
                            if (!is_null($taskId)) {
                                ProcedureTask::create([
                                    'procedure_id' => $workflowStep->id,
                                    'task_id' => $taskId,
                                ]);
                            }
                        }
                    }

                    foreach ($previousStepIds as $id) {
                        WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
                    }
                    $previousStepIds = [];
                    $previousStepIds[] = $workflowStep->id;
                    if (!(empty($step['edit_modal_escalates_to_steps']) || empty($step['edit_modal_escalates_after_steps']))) {
                        foreach ($step['edit_modal_escalates_to_steps'] as $key => $escalationDept) {
                            ProcedureEscalation::create([
                                'procedure_id' => $workflowStep->id,
                                'escalates_to' => $escalationDept,
                                'escalates_after' => $step['edit_modal_escalates_after_steps'][$key] ?? null,
                            ]);
                        }
                    }
                    $index++;
                }
            }



            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' =>  $e->getMessage()];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    public function updateEmployeeProcedure(Request $request, $id)
    {
        // return $request->all();

        try {
            $type = WkProcedure::where('id', $id)->first()->request_type_id;
            error_log($type);
            $requests = UserRequest::where('request_type_id', $type)->get();
            if ($requests->count() != 0) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.cant_edit_procedure_it_have_requests'),
                ];
                return redirect()->back()->with(['status' => $output]);
            }




            $steps = $request->input('step');

            $check_repeated = [];
            foreach ($steps  as $index => $step) {
                $check_repeated[] = $step['edit_modal_department_id_steps'][0];
            }
            if (count($check_repeated) !== count(array_unique($check_repeated))) {
                throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
            }
            $procedures = WkProcedure::where('request_type_id', $type)->get();

            if ($procedures) {
                foreach ($procedures as $procedure) {
                    ProcedureTask::where('procedure_id', $procedure->id)->delete();
                    ProcedureEscalation::where('procedure_id', $procedure->id)->delete();
                    $procedure->delete();
                }
            }
            RequestsType::where('id', $type)->update(['goes_to_superior' => $request->superior_department]);

            $previousStepIds = [];
            if ($steps) {
                $index = 0;
                foreach ($steps  as $step) {

                    $start_dep = $step['edit_modal_department_id_steps'][0];
                    $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                    $workflowStep = WkProcedure::create([
                        'request_type_id' => $type,
                        'request_owner_type' => 'employee',
                        'department_id' => $start_dep,
                        'business_id' => $business_id,
                        'next_department_id' => null,
                        'start' => $index === 0 ? 1 : 0,
                        'end' => $index === count($steps) - 1 ? 1 : 0,
                        'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
                        'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
                        'action_type' => $step['edit_action_type'] ?? null,
                    ]);
                    if (isset($step['edit_tasks']) && $step['edit_action_type'] === 'task') {
                        foreach ($step['edit_tasks'] as $taskId) {
                            if (!is_null($taskId)) {
                                ProcedureTask::create([
                                    'procedure_id' => $workflowStep->id,
                                    'task_id' => $taskId,
                                ]);
                            }
                        }
                    }
                    foreach ($previousStepIds as $id) {
                        WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
                    }
                    $previousStepIds = [];
                    $previousStepIds[] = $workflowStep->id;
                    if (!(empty($step['edit_modal_escalates_to_steps']) || empty($step['edit_modal_escalates_after_steps']))) {
                        foreach ($step['edit_modal_escalates_to_steps'] as $key => $escalationDept) {
                            ProcedureEscalation::create([
                                'procedure_id' => $workflowStep->id,
                                'escalates_to' => $escalationDept,
                                'escalates_after' => $step['edit_modal_escalates_after_steps'][$key] ?? null,
                            ]);
                        }
                    }
                    $index++;
                }
            }



            \DB::commit();

            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            \DB::rollBack();
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' =>  $e->getMessage()];
        }

        return redirect()->route('employeesProcedures')->with(['status' => $output]);
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {


        try {
            $type = WkProcedure::where('id', $id)->first()->request_type_id;
            $requests = UserRequest::where('request_type_id', $type)->get();
            if ($requests->count() != 0) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.cant_delete_procedure_it_have_requests'),
                ];
                return $output;
            }
            error_log($type);

            $wk_pocedures =  WkProcedure::where('request_type_id', $type)->get();
            foreach ($wk_pocedures  as $wk_pocedure) {
                error_log($wk_pocedure->id);
                ProcedureTask::where('procedure_id',  $wk_pocedure->id)->delete();
                ProcedureEscalation::where('procedure_id',  $wk_pocedure->id)->delete();
                $wk_pocedure->delete();
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
