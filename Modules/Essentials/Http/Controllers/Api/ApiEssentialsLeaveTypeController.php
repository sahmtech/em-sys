<?php

namespace Modules\Essentials\Http\Controllers\Api;

use App\Utils\ModuleUtil;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\ToDo;

class ApiEssentialsLeaveTypeController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
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
     * @return Response
     */
    public function getLeaveTypes()
    {


        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
           //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;


            $leave_types = EssentialsLeaveType::where('business_id', $business_id)
                ->select(['id', 'leave_type', 'duration', 'max_leave_count',])->get();



            $res = [
                'leave_types' => $leave_types
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }


    public function getMyToDo()
    {

        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
           //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $priority_filter = request()->priority;

            $data = ToDo::where('business_id', $business_id)
                ->with(['assigned_by'])
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->select('*');

            if ($priority_filter) {
                $data = $data->where('priority', $priority_filter);
            }
            $data = $data->get();
            $todos = [];
            foreach ($data as $todo) {
                $todos[] = [
                    'id' => $todo->id,
                    'business_id' => $todo->business_id,
                    'task' => $todo->task,
                    'date' => $todo->date,
                    'end_date' => $todo->end_date,
                    'task_id' => $todo->task_id,
                    'description' => $todo->description,
                    'status' => $todo->status,
                    'estimated_hours' => $todo->estimated_hours,
                    'priority' => $todo->priority,
                    'assigned_by' => $todo->assigned_by->first_name . ' ' . $todo->assigned_by->last_name,
                ];
            }


            $res = [
                'todos' => collect($todos),
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
