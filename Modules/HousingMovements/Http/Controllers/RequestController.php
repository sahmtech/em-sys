<?php

namespace Modules\HousingMovements\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\RequestUtil;
use Modules\Essentials\Entities\EssentialsDepartment;


class RequestController extends Controller
{
    protected $requestUtil;



    public function __construct(RequestUtil $requestUtil)
    {
        $this->requestUtil = $requestUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('housingmovements.change_status');
        $can_return_request = auth()->user()->can('housingmovements.return_the_request');
        $can_show_request = auth()->user()->can('housingmovements.view_request');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%سكن%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_HM_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }
        $ownerTypes = ['employee','worker', 'manager'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'housingmovements::requests.allRequest', $can_change_status, $can_return_request, $can_show_request);
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%سكن%')
            ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
    }


    public function requestsFillter()
    {
        $business_id = request()->session()->get('user.business_id');
        $can_change_status = auth()->user()->can('housingmovements.change_status');
        $can_return_request = auth()->user()->can('housingmovements.return_the_request');
        $can_show_request = auth()->user()->can('housingmovements.view_request');


        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%سكن%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_HM_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }
        $ownerTypes = ['employee', 'manager'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'housingmovements::requests.allRequestFillter', $can_change_status ,$can_return_request, $can_show_request);
    }
}
