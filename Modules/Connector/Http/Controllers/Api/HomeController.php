<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Business;
use App\Notification;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
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
class HomeController extends ApiController
{

    /**
     * All Utils instance.
     */
    protected $moduleUtil;
    protected $commonUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
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

    // public function getNotifications()
    // {
    //     $user = Auth::user();
    //     $notifications = $user->notifications()->where('is_deleted', 0)->orderBy('created_at', 'DESC')->get();

    //     $notifications_data = $this->commonUtil->parseNotifications($notifications);
    //     // return User::where('id',$user->id)->first()->allNotifications;
    //     return new CommonResource($notifications_data);
    // }

    public function removeNotification($id)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $user = User::where('id', $user->id)->first();
            Notification::where('id', $id)->update(['is_deleted' => 1]);
            return new CommonResource(['msg' => 'تم حذف الاشعار بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function readAllNotifications()
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $user = User::where('id', $user->id)->first();
            Notification::where('notifiable_id', $user->id)->update(['read_at' => Carbon::now()]);
            return new CommonResource(['msg' => 'تم قراءة الاشعارات بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function logout()
    {
        try {
            Auth::user()->tokens()->delete();
            return new CommonResource(['msg' => 'تم تسجيل الخروج بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }


    public function terms_privacy()
    {
        try {
            $res = [
                'terms_of_use' => 'terms and conditions of use place holder',
                'privacy_policy' => 'privacy policy plave holder',
                'support_phone' => '999999999999',
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }
}
