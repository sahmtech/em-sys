<?php

namespace Modules\Essentials\Http\Controllers;

use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsProceduresEscalation;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
class EssentialsWkProcedureController extends Controller
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
  
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delete_procedures = auth()->user()->can('essentials.delete_procedures');
    
        $requestsType = [
            'exitRequest',
            'returnRequest',
            'escapeRequest',
            'advanceSalary',
            'leavesAndDepartures',
            'atmCard',
            'residenceRenewal',
            'residenceCard',
            'workerTransfer',
            'workInjuriesRequest',
            'residenceEditRequest',
            'baladyCardRequest',
            'insuranceUpgradeRequest',
            'mofaRequest',
            'chamberRequest',
            'cancleContractRequest',
            'WarningRequest'

        ];

        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');

        $escalates_departments = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%تنفيذ%')
                    ->orWhere('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%');
            })
            ->pluck('name', 'id')->toArray();

        $actualTypes = EssentialsWkProcedure::distinct()->pluck('type')->toArray();
        $missingTypes = array_diff($requestsType, $actualTypes);
        if (request()->ajax()) {

            $procedures = EssentialsWkProcedure::where('business_id', $business_id)
                ->groupBy('type')
                ->with('department')
                ->get();

            return DataTables::of($procedures)
            ->addColumn('steps', function ($procedure) {
                try {
                    $stepsData = EssentialsWkProcedure::where('type', $procedure->type)
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
                                $loopStep = EssentialsWkProcedure::where('department_id', $loopStep->next_department_id)
                                    ->where('type', $loopStep->type)
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
            }) ->addColumn('action', function ($row) use ($is_admin, $can_delete_procedures) {
                    $html = '';
                    if ($is_admin || $can_delete_procedures) {
                        $html .= '<button class="btn btn-xs btn-danger delete_procedure_button" data-href="' . route('procedure.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }
                    return $html;
                })
                ->rawColumns(['steps', 'escalations', 'action'])
                ->make(true);
        }

        return view('essentials::work_flow.index')->with(compact('departments', 'escalates_departments', 'missingTypes'));
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
       
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(Request $request)
    {
        $type = $request->input('type');
        $steps = $request->input('steps');

        \DB::beginTransaction();
        try {
            $previousStepIds = [];
            foreach ($steps as $index => $step) {
                if ($index === 0 && is_array($step['department_id'])) {
                    foreach ($step['department_id'] as $departmentId) {
                        if ($departmentId) {
                            $business_id = EssentialsDepartment::where('id', $departmentId)->first()->business_id;
                            $workflowStep = EssentialsWkProcedure::create([
                                'type' => $type,
                                'department_id' => $departmentId,
                                'business_id' => $business_id,
                                'next_department_id' => null,
                                'start' => 1,
                                'end' => 0,
                                'can_reject' => $step['can_reject'] ?? null,
                                'can_return' => $step['can_return'] ?? null,
                            ]);
                            $previousStepIds[] = $workflowStep->id;
                        }
                    }
                } else {
                    $singleDepartmentId = is_array($step['department_id']) ? $step['department_id'][0] : $step['department_id'];
                    $business_id = EssentialsDepartment::where('id', $singleDepartmentId)->first()->business_id;
                    $workflowStep = EssentialsWkProcedure::create([
                        'type' => $type,
                        'department_id' => $singleDepartmentId,
                        'business_id' => $business_id,
                        'next_department_id' => null,
                        'start' => 0,
                        'end' => $index === count($steps) - 1 ? 1 : 0,
                        'can_reject' => $step['can_reject'] ?? 0,
                        'can_return' => $step['can_return'] ?? 0,
                    ]);


                    foreach ($previousStepIds as $id) {
                        EssentialsWkProcedure::where('id', $id)->update(['next_department_id' => $singleDepartmentId]);
                    }
                    $previousStepIds = [];

                    $previousStepIds[] = $workflowStep->id;
                    if (!empty($step['escalates_to'])) {
                        foreach ($step['escalates_to'] as $key => $escalationDept) {
                            EssentialsProceduresEscalation::create([
                                'procedure_id' => $workflowStep->id,
                                'escalates_to' => $escalationDept,
                                'escalates_after' => $step['escalates_after'][$key] ?? null,
                            ]);
                        }
                    }
                }
            }

            \DB::commit();

            $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            \DB::rollBack();
            $output = ['success' => false, 'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage()];
        }

        return redirect()->route('procedures')->with(['status' => $output]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $type = EssentialsWkProcedure::where('id', $id)->first()->type;
            $requests=FollowupWorkerRequest::where('type',$type)->get();
            if ($requests->count() != 0) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.cant_delete_procedure_it_have_requests'),
                ];
                return $output;
            }
            EssentialsWkProcedure::where('type', $type)->delete();

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
}
