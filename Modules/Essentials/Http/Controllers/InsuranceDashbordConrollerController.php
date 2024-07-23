<?php

namespace Modules\Essentials\Http\Controllers;

use App\Company;
use App\Contact;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use App\Request as UserRequest;

class InsuranceDashbordConrollerController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_change_status = auth()->user()->can('essentials.change_insurance_request_status');

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }


        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%تأمين%')
            ->pluck('id')->toArray();



        $requestsProcess_count = UserRequest::where('request_processes.status', 'pending')->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')->whereIn('department_id', $departmentIds)
            ->whereIn('requests.related_to', $userIds)->where('request_processes.sub_status', null)->count();


        $insuranceCompanies_count = Contact::where('contacts.type', 'insurance')->count();

        $insuranceContracts_count = DB::table('essentials_insurance_contracts')->count();
        $insuranceCompaniesContracts_count = Company::where('business_id', $business_id)
            ->with(['essentialsCompaniesInsurancesContract'])
            ->count();
        return view('essentials::insurance.dashboard.insurance_dashboard', compact('requestsProcess_count', 'insuranceCompaniesContracts_count', 'insuranceContracts_count', 'insuranceCompanies_count'));
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
    public function store(Request $request)
    {
        //
    }

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