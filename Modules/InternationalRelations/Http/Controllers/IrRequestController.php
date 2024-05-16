<?php

namespace Modules\InternationalRelations\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Modules\Essentials\Entities\EssentialsDepartment;


class IrRequestController extends Controller
{
    protected $moduleUtil;
    protected $requestUtil;


    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('internationalrelations.change_request_status');
        $can_return_request = auth()->user()->can('internationalrelations.return_ir_request');
        $can_show_request = auth()->user()->can('internationalrelations.show_ir_request');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%دولي%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_internationalrelations_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['worker'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'internationalrelations::requests.allRequest', $can_change_status, $can_return_request, $can_show_request);
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%دولي%')
            ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
    }
}