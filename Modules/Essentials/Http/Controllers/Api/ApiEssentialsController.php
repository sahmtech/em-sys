<?php

namespace Modules\Essentials\Http\Controllers\Api;

use App\Notification;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\ToDo;

class ApiEssentialsController extends ApiController
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
    public function getEditUserInfo()
    {

        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();


            $user = User::where('id', $user->id)->first();
            $res = [
                'first_name' =>   $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function updateUserInfo(Request $request)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            if ($request->otp == '1111') {
                $user = User::where('id', $user->id)->first();
                $res = [
                    'first_name' =>  $request->first_name ?? $user->first_name,
                    'mid_name' => $request->mid_name ?? $user->mid_name,
                    'last_name' => $request->last_name ?? $user->last_name,
                    'email' => $request->email ?? $user->email,
                    'contact_number' => $request->contact_number ?? $user->contact_number,
                ];

                $user->update($res);
                return new CommonResource($res);
            } else {
                throw new \Exception("The provided OTP is incorrect.");
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    function resetPassword(Request $request)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();

            if ($request->otp == '1111') {
                $user = User::where('id', $user->id)->first();
                $user->update(['password' => Hash::make($request->new_password)]);
                return new CommonResource(['msg' => 'تم تغيير كلمة المرور بنجاح']);
            } else {
                throw new \Exception("The provided OTP is incorrect.");
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    function changeToDoStatus(Request $request, $id)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $toDo = ToDo::find($id);
            $status = ['new', 'in_progress', 'on_hold', 'completed',];
            $toDo->update(['status' => $status[$request->status]]);
            return new CommonResource(['msg' => 'تم تغيير حالة المهمة بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

  
}
