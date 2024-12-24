<?php

namespace Modules\CEOManagment\Http\Controllers;

use App\Business;
use App\Contact;
use App\Request as UserRequest;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CEOManagment\Entities\ProcedureEscalation;
use Modules\CEOManagment\Entities\ProcedureTask;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\Task;
use Modules\CEOManagment\Entities\TimeSheetWorkflow;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\Essentials\Entities\EssentialsDepartment;
use Yajra\DataTables\Facades\DataTables;

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
        //  $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delete_procedures = auth()->user()->can('generalmanagement.delete_procedure');
        $can_edit_procedures = auth()->user()->can('generalmanagement.edit_procedure');
        $business = Business::pluck('name', 'id');

        $departments = EssentialsDepartment::pluck('name', 'id');

        $escalates_departments = EssentialsDepartment::where(function ($query) {
            $query->Where('name', 'like', '%مجلس%')
                ->orWhere('name', 'like', '%عليا%')
                ->orWhere('name', 'like', '%عام%')
                ->orWhere('name', 'like', '%تنفيذ%');
        })
            ->pluck('name', 'id')->unique()->toArray();

        $requestsType = RequestsType::where('for', 'employee')->pluck('type', 'id');
        $actualTypes = WkProcedure::distinct()->where('request_owner_type', 'employee')->pluck('request_type_id')->toArray();

        $missingTypes = array_diff_key($requestsType->toArray(), array_flip($actualTypes));

        $procedures = WkProcedure::where('request_owner_type', 'employee')
            ->select('business_id', 'request_type_id', 'id')
            ->groupBy('business_id', 'request_type_id')
            ->with('department');

        if (request()->ajax()) {

            return DataTables::of($procedures)
                ->editColumn('request_type_id', function ($row) use ($requestsType) {
                    $item = $requestsType[$row->request_type_id] ?? '';

                    return $item;
                })
                ->editColumn('business_id', function ($row) use ($business) {
                    $item = $business[$row->business_id] ?? '';

                    return $item;
                })
                ->addColumn('steps', function ($procedure) {
                    try {
                        $stepsData = WkProcedure::where('request_type_id', $procedure->request_type_id)
                            ->where('business_id', $procedure->business_id)
                            ->with(['department', 'nextDepartment'])
                            ->where('start', 1)
                            ->orderBy('start', 'desc')
                            ->get();
                        $stepsFormatted = [];

                        foreach ($stepsData as $step) {
                            $sequence = [];
                            $end = false;
                            $loopStep = $step;
                            $visitedDepartments = [];

                            while (!$end) {
                                $departmentName = $loopStep->department->name;
                                $sequence[] = $departmentName;

                                // Check if this step has already been visited
                                if (isset($visitedDepartments[$loopStep->department_id])) {
                                    // If already visited, break the loop to avoid infinite loop
                                    break;
                                } else {
                                    // Mark the department as visited
                                    $visitedDepartments[$loopStep->department_id] = true;
                                }

                                $end = $loopStep->end;

                                if (!$end) {
                                    $loopStep = WkProcedure::where('department_id', $loopStep->next_department_id)
                                        ->where('business_id', $loopStep->business_id)
                                        ->where('request_type_id', $loopStep->request_type_id)
                                        ->with(['department', 'nextDepartment'])
                                        ->first();

                                    // If no next step found, break the loop
                                    if (!$loopStep) {
                                        break;
                                    }
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
        return view('ceomanagment::work_flow.employees')->with(compact('departments', 'business', 'tasks', 'escalates_departments', 'missingTypes'));
    }

    public function workersProcedures()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delete_procedures = auth()->user()->can('generalmanagement.delete_procedure');
        $can_edit_procedures = auth()->user()->can('generalmanagement.edit_procedure');
        $business = Business::pluck('name', 'id');

        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');

        $escalates_departments = EssentialsDepartment::where(function ($query) {
            $query->Where('name', 'like', '%مجلس%')
                ->orWhere('name', 'like', '%عليا%')
                ->orWhere('name', 'like', '%عام%')
                ->orWhere('name', 'like', '%تنفيذ%');
        })
            ->pluck('name', 'id')->unique()->toArray();
        $requestsType = RequestsType::where('for', 'worker')->pluck('type', 'id');
        $actualTypes = WkProcedure::distinct()->where('request_owner_type', 'worker')->pluck('request_type_id')->toArray();

        $missingTypes = array_diff_key($requestsType->toArray(), array_flip($actualTypes));

        $procedures = WkProcedure::where('request_owner_type', 'worker')
            ->select('business_id', 'request_type_id', 'id')
            ->groupBy('business_id', 'request_type_id')
            ->with('department');

        if (request()->ajax()) {
            return DataTables::of($procedures)
                ->editColumn('request_type_id', function ($row) use ($requestsType) {
                    $item = $requestsType[$row->request_type_id] ?? '';
                    return $item;
                })->editColumn('business_id', function ($row) use ($business) {
                $item = $business[$row->business_id] ?? '';
                return $item;
            })
                ->addColumn('steps', function ($procedure) {
                    try {
                        $stepsData = WkProcedure::where('request_type_id', $procedure->request_type_id)
                            ->where('business_id', $procedure->business_id)
                            ->with(['department', 'nextDepartment'])
                            ->where('start', 1)
                            ->orderBy('start', 'desc')
                            ->get();
                        $stepsFormatted = [];

                        foreach ($stepsData as $step) {
                            $sequence = [];
                            $end = false;
                            $loopStep = $step;
                            $visitedDepartments = [];

                            while (!$end) {
                                $departmentName = $loopStep->department->name;

                                // Check if this department has been visited before
                                if (isset($visitedDepartments[$loopStep->department_id])) {
                                    // Skip to the next occurrence of this department
                                    $loopStep = WkProcedure::where('department_id', $loopStep->department_id)
                                        ->where('business_id', $loopStep->business_id)
                                        ->where('request_type_id', $loopStep->request_type_id)
                                        ->where('id', '>', $visitedDepartments[$loopStep->department_id])
                                        ->with(['department', 'nextDepartment'])
                                        ->first();

                                    // If no next occurrence found, break the loop
                                    if (!$loopStep) {
                                        break;
                                    }
                                }

                                $sequence[] = $departmentName;
                                $visitedDepartments[$loopStep->department_id] = $loopStep->id; // Update the last occurrence of this department

                                $end = $loopStep->end;

                                if (!$end) {
                                    $loopStep = WkProcedure::where('department_id', $loopStep->next_department_id)
                                        ->where('business_id', $loopStep->business_id)
                                        ->where('request_type_id', $loopStep->request_type_id)
                                        ->with(['department', 'nextDepartment'])
                                        ->first();

                                    // If no next step found, break the loop
                                    if (!$loopStep) {
                                        break;
                                    }
                                }
                            }

                            $sequenceString = implode(' -> ', $sequence);
                            $stepsFormatted[] = "<li>{$sequenceString}</li>";
                        }
                        return '<ul>' . implode('', $stepsFormatted) . '</ul>';
                    } catch (\Exception $e) {
                        return '';
                    }
                })
                ->addColumn('action', function ($row) use ($is_admin, $can_delete_procedures, $can_edit_procedures) {
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
        return view('ceomanagment::work_flow.workers')->with(compact('departments', 'business', 'tasks', 'escalates_departments', 'missingTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function getProcedure($procedure_id)
    {
        $procedureType = WkProcedure::where('id', $procedure_id)->first()->request_type_id;
        $procedureBusiness = WkProcedure::where('id', $procedure_id)->first()->business_id;

        $start_from_customer = RequestsType::where('id', $procedureType)->first()->start_from_customer;
        $procedures = WkProcedure::with('department')->where('request_type_id', $procedureType)->where('business_id', $procedureBusiness)->get();
        $superior_dep = RequestsType::where('id', $procedureType)->first()->goes_to_superior;
        $escalates_departments = EssentialsDepartment::where(function ($query) {
            $query->Where('name', 'like', '%مجلس%')
                ->orWhere('name', 'like', '%عليا%')
                ->orWhere('name', 'like', '%عام%')
                ->orWhere('name', 'like', '%تنفيذ%');
        })->pluck('name', 'id')->toArray();

        foreach ($procedures as $procedure) {
            $escalations = ProcedureEscalation::where('procedure_id', $procedure->id)->get();
            $tasks = ProcedureTask::where('procedure_id', $procedure->id)->get();

            $taskIds = $tasks->pluck('task_id');
            $taskDescriptions = Task::whereIn('id', $taskIds)->get(['id', 'description']);

            $procedure->escalations = $escalations;
            $procedure->tasks = $taskDescriptions;
        }

        // Return response with the request_type_id
        return response()->json([
            'procedures' => $procedures,
            'superior_dep' => $superior_dep,
            'start_from_customer' => $start_from_customer,
            'escalates_departments' => $escalates_departments,
            'request_type_id' => $procedureType, // Include the request_type_id in the response
        ]);
    }

    public function fetchWorkerRequestTypesByBusiness(Request $request)
    {
        $businessId = $request->get('business_id');

        $excludedRequestTypeIds = WKProcedure::where('business_id', $businessId)
            ->pluck('request_type_id');

        $requestTypes = RequestsType::whereNotIn('id', $excludedRequestTypeIds)->where('for', 'worker')->get();

        $formattedTypes = $requestTypes->mapWithKeys(fn($type) => [$type->id => trans("ceomanagment::lang.{$type->type}")]);

        return response()->json(['types' => $formattedTypes]);
    }
    public function fetchEmployeeRequestTypesByBusiness(Request $request)
    {
        $businessId = $request->get('business_id');

        $excludedRequestTypeIds = WKProcedure::where('business_id', $businessId)
            ->pluck('request_type_id');

        $requestTypes = RequestsType::whereNotIn('id', $excludedRequestTypeIds)->where('for', 'employee')->get();

        $formattedTypes = $requestTypes->mapWithKeys(fn($type) => [$type->id => trans("ceomanagment::lang.{$type->type}")]);

        return response()->json(['types' => $formattedTypes]);
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function storeWorkerProcedure(Request $request)
    {
        //return $request->all();

        $type = $request->input('type');
        $steps = $request->input('step');
        $business_id = $request->input('business');

        \DB::beginTransaction();
        try {

            $check_repeated = [];
            foreach ($steps as $index => $step) {
                $check_repeated[] = $step['add_modal_department_id_steps'][0];
            }
            // if (count($check_repeated) !== count(array_unique($check_repeated))) {
            //     throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
            // }
            RequestsType::where('id', $type)->update(['start_from_customer' => $request->start_from_customer, 'customer_department' => $steps[0]['add_modal_department_id_steps'][0]]);

            $previousStepIds = [];
            foreach ($steps as $index => $step) {
                $start_dep = $step['add_modal_department_id_steps'][0];
                //   $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
                $workflowStep = WkProcedure::create([
                    'request_type_id' => $type,
                    'request_owner_type' => 'worker',
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
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return redirect()->route('workersProcedures')->with(['status' => $output]);
    }

    public function storeEmployeeProcedure(Request $request)
    {

        $type = $request->input('type');
        $steps = $request->input('step');
        $business_id = $request->input('business');

        \DB::beginTransaction();
        try {

            $check_repeated = [];
            foreach ($steps as $index => $step) {
                $check_repeated[] = $step['add_modal_department_id_steps'][0];
            }
            // if (count($check_repeated) !== count(array_unique($check_repeated))) {
            //     throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
            // }
            RequestsType::where('id', $type)->update(['goes_to_superior' => $request->superior_department]);

            $previousStepIds = [];
            foreach ($steps as $index => $step) {
                $start_dep = $step['add_modal_department_id_steps'][0];
                //   $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
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
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return redirect()->route('employeesProcedures')->with(['status' => $output]);
    }

    public function update(Request $request, $id)
    {

        \DB::beginTransaction();
        try {
            $steps = $request->input('step');
            $procedureId = $steps[0]['procedure_id'];
            if ($procedureId) {
                $type = WKprocedure::where('id', $procedureId)->first()->request_type_id;
                $requests = UserRequest::where('request_type_id', $type)->where('status', 'pending')->get();
                if ($requests->count() != 0) {
                    $output = [
                        'success' => false,
                        'msg' => __('ceomanagment::lang.cant_edit_procedure_it_have_pending_requests'),
                    ];
                    return redirect()->back()->with(['status' => $output]);
                }
            }

            $business_id = $request->input('business');
            RequestsType::where('id', $type)->update(['start_from_customer' => $request->start_from_customer, 'customer_department' => $steps[0]['edit_modal_department_id_steps'][0]]);
            $previousStepIds = [];
            foreach ($steps as $index => $step) {
                $procedureId = $step['procedure_id'] ?? null;
                $start_dep = $step['edit_modal_department_id_steps'][0];

                if ($procedureId) {
                    // Update the existing procedure
                    $workflowStep = WkProcedure::find($procedureId);
                    if ($workflowStep) {
                        $workflowStep->update([
                            'department_id' => $start_dep,
                            'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
                            'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
                            'action_type' => $step['edit_action_type'] ?? null,
                        ]);

                        // Handle tasks: update, delete if removed, and create new
                        $existingTaskIds = ProcedureTask::where('procedure_id', $workflowStep->id)->pluck('task_id')->toArray();
                        $newTaskIds = array_filter($step['edit_tasks'] ?? []);

                        // Delete tasks that are no longer in the list
                        $tasksToDelete = array_diff($existingTaskIds, $newTaskIds);
                        if (!empty($tasksToDelete)) {
                            ProcedureTask::where('procedure_id', $workflowStep->id)->whereIn('task_id', $tasksToDelete)->delete();
                        }

                        // Add new tasks
                        foreach ($newTaskIds as $taskId) {
                            if (!in_array($taskId, $existingTaskIds)) {
                                ProcedureTask::create([
                                    'procedure_id' => $workflowStep->id,
                                    'task_id' => $taskId,
                                ]);
                            }
                        }

                        // Handle escalations: update, delete if removed, and create new
                        $existingEscalations = ProcedureEscalation::where('procedure_id', $workflowStep->id)->get()->keyBy('escalates_to');
                        error_log($workflowStep->id);
                        error_log(json_encode($existingEscalations->toArray()));

                        $newEscalations = $step['edit_modal_escalates_to_steps'] ?? [];
                        $newEscalatesAfter = $step['edit_modal_escalates_after_steps'] ?? [];

                        // Ensure the new escalations and escalates_after arrays have the same length
                        if (count($newEscalations) === count($newEscalatesAfter) && count($newEscalations) > 0) {
                            $newEscalationData = array_combine($newEscalations, $newEscalatesAfter);
                        } else {
                            $newEscalationData = [];
                        }

                        error_log(json_encode($newEscalationData));

                        // Delete escalations that are no longer in the list
                        $escalationsToDelete = array_diff($existingEscalations->keys()->toArray(), $newEscalations);
                        if (!empty($escalationsToDelete)) {
                            ProcedureEscalation::where('procedure_id', $workflowStep->id)->whereIn('escalates_to', $escalationsToDelete)->delete();
                        }

                        // Update or create escalations
                        foreach ($newEscalationData as $escalatesTo => $escalatesAfter) {
                            if (!is_null($escalatesTo) && !is_null($escalatesAfter)) {
                                ProcedureEscalation::updateOrCreate(
                                    ['procedure_id' => $workflowStep->id, 'escalates_to' => $escalatesTo],
                                    ['escalates_after' => $escalatesAfter]
                                );
                            }
                        }
                    }
                } else {
                    // Create a new procedure if procedureId does not exist
                    $workflowStep = WkProcedure::create([
                        'request_type_id' => $type,
                        'request_owner_type' => 'worker',
                        'department_id' => $start_dep,
                        'business_id' => $business_id,
                        'next_department_id' => null,
                        'start' => $index === 0 ? 1 : 0,
                        'end' => $index === count($steps) - 1 ? 1 : 0,
                        'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
                        'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
                        'action_type' => $step['edit_action_type'] ?? null,
                    ]);

                    // Create tasks for the new procedure
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

                    // Create escalations for the new procedure
                    if (isset($step['edit_modal_escalates_to_steps']) && isset($step['edit_modal_escalates_after_steps'])) {
                        foreach ($step['edit_modal_escalates_to_steps'] as $key => $escalationDept) {
                            if (!is_null($escalationDept)) {
                                ProcedureEscalation::create([
                                    'procedure_id' => $workflowStep->id,
                                    'escalates_to' => $escalationDept,
                                    'escalates_after' => $step['edit_modal_escalates_after_steps'][$key] ?? null,
                                ]);
                            }
                        }
                    }
                }

                // Update the next_department_id for the previous step
                foreach ($previousStepIds as $id) {
                    WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
                }
                $previousStepIds = [$workflowStep->id];
            }

            \DB::commit();

            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            \DB::rollBack();
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return redirect()->back()->with(['status' => $output]);
    }
    public function updateEmployeeProcedure(Request $request)
    {
        \DB::beginTransaction();
        try {
            $steps = $request->input('step');
            $procedureId = $steps[0]['procedure_id'];
            $business_id = $request->input('business') ?? session()->get('user.business_id');

            if ($procedureId) {
                $type = WKprocedure::where('id', $procedureId)->first()->request_type_id;
                $requests = UserRequest::where('request_type_id', $type)->where('status', 'pending')->get();
                if ($requests->count() != 0) {
                    $output = [
                        'success' => false,
                        'msg' => __('ceomanagment::lang.cant_edit_procedure_it_have_pending_requests'),
                    ];
                    return redirect()->back()->with(['status' => $output]);
                }
            }

            RequestsType::where('id', $type)->update(['goes_to_superior' => $request->superior_department]);

            $previousStepIds = [];
            foreach ($steps as $index => $step) {
                $procedureId = $step['procedure_id'] ?? null;
                $start_dep = $step['edit_modal_department_id_steps'][0];

                if ($procedureId) {
                    // Update the existing procedure
                    $workflowStep = WkProcedure::find($procedureId);
                    if ($workflowStep) {
                        $workflowStep->update([
                            'department_id' => $start_dep,
                            'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
                            'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
                            'action_type' => $step['edit_action_type'] ?? null,
                        ]);

                        // Handle tasks: update, delete if removed, and create new
                        $existingTaskIds = ProcedureTask::where('procedure_id', $workflowStep->id)->pluck('task_id')->toArray();
                        $newTaskIds = array_filter($step['edit_tasks'] ?? []);

                        // Delete tasks that are no longer in the list
                        $tasksToDelete = array_diff($existingTaskIds, $newTaskIds);
                        if (!empty($tasksToDelete)) {
                            ProcedureTask::where('procedure_id', $workflowStep->id)->whereIn('task_id', $tasksToDelete)->delete();
                        }

                        // Add new tasks
                        foreach ($newTaskIds as $taskId) {
                            if (!in_array($taskId, $existingTaskIds)) {
                                ProcedureTask::create([
                                    'procedure_id' => $workflowStep->id,
                                    'task_id' => $taskId,
                                ]);
                            }
                        }

                        // Handle escalations: update, delete if removed, and create new
                        $existingEscalations = ProcedureEscalation::where('procedure_id', $workflowStep->id)->get()->keyBy('escalates_to');
                        error_log($workflowStep->id);
                        error_log(json_encode($existingEscalations->toArray()));

                        $newEscalations = $step['edit_modal_escalates_to_steps'] ?? [];
                        $newEscalatesAfter = $step['edit_modal_escalates_after_steps'] ?? [];

                        // Ensure the new escalations and escalates_after arrays have the same length
                        if (count($newEscalations) === count($newEscalatesAfter) && count($newEscalations) > 0) {
                            $newEscalationData = array_combine($newEscalations, $newEscalatesAfter);
                        } else {
                            $newEscalationData = [];
                        }

                        error_log(json_encode($newEscalationData));

                        // Delete escalations that are no longer in the list
                        $escalationsToDelete = array_diff($existingEscalations->keys()->toArray(), $newEscalations);
                        if (!empty($escalationsToDelete)) {
                            ProcedureEscalation::where('procedure_id', $workflowStep->id)->whereIn('escalates_to', $escalationsToDelete)->delete();
                        }

                        // Update or create escalations
                        foreach ($newEscalationData as $escalatesTo => $escalatesAfter) {
                            if (!is_null($escalatesTo) && !is_null($escalatesAfter)) {
                                ProcedureEscalation::updateOrCreate(
                                    ['procedure_id' => $workflowStep->id, 'escalates_to' => $escalatesTo],
                                    ['escalates_after' => $escalatesAfter]
                                );
                            }
                        }
                    }
                } else {
                    // Create a new procedure if procedureId does not exist
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

                    // Create tasks for the new procedure
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

                    // Create escalations for the new procedure
                    if (isset($step['edit_modal_escalates_to_steps']) && isset($step['edit_modal_escalates_after_steps'])) {
                        foreach ($step['edit_modal_escalates_to_steps'] as $key => $escalationDept) {
                            if (!is_null($escalationDept)) {
                                ProcedureEscalation::create([
                                    'procedure_id' => $workflowStep->id,
                                    'escalates_to' => $escalationDept,
                                    'escalates_after' => $step['edit_modal_escalates_after_steps'][$key] ?? null,
                                ]);
                            }
                        }
                    }
                }

                // Update the next_department_id for the previous step
                foreach ($previousStepIds as $id) {
                    WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
                }
                $previousStepIds = [$workflowStep->id];
            }

            \DB::commit();

            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->route('employeesProcedures')->with(['status' => $output]);

    }

    // public function updateEmployeeProcedure(Request $request)
    // {
    //     \DB::beginTransaction();
    //     try {
    //         $steps = $request->input('step');
    //         $type = $request->input('type');
    //         $business_id = $request->input('business');

    //         // Update the "goes_to_superior" field in RequestsType
    //         RequestsType::where('id', $type)->update(['goes_to_superior' => $request->superior_department]);

    //         $previousStepIds = [];
    //         foreach ($steps as $index => $step) {
    //             $procedureId = $step['procedure_id'] ?? null;
    //             $start_dep = $step['edit_modal_department_id_steps'][0];

    //             if ($procedureId) {
    //                 error_log($procedureId);
    //                 $workflowStep = WkProcedure::find($procedureId);
    //                 if ($workflowStep) {
    //                     $workflowStep->update([
    //                         'department_id' => $start_dep,
    //                         'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
    //                         'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
    //                         'action_type' => $step['edit_action_type'] ?? null,
    //                     ]);

    //                     // Handle tasks and escalations for the existing step
    //                     $this->handleTasksAndEscalations($workflowStep->id, $step);
    //                 }
    //             } else {
    //                 // Create a new procedure if procedureId does not exist
    //                 $workflowStep = WkProcedure::create([
    //                     'request_type_id' => $type,
    //                     'request_owner_type' => 'employee',
    //                     'department_id' => $start_dep,
    //                     'business_id' => $business_id,
    //                     'next_department_id' => null,
    //                     'start' => $index === 0 ? 1 : 0,
    //                     'end' => $index === count($steps) - 1 ? 1 : 0,
    //                     'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
    //                     'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
    //                     'action_type' => $step['edit_action_type'] ?? null,
    //                 ]);

    //                 // Handle tasks and escalations for the new step
    //                 $this->handleTasksAndEscalations($workflowStep->id, $step);
    //             }

    //             // Update the next_department_id for the previous step
    //             foreach ($previousStepIds as $id) {
    //                 WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
    //             }
    //             $previousStepIds = [$workflowStep->id];
    //         }

    //         \DB::commit();

    //         $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
    //     } catch (\Exception $e) {
    //         \DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
    //         $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
    //     }

    //     return redirect()->route('employeesProcedures')->with(['status' => $output]);
    // }

    // protected function handleTasksAndEscalations($procedureId, $step)
    // {
    //     // Handle tasks: update, delete if removed, and create new
    //     $existingTaskIds = ProcedureTask::where('procedure_id', $procedureId)->pluck('task_id')->toArray();
    //     $newTaskIds = array_filter($step['edit_tasks'] ?? []);

    //     // Delete tasks that are no longer in the list
    //     $tasksToDelete = array_diff($existingTaskIds, $newTaskIds);
    //     if (!empty($tasksToDelete)) {
    //         ProcedureTask::where('procedure_id', $procedureId)->whereIn('task_id', $tasksToDelete)->delete();
    //     }

    //     // Add new tasks
    //     foreach ($newTaskIds as $taskId) {
    //         if (!in_array($taskId, $existingTaskIds)) {
    //             ProcedureTask::create([
    //                 'procedure_id' => $procedureId,
    //                 'task_id' => $taskId,
    //             ]);
    //         }
    //     }

    //     // Handle escalations: update, delete if removed, and create new
    //     $existingEscalations = ProcedureEscalation::where('procedure_id', $procedureId)->get()->keyBy('escalates_to');
    //     $newEscalations = $step['edit_modal_escalates_to_steps'] ?? [];
    //     $newEscalatesAfter = $step['edit_modal_escalates_after_steps'] ?? [];

    //     if (count($newEscalations) === count($newEscalatesAfter) && count($newEscalations) > 0) {
    //         $newEscalationData = array_combine($newEscalations, $newEscalatesAfter);
    //     } else {
    //         $newEscalationData = [];
    //     }

    //     // Delete escalations that are no longer in the list
    //     $escalationsToDelete = array_diff($existingEscalations->keys()->toArray(), $newEscalations);
    //     if (!empty($escalationsToDelete)) {
    //         ProcedureEscalation::where('procedure_id', $procedureId)->whereIn('escalates_to', $escalationsToDelete)->delete();
    //     }

    //     // Update or create escalations
    //     foreach ($newEscalationData as $escalatesTo => $escalatesAfter) {
    //         if (!is_null($escalatesTo) && !is_null($escalatesAfter)) {
    //             ProcedureEscalation::updateOrCreate(
    //                 ['procedure_id' => $procedureId, 'escalates_to' => $escalatesTo],
    //                 ['escalates_after' => $escalatesAfter]
    //             );
    //         }
    //     }
    // }

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

            $wk_pocedures = WkProcedure::where('request_type_id', $type)->get();
            foreach ($wk_pocedures as $wk_pocedure) {
                error_log($wk_pocedure->id);
                ProcedureTask::where('procedure_id', $wk_pocedure->id)->delete();
                ProcedureEscalation::where('procedure_id', $wk_pocedure->id)->delete();
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

    public function timesheet_wk()
    {
        $businessWithWorkflows = TimeSheetWorkflow::distinct()->pluck('business_id')->toArray();
        $business = Business::whereNotIn('id', $businessWithWorkflows)->pluck('name', 'id');
        $all_business = Business::pluck('name', 'id');
        $workflows = TimeSheetWorkflow::all();
        $departments = EssentialsDepartment::pluck('name', 'id');
        $clients = Contact::all();

        return view('ceomanagment::work_flow.time_sheet', compact('workflows', 'all_business', 'business', 'departments', 'clients'));
    }
    public function getDepartmentsForWk($businessId)
    {

        $departments = EssentialsDepartment::where('business_id', $businessId)->pluck('name', 'id');
        return response()->json($departments);
    }

    public function storeTimeSheetProcedure(Request $request)
    {
        $data = $request->all();
        $businessId = $data['business'];

        foreach ($data['steps'] as $stepNumber => $step) {

            if (isset($step['departments'])) {
                $departments = array_filter($step['departments'], function ($departmentId) {
                    return !is_null($departmentId);
                });

                if (count($departments) > 0) {
                    foreach ($departments as $departmentId) {
                        TimeSheetWorkflow::create([
                            'department_id' => $departmentId,
                            'business_id' => $businessId,
                            'step_number' => $stepNumber,
                            // 'clients_allowed' => isset($step['clients_allowed']) ? $step['clients_allowed'] : 0,

                        ]);
                    }

                    if (isset($step['clients_allowed']) && $step['clients_allowed'] == 1) {
                        TimeSheetWorkflow::create([
                            'department_id' => null,
                            'business_id' => $businessId,
                            'step_number' => $stepNumber,
                            'clients_allowed' => 1,
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Workflow added successfully.');
    }
}
//public function storeEmployeeProcedure(Request $request)  // {
//     return $request->all();

//     $type = $request->input('type');
//     $steps = $request->input('step');
//     $business_id = $request->input('business');
//     \DB::beginTransaction();
//     try {

//         $add_modal_department_id_start = $request->add_modal_department_id_start;

//         $check_repeated = [];
//         foreach ($add_modal_department_id_start as $start_dep) {
//             $check_repeated[] = $start_dep;
//         }
//         foreach ($steps  as $index => $step) {
//             $check_repeated[] = $step['add_modal_department_id_steps'][0];
//         }
//         // if (count($check_repeated) !== count(array_unique($check_repeated))) {
//         //     throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
//         // }
//         if ($request->start_from_customer) {
//             $request_type = RequestsType::where('id', $type)->first();

//             $request_type->start_from_customer = 1;
//             //  $request_type->customer_department = $request->customer_department;
//             $request_type->customer_department = $add_modal_department_id_start[0];
//             $request_type->save();
//         }
//         $previousStepIds = [];
//         foreach ($add_modal_department_id_start as $start_dep) {
//             if ($start_dep) {
//                 //  $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
//                 $workflowStep = WkProcedure::create([
//                     'request_type_id' => $type,
//                     'request_owner_type' => 'worker',
//                     'department_id' => $start_dep,
//                     'business_id' => $business_id,
//                     'next_department_id' => null,
//                     'start' => 1,
//                     'end' => 0,
//                     'can_reject' => 1,
//                     'can_return' =>  null,
//                 ]);
//                 $previousStepIds[] = $workflowStep->id;
//             }
//         }

//         foreach ($steps  as $index => $step) {
//             $start_dep = $step['add_modal_department_id_steps'][0];
//             //  $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
//             $workflowStep = WkProcedure::create([
//                 'request_type_id' => $type,
//                 'request_owner_type' => 'worker',
//                 'department_id' => $start_dep,
//                 'business_id' => $business_id,
//                 'next_department_id' => null,
//                 'start' => 0,
//                 'end' => $index === count($steps) - 1 ? 1 : 0,
//                 'can_reject' => $step['add_modal_can_reject_steps'][0] ?? 0,
//                 'can_return' => $step['add_modal_can_return_steps'][0] ?? 0,
//                 'action_type' => $step['action_type'] ?? null,
//             ]);
//             if (isset($step['tasks']) && $step['action_type'] === 'task') {
//                 foreach ($step['tasks'] as $taskId) {
//                     if (!is_null($taskId)) {
//                         ProcedureTask::create([
//                             'procedure_id' => $workflowStep->id,
//                             'task_id' => $taskId,
//                         ]);
//                     }
//                 }
//             }
//             foreach ($previousStepIds as $id) {
//                 WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
//             }
//             $previousStepIds = [];
//             $previousStepIds[] = $workflowStep->id;
//             if (!(empty($step['add_modal_escalates_to_steps']) || empty($step['add_modal_escalates_after_steps']))) {
//                 foreach ($step['add_modal_escalates_to_steps'] as $key => $escalationDept) {
//                     ProcedureEscalation::create([
//                         'procedure_id' => $workflowStep->id,
//                         'escalates_to' => $escalationDept,
//                         'escalates_after' => $step['add_modal_escalates_after_steps'][$key] ?? null,
//                     ]);
//                 }
//             }
//         }

//         \DB::commit();

//         $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
//     } catch (\Exception $e) {
//         \DB::rollBack();
//         error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
//         $output = ['success' => false, 'msg' =>  $e->getMessage()];
//     }

//     return redirect()->route('workersProcedures')->with(['status' => $output]);
// }

// update
//return $request;
// try {
//     $type = WkProcedure::where('id', $id)->first()->request_type_id;
//     $requests = UserRequest::where('request_type_id', $type)->get();
//     if ($requests->count() != 0) {
//         $output = [
//             'success' => false,
//             'msg' => __('ceomanagment::lang.cant_edit_procedure_it_have_requests'),
//         ];
//         return redirect()->back()->with(['status' => $output]);
//     }
//     $procedureType = WkProcedure::where('id', $id)->first()->request_type_id;
//     $for = WkProcedure::where('id', $id)->first()->request_owner_type;

//     $type = $procedureType;

//     $steps = $request->input('step');

//     $edit_modal_department_id_start = $request->edit_modal_department_id_start;

//     $check_repeated = [];
//     if ($edit_modal_department_id_start) {
//         foreach ($edit_modal_department_id_start as $start_dep) {
//             $check_repeated[] = $start_dep;
//         }
//     }
//     if ($steps) {
//         foreach ($steps  as $index => $step) {
//             $check_repeated[] = $step['edit_modal_department_id_steps'][0];
//         }
//     }

//     if (count($check_repeated) !== count(array_unique($check_repeated))) {
//         throw new \Exception(__('ceomanagment::lang.repeated_managements_please_re_check'));
//     }

//     $procedures = WkProcedure::where('request_type_id', $procedureType)->get();
//     if ($procedures) {
//         foreach ($procedures as $procedure) {

//             ProcedureTask::where('procedure_id', $procedure->id)->delete();
//             ProcedureEscalation::where('procedure_id', $procedure->id)->delete();
//             $procedure->delete();
//         }
//     }
//     $previousStepIds = [];
//     if ($edit_modal_department_id_start) {
//         foreach ($edit_modal_department_id_start as $start_dep) {
//             if ($start_dep) {
//                 $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
//                 $workflowStep = WkProcedure::create([
//                     'request_type_id' => $type,
//                     'request_owner_type' => $for,
//                     'department_id' => $start_dep,
//                     'business_id' => $business_id,
//                     'next_department_id' => null,
//                     'start' => 1,
//                     'end' => 0,
//                     'can_reject' => null,
//                     'can_return' =>  null,
//                 ]);
//                 $previousStepIds[] = $workflowStep->id;
//             }
//         }
//     }

//     if ($steps) {
//         $index = 0;
//         foreach ($steps  as $step) {

//             error_log($index);
//             $start_dep = $step['edit_modal_department_id_steps'][0];
//             $business_id = EssentialsDepartment::where('id', $start_dep)->first()->business_id;
//             $workflowStep = WkProcedure::create([
//                 'request_type_id' => $type,
//                 'request_owner_type' => $for,
//                 'department_id' => $start_dep,
//                 'business_id' => $business_id,
//                 'next_department_id' => null,
//                 'start' => 0,
//                 'end' => $index === count($steps) - 1 ? 1 : 0,
//                 'can_reject' => $step['edit_modal_can_reject_steps'][0] ?? 0,
//                 'can_return' => $step['edit_modal_can_return_steps'][0] ?? 0,
//                 'action_type' => $step['edit_action_type'] ?? null,
//             ]);
//             if (isset($step['edit_tasks']) && $step['edit_action_type'] === 'task') {
//                 foreach ($step['edit_tasks'] as $taskId) {
//                     if (!is_null($taskId)) {
//                         ProcedureTask::create([
//                             'procedure_id' => $workflowStep->id,
//                             'task_id' => $taskId,
//                         ]);
//                     }
//                 }
//             }

//             foreach ($previousStepIds as $id) {
//                 WkProcedure::where('id', $id)->update(['next_department_id' => $start_dep]);
//             }
//             $previousStepIds = [];
//             $previousStepIds[] = $workflowStep->id;
//             if (!(empty($step['edit_modal_escalates_to_steps']) || empty($step['edit_modal_escalates_after_steps']))) {
//                 foreach ($step['edit_modal_escalates_to_steps'] as $key => $escalationDept) {
//                     ProcedureEscalation::create([
//                         'procedure_id' => $workflowStep->id,
//                         'escalates_to' => $escalationDept,
//                         'escalates_after' => $step['edit_modal_escalates_after_steps'][$key] ?? null,
//                     ]);
//                 }
//             }
//             $index++;
//         }
//     }

//     $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
// } catch (\Exception $e) {
//     error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
//     $output = ['success' => false, 'msg' =>  $e->getMessage()];
// }
