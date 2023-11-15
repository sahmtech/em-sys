<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsWkProcedure;

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

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
      
        $departments=EssentialsDepartment::all()->pluck('name','id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);


        if (request()->ajax()) {	
   
            $procedures = EssentialsWkProcedure::groupBy('type')->with('department')->get();

        return DataTables::of($procedures)
        ->addColumn('steps', function ($procedure) {
            $steps = [];
            $steps1=EssentialsWkProcedure::where('type',$procedure->type)->with('department')->get();
            foreach ($steps1 as $step) {
                
                $departmentName = $step->department->name;
    
                $arrow = $departmentName ? ' ⬅ ' : '';
            
                $steps[] = "{$departmentName}";
                $steps[]="{$arrow}";
                
            }
            array_pop($steps);
            return implode($steps);
        })
        ->addColumn(
            'action',
             function ($row) {
                $html = '';
       
               $html .= '<button class="btn btn-xs btn-danger delete_procedure_button" data-href="'. route('procedure.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                
                return $html;
             }
            )
        ->rawColumns(['steps','action'])
        ->make(true);
        }
         return view('essentials::work_flow.index')->with(compact('departments'));
       
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
       
        ]);

        $type = $request->input('type');
        $steps = $request->input('steps');


        \DB::beginTransaction();

        try {
            $previousStep = null;

            foreach ($steps as $index => $step) {
                $workflowStep = [
                    'type' => $type,
                    'department_id' => $step['department_id'],
                    'next_department_id' => null,
                    'start' => $previousStep === null ? 1 : null,
                    'end' => $index === count($steps) - 1 ? 1 : null,
                    'can_reject' =>$step['can_reject'],
                    'can_return' => $step['can_return'],
                    // 'can_reject' => isset($step['can_reject']) ? 1 : 0,
                    // 'can_return' => isset($step['can_return']) ? 1 : 0,
             
                   
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

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
