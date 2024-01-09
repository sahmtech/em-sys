<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\Sales\Entities\SalesProject;

class WorkerBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('housingmovements::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $workers = User::where('user_type', 'worker')->whereNull('assigned_to')->get();
        $projects = SalesProject::all();
        return view('housingmovements::projects_workers.bookWorker', compact('workers', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            HousingMovementsWorkerBooking::create([
                'project_id' => $request->input('project_id'),
                'created_by' => Auth::user()->id,
                'user_id' => $request->input('user_id'),
            ]);


            DB::commit();
            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('housingmovements::lang.added_success'),
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
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
        if (request()->ajax()) {
            try {
                HousingMovementsWorkerBooking::find($id)->delete();

                $output = [
                    'success' => true,
                    'msg' => 'تم الحذف بنجاح',
                ];
            } catch (Exception $e) {
                return redirect()->back()
                    ->with('status', [
                        'success' => false,
                        'msg' => __('messages.something_went_wrong'),
                    ]);
            }
            return $output;
        }
    }
}