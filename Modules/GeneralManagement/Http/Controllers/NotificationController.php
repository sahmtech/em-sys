<?php

namespace Modules\GeneralManagement\Http\Controllers;

use App\Notifications\GeneralNotification;
use App\SentNotification;
use App\SentNotificationsUser;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Exception;
use stdClass;

class NotificationController extends Controller
{

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $sent_notifications = SentNotification::with('sentNotificationsUser')->where('sender_id', auth()->user()->id);
        if (request()->ajax()) {
            return DataTables::of($sent_notifications)
                ->addColumn('title', function ($row) {
                    return $row->title;
                })
                ->addColumn('msg', function ($row) {
                    return $row->msg;
                })
                ->addColumn('to', function ($row) {
                    return json_decode($row->to);
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->rawColumns(['title', 'msg', 'to', 'created_at'])
                ->make(true);
        }
        return view('generalmanagement::notifications.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereIn('user_type', ['employee', 'manager'])->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $users = User::whereIn('id', $userIds)->select([
            'users.id as id',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''),' ', COALESCE(users.last_name, '')) as full_name"),
        ])->pluck('full_name', 'id')->toArray();
        return view('generalmanagement::notifications.create')->with(compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function storeAndSend(Request $request)
    {
        try {
            $user_ids = [];
            $to = [];
            if ($request->checkbox_all_employees && $request->checkbox_all_managers) {
                $user_ids = User::whereIn('user_type', ['employee', 'manager'])->pluck('id')->toArray();
                $to[] = ['all_employees'];
                $to[] = ['all_managers'];
            } else if ($request->checkbox_all_employees) {
                $user_ids = User::whereIn('user_type', ['employee'])->pluck('id')->toArray();
                $to[] = ['all_employees'];
            } elseif ($request->checkbox_all_managers) {
                $user_ids = User::whereIn('user_type', ['manager'])->pluck('id')->toArray();
                $to[] = ['all_managers'];
            } elseif ($request->users) {
                $user_ids = $request->users;
                $to[] = User::whereIn('id', $user_ids)->select([
                    'users.id as id',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name"),
                ])->pluck('full_name')->toArray();
            }
            $sentNotification = SentNotification::create([
                'via' => 'dashboard',
                'type' => 'GeneralManagement',
                'title' => $request->notification_title,
                'msg' => $request->message,
                'sender_id' => auth()->user()->id,
                'to' => json_encode($to),
            ]);
            $details = new stdClass();
            $details->title = $request->notification_title;
            $details->message = $request->message;

            foreach ($user_ids as $user_id) {
                SentNotificationsUser::create([
                    'sent_notifications_id' => $sentNotification->id,
                    'user_id' => $user_id,
                ]);
                // User::where('id', $user_id)->first()?->notify(new GeneralNotification($details, false, true));
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function settings()
    {

        return view('generalmanagement::notifications.settings');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        //
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
        //
    }
}
