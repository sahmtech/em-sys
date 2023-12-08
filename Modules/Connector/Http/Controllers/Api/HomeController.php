<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Utils\ModuleUtil;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsUserShift;

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
    public function home()
    {


        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $shift = EssentialsUserShift::where('user_id', $user->id)->first()->shift;



            $res = [
                'new_notifications' => 0,
                'work_day_start'=> $shift->start_time,
                'work_day_end'=> $shift->end_time,

            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
