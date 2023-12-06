<?php

namespace Modules\Essentials\Http\Controllers\Api;

use App\Utils\ModuleUtil;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Auth;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsLeaveType;

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
        $business_id = request()->session()->get('user.business_id');

        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;


            $leave_types = EssentialsLeaveType::where('business_id', $business_id)
                ->select(['leave_type', 'duration', 'max_leave_count', 'id']);



            $res = [
                'leave_types' => $leave_types
            ];

            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
