<?php

namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\RequestUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
use App\AccessRole;
use Modules\CEOManagment\Entities\RequestsType;
use App\AccessRoleRequest;

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

        $can_change_status = auth()->user()->can('operationsmanagmentgovernment.change_request_status');
        $can_return_request = auth()->user()->can('operationsmanagmentgovernment.return_request');
        $can_show_request = auth()->user()->can('operationsmanagmentgovernment.show_request');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->Where(function ($query) {
                $query->where('name', 'LIKE', '%تشغيل%')
                    ->where('name', 'LIKE', '%حكومي%');
            })
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('operationsmanagmentgovernment::lang.there_is_no_operationsmanagmentgovernment_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')->where('business_id', $business_id)
            ->Where(function ($query) {
                $query->where('name', 'LIKE', '%تشغيل%')
                    ->where('name', 'LIKE', '%حكومي%');
            })->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'operationsmanagmentgovernment::requests.allRequests', $can_change_status, $can_return_request, $can_show_request, $requestsTypes);
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('operationsmanagmentgovernment::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->Where(function ($query) {
                $query->where('name', 'LIKE', '%تشغيل%')
                    ->where('name', 'LIKE', '%حكومي%');
            })
            ->pluck('id')->toArray();

        return $this->requestUtil->storeRequest($request, $departmentIds);
    }
}
