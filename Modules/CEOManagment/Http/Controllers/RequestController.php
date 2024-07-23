<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use App\AccessRole;
use App\AccessRoleCompany;
use App\AccessRoleRequest;
use App\Company;
use App\User;
use App\Utils\RequestUtil;
use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Carbon\Carbon;
use Modules\CEOManagment\Entities\RequestsType;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
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

        $can_change_status = auth()->user()->can('ceomanagment.change_request_status');
        $can_return_request = auth()->user()->can('ceomanagment.return_request');
        $can_show_request = auth()->user()->can('ceomanagment.view_request');

        $departmentIds = EssentialsDepartment::pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)

            ->where(function ($query) {
                $query->where('name', 'LIKE', '%تنفيذ%');
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')->where('business_id', $business_id)->where('name', 'LIKE', '%تنفيذ%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'ceomanagment::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, $departmentIdsForGeneralManagment);
    }
}