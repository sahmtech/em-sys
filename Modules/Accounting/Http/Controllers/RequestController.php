<?php

namespace Modules\Accounting\Http\Controllers;

use App\AccessRole;
use App\AccessRoleRequest;
use App\Utils\RequestUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Illuminate\Support\Facades\Session;
use Modules\CEOManagment\Entities\RequestsType;

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

        $can_change_status = auth()->user()->can('accounting.change_status');
        $can_return_request = auth()->user()->can('accounting.return_the_request');
        $can_show_request = auth()->user()->can('accounting.show_request');

        $company_id = Session::get('selectedCompanyId');
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'like', '%حاسب%')
                ->orWhere('name', 'like', '%مالي%');
        })
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_HR_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }
        $roles = DB::table('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%حاسب%')
                    ->orWhere('name', 'like', '%مالي%');
            })->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'accounting::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, [], false, $company_id);
    }



    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'like', '%حاسب%')
                ->orWhere('name', 'like', '%مالي%');
        })
            ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
    }
}