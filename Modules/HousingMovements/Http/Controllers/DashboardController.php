<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    
    public function index()
    {
        $final_exit_count = User::where('user_type', 'worker')->where('status', 'inactive')->count();
        $reserved_shopping_count = HousingMovementsWorkerBooking::all()->count();
        $bookedWorker_ids = HousingMovementsWorkerBooking::all()->pluck('user_id');
        $HtrRoomsWorkersHistory_roomIds= HtrRoomsWorkersHistory::all()->pluck('room_id');
        $empty_rooms_count = HtrRoom::whereNotIn('id',$HtrRoomsWorkersHistory_roomIds)->count();
        
        $available_shopping_count = User::where('user_type', 'worker')->whereNull('assigned_to')->whereNotIn('id', $bookedWorker_ids)->count();
        $leaves_count =FollowupWorkerRequest::where('type','leaves')->count();
        return view('housingmovements::dashboard.hm_dashboard',compact('empty_rooms_count','leaves_count','available_shopping_count','reserved_shopping_count','final_exit_count'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::create');
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
        return view('housingmovements::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('housingmovements::edit');
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