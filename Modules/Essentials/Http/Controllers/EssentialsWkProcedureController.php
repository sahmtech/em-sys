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
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;

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
        

        $actualTypes = EssentialsWkProcedure::distinct()->pluck('type')->toArray();
        $missingTypes = array_diff($requestsType, $actualTypes);
        $departments=EssentialsDepartment::where('business_id',$business_id)->pluck('name','id');
       
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_delete_procedures = auth()->user()->can('essentials.delete_procedures');
        $can_show_steps= auth()->user()->can('essentials.essentials_show_steps');

        if (request()->ajax()) {	
   
        $procedures = EssentialsWkProcedure::where('business_id',$business_id)->groupBy('type')->with('department')->get();

        return DataTables::of($procedures)
        ->addColumn('steps', function ($procedure)   use ($is_admin ,$can_show_steps) {
            $steps = [];
            if($can_show_steps || $is_admin )
            {
                $steps1=EssentialsWkProcedure::where('type',$procedure->type)->with('department')->get();
                foreach ($steps1 as $step) {
                    
                    $departmentName = $step->department->name;
        
                    $arrow = $departmentName ? ' ⬅ ' : '';
                
                    $steps[] = "{$departmentName}";
                    $steps[]="{$arrow}";
                    
                }
                array_pop($steps);
            }
           
            return implode($steps);
        })
        ->addColumn(
            'action',
             function ($row)  use ($is_admin , $can_delete_procedures) {
            if ($is_admin || $can_delete_procedures ) {
               $html = '';
       
               $html .= '<button class="btn btn-xs btn-danger delete_procedure_button" data-href="'. route('procedure.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                
                return $html;
            }
             }
            )
        ->rawColumns(['steps','action'])
        ->make(true);
        }
        $businesses = Business::forDropdown();
         return view('essentials::work_flow.index')
         ->with(compact('departments','businesses','missingTypes'));
       
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
       
       $request->validate([
            'type' => 'required|string',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:essentials_departments,id',
            'steps.*.can_reject' => 'nullable|boolean',
            'steps.*.can_return' => 'nullable|boolean',
            'steps.*.escalates_to' => 'nullable|exists:essentials_departments,id',
            'escalates_after' => 'nullable|number',
            
       
        ]);

        $type = $request->input('type');
        $steps = $request->input('steps');


        \DB::beginTransaction();

        try {
            $previousStep = null;

            foreach ($steps as $index => $step) {
                $business_id=EssentialsDepartment::where('id',$step['department_id'])->first()->business_id;
                $workflowStep = [
                    'type' => $type,
                    'department_id' => $step['department_id'],
                    'business_id' => $business_id,
                    'escalates_to' => $step['escalates_to'] ?? null,
                    'escalates_after' => $step['escalates_after'] ?? null,
                    'next_department_id' => null,
                    'start' => $previousStep === null ? 1 : null,
                    'end' => $index === count($steps) - 1 ? 1 : null,
                    'can_reject' =>$step['can_reject'],
                    'can_return' => $step['can_return'],
       
                ];

                if ($previousStep !== null) {
                    $previousStep['next_department_id'] = $workflowStep['department_id'];
                    $previousStep->save();
                }

                $workflowStep = EssentialsWkProcedure::create($workflowStep);

                $previousStep = $workflowStep;
            }

    
            \DB::commit();

            $output = ['success' => true,
            'msg' => __('lang_v1.added_success'),
        ];
        } catch (\Exception $e) {
          
            \DB::rollBack();

            $output = ['success' => false,
                    'msg' => 'File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage(),
                ];
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
            $type=EssentialsWkProcedure::where('id', $id)->first()->type;
            
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
