<?php

namespace Modules\Essentials\Http\Controllers;


use Illuminate\Http\Request;
use App\Utils\RequestUtil;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Essentials\Entities\EssentialsDepartment;
use App\AccessRole;
use Modules\CEOManagment\Entities\RequestsType;
use App\AccessRoleRequest;
use Illuminate\Support\Facades\DB;

class InsuranceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    protected $requestUtil;


    public function __construct(RequestUtil $requestUtil)
    {
        $this->requestUtil = $requestUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('essentials.change_insurance_request_status');
        $can_return_request = auth()->user()->can("essentials.return_insurance_request");
        $can_show_request = auth()->user()->can("essentials.show_insurances_request");


        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%تأمين%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_insurance_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager'];
        $roles = DB::table('roles')->where('business_id', $business_id)
            ->where('name', 'LIKE', '%تأمين%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'essentials::requests.insurance_requests', $can_change_status, $can_return_request, $can_show_request, $requestsTypes);
    }


    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%تأمين%')
            ->pluck('id')->toArray();

        return $this->requestUtil->storeRequest($request, $departmentIds);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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
