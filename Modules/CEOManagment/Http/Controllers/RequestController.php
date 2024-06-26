<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use App\AccessRole;
use App\AccessRoleCompany;
use App\Company;
use App\User;
use App\Utils\RequestUtil;
use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Carbon\Carbon;

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

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)->pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)

            ->where(function ($query) {
                $query->where('name', 'LIKE', '%تنفيذ%')
                    ->orWhere(function ($query) {
                        $query->where('name', 'LIKE', '%تشغيل%')
                            ->where('name', 'LIKE', '%حكومي%');
                    });
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'ceomanagment::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $departmentIdsForGeneralManagment);
    }
}
