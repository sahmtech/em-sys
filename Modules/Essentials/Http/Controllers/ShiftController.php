<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsUserShift;
use Modules\Essentials\Entities\Shift;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ShiftController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (request()->ajax()) {
            $shifts = Shift::where('essentials_shifts.business_id', $business_id)
                ->whereNull('user_type')
                ->select([
                    'id',
                    'name',
                    'type',
                    'user_type',
                    'start_time',
                    'end_time',
                    'holidays',
                ]);

            return Datatables::of($shifts)
                ->editColumn('start_time', function ($row) {
                    $start_time_formated = $this->moduleUtil->format_time($row->start_time);

                    return $start_time_formated;
                })
                ->editColumn('end_time', function ($row) {
                    $end_time_formated = $this->moduleUtil->format_time($row->end_time);

                    return $end_time_formated;
                })
                ->editColumn('type', function ($row) {
                    return __('essentials::lang.' . $row->type);
                })
                ->editColumn('holidays', function ($row) {
                    if (!empty($row->holidays)) {
                        $holidays = array_map(function ($item) {
                            return __('lang_v1.' . $item);
                        }, $row->holidays);

                        return implode(', ', $holidays);
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" data-href="' . action([\Modules\Essentials\Http\Controllers\ShiftController::class, 'edit'], [$row->id]) . '" data-container="#edit_shift_modal" class="btn-modal btn btn-xs btn-primary"><i class="fas fa-edit" aria-hidden="true"></i> ' . __('messages.edit') . '</a>';

                    return $html;
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'type'])
                ->make(true);
        }
    }

    public function users_shifts(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1');

        $crud_users_shifts = auth()->user()->can('essentials.crud_users_shifts');
        if (!$crud_users_shifts) {
            abort(403, 'Unauthorized action.');
        }

        $userIds = User::where('user_type', '!=', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $shifts = Shift::pluck('name', 'id');
        $users_shifts = EssentialsUserShift::join('users as u', 'u.id', '=', 'essentials_user_shifts.user_id')
            ->join('essentials_shifts as shift', 'shift.id', '=', 'essentials_user_shifts.essentials_shift_id')
            ->whereIn('u.id', $userIds)
            ->where(function ($query) {
                $query->where('u.status', 'active')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('u.status', 'inactive')
                            ->whereIn('u.sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })
            ->select([
                'essentials_user_shifts.id',
                'u.id_proof_number as id_proof_number',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.mid_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'essentials_user_shifts.start_date',
                'essentials_user_shifts.end_date',
                'shift.name',
                'shift.type',
                'shift.start_time',
                'shift.end_time',
                'shift.holidays',
                'u.emp_number',
                'essentials_user_shifts.is_active',

            ])
            ->orderBy('essentials_user_shifts.id', 'desc');

        if ($request->ajax()) {
            return Datatables::of($users_shifts)
                ->editColumn('type', function ($row) {
                    return __('essentials::lang.' . $row->type);
                })
                ->editColumn('holidays', function ($row) {
                    if (!empty($row->holidays)) {
                        $holidays = is_array($row->holidays) ? $row->holidays : json_decode($row->holidays, true);
                        $holidays = array_map(function ($item) {
                            return __('lang_v1.' . trim($item));
                        }, $holidays);

                        return implode(', ', $holidays);
                    }
                })
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->where("u.id_proof_number", 'like', "%{$keyword}%");
                })
                ->make(true);
        }

        $all_users = User::whereIn('id', $userIds)
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'inactive')
                            ->whereIn('sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })
            ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), ' - ', COALESCE(id_proof_number, '')) as full_name"))
            ->get();

        $users = $all_users->pluck('full_name', 'id');

        return view('essentials::employee_affairs.users_shifts.index')->with(compact('users', 'shifts'));
    }

    public function editUserShift($id)
    {
        $shift = EssentialsUserShift::findOrFail($id);
        return response()->json($shift);
    }

    public function updateUserShift(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:essentials_shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();

        try {
            $shift = EssentialsUserShift::findOrFail($id);
            $shift->update([
                'user_id' => $validatedData['user_id'],
                'essentials_shift_id' => $validatedData['shift_id'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'is_active' => 1,
            ]);

            DB::commit();

            return response()->json(['message' => __('messages.shift_updated_successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => __('messages.error_occurred'), 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteUserShift($id)
    {
        try {
            $shift = EssentialsUserShift::findOrFail($id);
            $shift->delete();

            $output = [
                'success' => true,
                'msg' => __('messages.deleted_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeUserShift(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:essentials_shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);


        EssentialsUserShift::where('user_id', $validatedData['user_id'])

            ->update(['is_active' => 0]);


        EssentialsUserShift::create([
            'user_id' => $validatedData['user_id'],
            'essentials_shift_id' => $validatedData['shift_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'is_active' => 1,
        ]);

        return response()->json(['message' => __('messages.added_successfully')]);
    }



    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['name', 'type', 'holidays']);

            if ($input['type'] != 'flexible_shift') {
                $input['start_time'] = $this->moduleUtil->uf_time($request->input('start_time'));
                $input['end_time'] = $this->moduleUtil->uf_time($request->input('end_time'));
            } else {
                $input['start_time'] = null;
                $input['end_time'] = null;
            }

            $input['is_allowed_auto_clockout'] = !empty($request->input('is_allowed_auto_clockout')) ? 1 : 0;

            if (!empty($request->input('auto_clockout_time'))) {
                $input['auto_clockout_time'] = $this->moduleUtil->uf_time($request->input('auto_clockout_time'));
            }

            $input['business_id'] = $business_id;

            Shift::create($input);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),

            ];
        }

        return $output;
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $shift = Shift::where('business_id', $business_id)
            ->findOrFail($id);

        $days = $this->moduleUtil->getDays();

        return view('essentials::attendance.shift_modal')->with(compact('shift', 'days'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



            $input = $request->only(['name', 'type', 'holidays']);

            if ($input['type'] != 'flexible_shift') {
                $input['start_time'] = $this->moduleUtil->uf_time($request->input('start_time'));
                $input['end_time'] = $this->moduleUtil->uf_time($request->input('end_time'));
            } else {
                $input['start_time'] = null;
                $input['end_time'] = null;
            }

            $input['is_allowed_auto_clockout'] = !empty($request->input('is_allowed_auto_clockout')) ? 1 : 0;

            if (!empty($request->input('auto_clockout_time'))) {
                $input['auto_clockout_time'] = $this->moduleUtil->uf_time($request->input('auto_clockout_time'));
            }

            if (!empty($input['holidays'])) {
                $input['holidays'] = json_encode($input['holidays']);
            } else {
                $input['holidays'] = null;
            }

            $shift = Shift::where('business_id', $business_id)
                ->where('id', $id)
                ->update($input);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),

            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function getAssignUsers($shift_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $shift = Shift::where('business_id', $business_id)
            ->with(['user_shifts'])
            ->findOrFail($shift_id);

        $users = User::forDropdown($business_id, false);

        $user_shifts = [];

        if (!empty($shift->user_shifts)) {
            foreach ($shift->user_shifts as $user_shift) {
                $user_shifts[$user_shift->user_id] = [
                    'start_date' => !empty($user_shift->start_date) ? $this->moduleUtil->format_date($user_shift->start_date) : null,
                    'end_date' => !empty($user_shift->end_date) ? $this->moduleUtil->format_date($user_shift->end_date) : null,
                ];
            }
        }

        return view('essentials::attendance.add_shift_users')
            ->with(compact('shift', 'users', 'user_shifts'));
    }

    public function postAssignUsers(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $shift_id = $request->input('shift_id');
            $shift = Shift::where('business_id', $business_id)
                ->find($shift_id);

            $user_shifts = $request->input('user_shift');
            $user_shift_data = [];
            $user_ids = [];
            foreach ($user_shifts as $key => $value) {
                if (!empty($value['is_added'])) {
                    $user_ids[] = $key;
                    EssentialsUserShift::updateOrCreate(
                        [
                            'essentials_shift_id' => $shift_id,
                            'user_id' => $key,
                        ],
                        [
                            'start_date' => !empty($value['start_date']) ? $this->moduleUtil->uf_date($value['start_date']) : null,
                            'end_date' => !empty($value['end_date']) ? $this->moduleUtil->uf_date($value['end_date']) : null,
                        ]
                    );
                }
            }

            EssentialsUserShift::where('essentials_shift_id', $shift_id)
                ->whereNotIn('user_id', $user_ids)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),

            ];
        }

        return $output;
    }
}
