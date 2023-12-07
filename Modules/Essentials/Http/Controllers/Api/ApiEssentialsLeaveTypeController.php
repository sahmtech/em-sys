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
            abort(403, 'Unauthorized action.');
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
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;


            $todos = ToDo::where('business_id', $business_id)
                ->with(['assigned_by' => function ($query) {
                    $query->select('assigned_by.id', 'assigned_by.first_name', 'assigned_by.last_name'); // Specify the necessary columns
                }])
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->select([
                    'todos.id',
                    'todos.business_id',
                    'todos.task',
                    'todos.date',
                    'todos.end_date',
                    'todos.task_id',
                    'todos.description',
                    'todos.status',
                    'todos.estimated_hours',
                    'todos.priority',
                    DB::raw("CONCAT(COALESCE(assigned_by.first_name, ''),' ',COALESCE(assigned_by.last_name,'')) as assigned_by"),

                ])->get();


            $res = [
                'todos' => $todos
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
