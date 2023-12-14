<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\Contact;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\Shift;
use Modules\Essentials\Utils\EssentialsUtil;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;

class ShiftController extends Controller
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
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $shifts = Shift::where('essentials_shifts.business_id', $business_id)->where('user_type', 'worker')
            ->with('Project')
            ->paginate(5);
        $salesProject = SalesProject::all()->pluck('name', 'id');
        

        return view('housingmovements::shifts.index', compact('shifts', 'salesProject'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        // $salesProject = SalesProject
        $contacts = Contact::all()->pluck('supplier_business_name', 'id');
        $essentialsUtil = new Util();

        $days = $essentialsUtil->getDays();

        return view('housingmovements::shifts.create', compact('contacts', 'days'));
    }

    public function ProjectsByContacts($id){
        return Contact::find($id)->salesProject;
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // return $request->input('end_time');
        // return $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('end_time'))));
        try {

            Shift::create([
                'business_id' => $business_id,
                'user_type' => 'worker',
                'type' => 'fixed_shift',
                'name' => $request->input('name'),
                'holidays' => $request->input('holidays'),
                'project_id' => $request->input('project_id'),
                'end_time' => $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('end_time')))),
                'start_time' => $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('start_time')))),
            ]);


            return redirect()->back();
        } catch (\Exception $e) {
            // \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());


            return redirect()->back();
        }
        return redirect()->back();
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
        $salesProject = SalesProject::all();
        $essentialsUtil = new Util();
        $shift = Shift::find($id);
        $days = $essentialsUtil->getDays();

        return view('housingmovements::shifts.edit', compact('salesProject', 'days', 'shift'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $shift = Shift::find($id);
        try {

            $shift->update([
                'name' => $request->input('name'),
                'holidays' => $request->input('holidays'),
                'project_id' => $request->input('project_id'),
                'end_time' => $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('end_time')))),
                'start_time' => $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('start_time')))),
            ]);


            return redirect()->back();
        } catch (\Exception $e) {
            // \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());


            return redirect()->back();
        }
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */

    public function destroy($id)
    {
        try {
            Shift::find($id)->delete();
            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}