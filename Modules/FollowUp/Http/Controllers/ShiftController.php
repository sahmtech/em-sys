<?php

namespace Modules\FollowUp\Http\Controllers;

use App\Contact;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use DateTime;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\Shift;
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
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $shifts = Shift::where('essentials_shifts.business_id', $business_id)->where('user_type', 'worker')
            ->with('Project')
            ->get();
        $salesProject = SalesProject::all()->pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_edit_shifts = auth()->user()->can('followup.edit_shifts');
        $can_delete_shifts = auth()->user()->can('followup.delete_shifts');
        if (request()->ajax()) {
            return DataTables::of($shifts)

                ->editColumn('name', function ($row) {
                    return $row->name ?? '';
                })

                ->editColumn('start_time', function ($row) {
                    $dateTime = DateTime::createFromFormat('H:i:s', $row->start_time);
                    $start_time = $dateTime->format('g:i A');

                    return $start_time ?? '';
                })
                ->editColumn('end_time', function ($row) {
                    $dateTime_ = DateTime::createFromFormat('H:i:s', $row->end_time);
                    $end_time = $dateTime_->format('g:i A');
                    return $end_time ?? '';
                })
                ->editColumn('holiday', function ($row) {
                    $html = '';
                    if ($row->holidays)
                        foreach ($row->holidays as $holiday) {
                            $html .= '<h6 style="margin-top: 0px;"><span class="badge badge-secondary">' . __('lang_v1.' . $holiday) . '</span></h6>';
                        }
                    else {
                        $html .= ' <h6 style="margin-top: 0px;"><span class="badge badge-secondary">لا يوجد عطل</span></h6>';
                    }

                    return $html;
                })
                ->editColumn('project_name', function ($row) {
                    return $row->Project->name ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_shifts, $can_delete_shifts) {

                        $html = '';
                        if (($is_admin  ||  $can_edit_shifts)) {
                            $html .= '
                        <a href="' . route('shifts-edit', ['id' => $row->id])  . '"
                        data-href="' . route('shifts-edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_shits_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        }
                        if (($is_admin  ||  $can_delete_shifts)) {
                            $html .= '
                    <button data-href="' .  route('shifts-delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_shift_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        }
                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {
                })

                ->rawColumns(['action', 'name', 'shift_type', 'start_time', 'holiday', 'end_time', 'project_name'])
                ->make(true);
        }



        return view('followup::shifts.index', compact('shifts', 'salesProject'));
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

        return view('followup::shifts.create', compact('contacts', 'days'));
    }

    public function ProjectsByContacts($id)
    {
        return Contact::find($id)->salesProjects;
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



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


            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('housingmovements::lang.added_success'),
                ]);
        } catch (\Exception $e) {
            // \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
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
        return view('followup::show');
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

        return view('followup::shifts.edit', compact('salesProject', 'days', 'shift'));
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $shift = Shift::find($id);
        try {

            $shift->update([
                'name' => $request->input('name'),
                'holidays' => $request->input('holidays'),
                'project_id' => $request->input('project_id'),
                'end_time' => $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('end_time')))),
                'start_time' => $this->moduleUtil->uf_time(date("h:i:sa", strtotime($request->input('start_time')))),
            ]);
            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('housingmovements::lang.updated_success'),
                ]);
        } catch (\Exception $e) {
            // \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
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
        if (request()->ajax()) {
            try {
                Shift::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف الفترة بنجاح',
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