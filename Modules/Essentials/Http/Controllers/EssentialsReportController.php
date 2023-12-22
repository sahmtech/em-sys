<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\User;
use DB;
class EssentialsReportController extends Controller
{
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
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }
    
        if ($request->ajax()) {
            $employees = User::where('user_type', 'employee')->count();
            $managers = User::where('user_type', 'manager')->count();
            $workers = User::where('user_type', 'worker')->count();
    
            $ageDistribution = User::select(DB::raw('FLOOR(DATEDIFF(NOW(), dob) / 365.25) as age'), DB::raw('count(*) as count'))
                ->groupBy('age')
                ->get();
    
            $genderDistribution = User::select('gender', DB::raw('count(*) as count'))
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
            ->with(compact('business_locations', 'bl_attributes', 'business_locations'));
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
