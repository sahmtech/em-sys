<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\Company;
use App\User;
use App\Utils\ModuleUtil;
use DB;

class EssentialsReportController extends Controller
{
    protected $moduleUtil;



    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }


        $business_id = request()->session()->get('user.business_id');
        // $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        // $bl_attributes = $business_locations['attributes'];
        // $business_locations = $business_locations['locations'];
        // $default_location = null;
        // foreach ($business_locations as $id => $name) {
        //     $default_location = BusinessLocation::findOrFail($id);
        //     break;
        // }
        $business_locations = Company::whereIn('id', $companies_ids)->pluck('name', 'id');
        if ($request->ajax()) {
            $employees = User::whereIn('id', $userIds)->where('user_type', 'employee')->count();
            $managers = User::whereIn('id', $userIds)->where('user_type', 'manager')->count();
            $workers = User::whereIn('id', $userIds)->where('user_type', 'worker')->count();

            $ageDistribution = User::whereIn('id', $userIds)->select(DB::raw('FLOOR(DATEDIFF(NOW(), dob) / 365.25) as age'), DB::raw('count(*) as count'))
                ->groupBy('age')
                ->get();

            $genderDistribution = User::whereIn('id', $userIds)->select('gender', DB::raw('count(*) as count'))
                ->where('user_type', 'employee')
                ->groupBy('gender')
                ->get();

            $processedAgeDistribution = [
                'group1' => 0,
                'group2' => 0,
                'group3' => 0,
            ];

            foreach ($ageDistribution as $item) {
                if ($item->age <= 25) {
                    $processedAgeDistribution['group1'] += $item->count;
                } elseif ($item->age <= 40) {
                    $processedAgeDistribution['group2'] += $item->count;
                } else {
                    $processedAgeDistribution['group3'] += $item->count;
                }
            }

            $data = [
                [
                    'totalEmployees' => $employees,
                    'typeOfEmployees' => [
                        'employees' => $employees,
                        'managers' => $managers,
                        'workers' => $workers,
                    ],
                    'genderDistribution' => $genderDistribution,
                    'ageDistribution' => $processedAgeDistribution,
                ],
            ];

            return response()->json(['data' => $data]);
        }

        return view('essentials::reports.employees_info_report')
            ->with(compact('business_locations'));
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
