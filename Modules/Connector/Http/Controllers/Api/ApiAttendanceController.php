<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Business;
use App\BusinessLocationPolygonMarker;
use App\Notification;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsAttendance;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsUserShift;
use Modules\Essentials\Entities\ToDo;
use Modules\FollowUp\Entities\FollowupWorkerRequest;

/**
 * @group Taxonomy management
 * @authenticated
 *
 * APIs for managing taxonomies
 */
class ApiAttendanceController extends ApiController
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


    public function checkPointInPolygon(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->first();
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $location_id = $user->location_id;
        $isInsidePolygon = false;


        // Get the polygon markers from the database for the specified location
        $polygonMarkers = BusinessLocationPolygonMarker::where('business_location_id', $location_id)->get();

        // Create an array to store the polygon coordinates as strings
        $polygon = [];
        foreach ($polygonMarkers as $marker) {

            $polygon[] = $marker->lat . ' ' . $marker->lng;
        }

        // Transform string coordinates into arrays with x and y values
        $point = ['x' => $lat, 'y' => $lng];
        $vertices = array_map(function ($vertex) {
            $coordinates = explode(" ", $vertex);
            return ['x' => $coordinates[0], 'y' => $coordinates[1]];
        }, $polygon);

        // Check if the point sits exactly on a vertex
        if (in_array($point, $vertices, true)) {
            $isInsidePolygon = true;
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            $vertex2 = $vertices[$i];

            if ($vertex1['y'] == $vertex2['y'] && $vertex1['y'] == $point['y'] && $point['x'] > min($vertex1['x'], $vertex2['x']) && $point['x'] < max($vertex1['x'], $vertex2['x'])) {
                // Check if point is on a horizontal polygon boundary
                $isInsidePolygon = true;
            }

            if (
                $point['y'] > min($vertex1['y'], $vertex2['y']) &&
                $point['y'] <= max($vertex1['y'], $vertex2['y']) &&
                $point['x'] <= max($vertex1['x'], $vertex2['x']) &&
                $vertex1['y'] != $vertex2['y'] &&
                ($vertex1['x'] == $vertex2['x'] || $point['x'] <= ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'])
            ) {

                $intersections++;
            }
        }

        // If the number of edges we passed through is odd, then it's in the polygon.
        if ($intersections % 2 != 0 ||    $isInsidePolygon) {
            $isInsidePolygon = true;
        } else {
            $isInsidePolygon = false;
            error_log($intersections);
        }
        return    $isInsidePolygon;

        // if ($isInsidePolygon) {
        //     return response()->json(['status' => 'success', 'message' => 'Point is inside the polygon']);
        // } else {
        //     return response()->json(['status' => 'error', 'message' => 'Point is outside the polygon']);
        // }
    }
    public function clock_in(){
        
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function home()
    {


        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $business = Business::where('id', $business_id)->first();
            $shift = EssentialsUserShift::where('user_id', $user->id)->first()->shift;
            $lastRequest = FollowupWorkerRequest::select([
                'followup_worker_requests.request_no',
                'followup_worker_requests.id',
                'followup_worker_requests.type as type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.created_at',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.status_note as note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.department_id as department_id',
                'users.id_proof_number',


            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->where('users.id', $user->id)->latest('followup_worker_requests.created_at')

                ->first();


            $todo = ToDo::where('business_id', $business_id)
                ->with(['assigned_by'])
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })

                ->select('*')->latest('created_at')
                ->first();

            $lastTask = null;
            if ($todo) {
                $lastTask = [
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


            $user = User::where('id', $user->id)->first();
            $fullName = $user->first_name . ' ' . $user->last_name;
            $image =  $user->profile_image ? 'uploads/' . $user->profile_image : null;
            $essentialsEmployeeAppointmet = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->first();

            $professions = EssentialsProfession::all()->pluck('name', 'id')->toArray();
            $work = null;
            if ($essentialsEmployeeAppointmet) {
                $work = $professions[$essentialsEmployeeAppointmet->profession_id];
            }


            $attendanceList = EssentialsAttendance::where('user_id', $user->id)->whereDate('clock_in_time', Carbon::now()->toDateString())->first();
            $signed_in = $attendanceList ? true : false;
            $signed_out = $signed_in ? ($attendanceList->clock_out_time ? true : false) : false;


            $res = [
                'new_notifications' => 0,
                'work_day_start' => Carbon::parse($shift->start_time)->format('h:i A'),
                'work_day_end' => Carbon::parse($shift->end_time)->format('h:i A'),
                'business_name' => $business->name,
                'request' => $lastRequest,
                'task' => $lastTask,
                'full_name' => $fullName,
                'image' => $image,
                'work' => $work,
                'signed_in' => $signed_in,
                'signed_out' => $signed_out,
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
