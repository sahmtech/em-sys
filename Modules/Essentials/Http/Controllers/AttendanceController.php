<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\Business;
use App\Company;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsAttendance;
use Modules\Essentials\Entities\EssentialsAttendanceStatus;
use Modules\Essentials\Entities\Shift;
use Modules\Essentials\Utils\EssentialsUtil;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    protected $essentialsUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, EssentialsUtil $essentialsUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
    }

    public function personalAttendance($year = null, $month = null)
    {

        $user = auth()->user();
        $business_id = $user->business_id;
        $business = Business::where('id', $business_id)->first();



        if (!$year) {
            $year = Carbon::now()->year;
        }
        if (!$month) {
            $month = Carbon::now()->month;
        }


        $attendanceList = EssentialsAttendance::where([['user_id', '=', $user->id], ['business_id', '=', $business_id]])->with('shift')->get();
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $month_name = Carbon::create()->month($month)->format('F');


        //days before
        $daysBefore = [];
        $attended = 0;
        $late = 0;
        $absent = 0;
        $day = $firstDayOfMonth->subWeek();
        for ($i = 0; $i < 7; $i++) {
            error_log($day);
            $clock_in_time = null;
            $clock_out_time = null;
            if ($day->isFuture()) {
                $status = 0;
            } else {
                $status = 4;

                foreach ($attendanceList as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->clock_in_time)->toDateString();
                    $clock_in_time = null;
                    $clock_out_time = null;
                    if ($day->toDateString() == $attendanceDate) {
                        if ($attendance->status_id == 1) {
                            $status = 1;
                        } else if ($attendance->status_id == 2 || $attendance->status_id == 3) {
                            $status = 2;
                        }
                        break;
                    }
                }
                if ($status == 1) {
                    $attended += 1;
                } elseif ($status == 2 || $status == 3) {
                    $late += 1;
                } elseif ($status == 4) {
                    $absent += 1;
                }
            }
            $daysBefore[] = [
                'number_in_month' => $day->day,
                'number_in_week' => ($day->dayOfWeek + 1) % 8,
                'month' => $month == 1 ? 12 : $month - 1,
                'year' =>  $month == 1 ? $year - 1 : $year,
                'name' => $day->format('l'), // Full day name (Sunday, Monday, ...)
                'status' => $status == 1 ? 'حضور' : (($status == 2 || $status == 3) ? 'تأخير' : ($status == 4 ? 'غياب' : '')),
                'start_time' => $clock_in_time ? Carbon::parse($clock_in_time)->format('h:i A') : null,
                'end_time' => $clock_out_time ? Carbon::parse($clock_out_time)->format('h:i A') : null,
            ];
            $day->addDay();
        }

        //days
        $days = [];
        $attended_in_this_month = 0;
        $late_in_this_month = 0;
        $absent_in_this_month = 0;
        for ($day = $firstDayOfMonth; $day->lte($lastDayOfMonth); $day->addDay()) {
            $clock_in_time = null;
            $clock_out_time = null;
            if ($day->isFuture()) {
                $status = 0;
            } else {
                $status = 4;

                foreach ($attendanceList as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->clock_in_time)->toDateString();
                    $clock_in_time = null;
                    $clock_out_time = null;
                    if ($day->toDateString() == $attendanceDate) {

                        if ($attendance->status_id == 1) {
                            $status = 1;
                        } else if ($attendance->status_id == 2 || $attendance->status_id == 3) {
                            $status = 2;
                        }
                        break;
                        break;
                    }
                }
                if ($status == 1) {
                    $attended_in_this_month += 1;
                } elseif ($status == 2 || $status == 3) {
                    $late_in_this_month += 1;
                } elseif ($status == 4) {
                    $absent_in_this_month += 1;
                }
            }

            $days[] = [
                'number_in_month' => $day->day,
                'number_in_week' => ($day->dayOfWeek + 1) % 8,
                'month' => (int)$month,
                'year' => $year,
                'name' => $day->format('l'), // Full day name (Sunday, Monday, ...)
                'status' => $status == 1 ? 'حضور' : (($status == 2 || $status == 3) ? 'تأخير' : ($status == 4 ? 'غياب' : '')),
                'start_time' => $clock_in_time ? Carbon::parse($clock_in_time)->format('h:i A') : null,
                'end_time' => $clock_out_time ? Carbon::parse($clock_out_time)->format('h:i A') : null,
            ];
        }


        //days after
        $daysAfter = [];
        $attended = 0;
        $late = 0;
        $absent = 0;
        $day = $lastDayOfMonth->addDay();
        for ($i = 0; $i < 7; $i++) {
            $clock_in_time = null;
            $clock_out_time = null;
            if ($day->isFuture()) {
                $status = 0;
            } else {
                $status = 4;

                foreach ($attendanceList as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->clock_in_time)->toDateString();
                    $clock_in_time = null;
                    $clock_out_time = null;
                    if ($day->toDateString() == $attendanceDate) {

                        if ($attendance->status_id == 1) {
                            $status = 1;
                        } else if ($attendance->status_id == 2 || $attendance->status_id == 3) {
                            $status = 2;
                        }
                        break;
                    }
                }
                if ($status == 1) {
                    $attended += 1;
                } elseif ($status == 2 || $status == 3) {
                    $late += 1;
                } elseif ($status == 4) {
                    $absent += 1;
                }
            }

            $daysAfter[] = [
                'number_in_month' => $day->day,
                'number_in_week' => ($day->dayOfWeek + 1) % 8,
                'month' => $month == 12 ? 1 : $month + 1,
                'year' => $month == 12 ? $year + 1 : $year,
                'name' => $day->format('l'), // Full day name (Sunday, Monday, ...)
                'status' => $status == 1 ? 'حضور' : (($status == 2 || $status == 3) ? 'تأخير' : ($status == 4 ? 'غياب' : '')),
                'start_time' => $clock_in_time ? Carbon::parse($clock_in_time)->format('h:i A') : null,
                'end_time' => $clock_out_time ? Carbon::parse($clock_out_time)->format('h:i A') : null,
            ];
            $day->addDay();
        }




        $res = [
            'attended' => $attended_in_this_month,
            'late' => $late_in_this_month,
            'absent' => $absent_in_this_month,
            'month_name' => $month_name,
            'days_before' => $daysBefore,
            'days' => $days,
            'days_after' => $daysAfter,
        ];
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $res,
            ]);
        }

        return view('essentials::attendance.personal_attendance')->with(compact('res'));
    }

    public function end_manual_attendance()
    {
        $output = '';
        try {
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


            $companies_ids = Company::pluck('id')->unique()->toArray();
            if (!$is_admin) {

                $companies_ids = [];
                $roles = auth()->user()->roles;
                foreach ($roles as $role) {
                    $accessRole = AccessRole::where('role_id', $role->id)->first();
                    if ($accessRole) {
                        $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                    }
                }
            }
            $attendances = EssentialsAttendance::whereHas('employee', function ($qu) use ($companies_ids) {
                $qu->whereIn('company_id', $companies_ids)->whereHas('contract', function ($qu2) {
                    $qu2->where('contract_type_id', 3);
                });
            })->whereDate('clock_in_time', Carbon::today())->inRandomOrder()->get();


            foreach ($attendances as $attendance) {
                $startTime = Carbon::createFromTime(16, 45, 0, 'Asia/Riyadh');

                $randomSeconds = rand(0, 30 * 60);

                $randomTime = $startTime->copy()->addSeconds($randomSeconds);
                $attendance->update(['clock_out_time' => $randomTime]);
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function add_manual_attendance()
    {
        $output = '';
        try {
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


            $companies_ids = Company::pluck('id')->unique()->toArray();
            if (!$is_admin) {

                $companies_ids = [];
                $roles = auth()->user()->roles;
                foreach ($roles as $role) {
                    $accessRole = AccessRole::where('role_id', $role->id)->first();
                    if ($accessRole) {
                        $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                    }
                }
            }


            $user = auth()->user();
            $business_id = $user->business_id;
            $business = Business::findOrFail($business_id);

            $users_ids = User::whereHas('contract', function ($qu) {
                $qu->where('contract_type_id', 3);
            })->whereIn('company_id', $companies_ids)->inRandomOrder()->pluck('users.id')->toArray();


            $attendances = EssentialsAttendance::whereHas('employee', function ($qu) use ($companies_ids) {
                $qu->whereIn('company_id', $companies_ids)->whereHas('contract', function ($qu2) {
                    $qu2->where('contract_type_id', 3);
                });
            })->whereDate('clock_in_time', Carbon::today())->get();

            $usersCount = count($users_ids);

            // Get the number of attendances
            $attendancesCount = $attendances->count();

            if ($usersCount ==    $attendancesCount) {
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.already_clocked_in'),
                ];
                return redirect()->back()->with('status', $output);
            }

            foreach ($users_ids as  $user_id) {

                $startTime = Carbon::createFromTime(8, 45, 0, 'Asia/Riyadh');

                $randomSeconds = rand(0, 30 * 60);

                $randomTime = $startTime->copy()->addSeconds($randomSeconds);

                $data = [
                    'business_id' => $business_id,
                    'user_id' => $user_id,
                    'clock_in_time' => $randomTime,
                    'clock_in_note' => '',
                ];
                EssentialsAttendance::create($data);
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function manual_attendance()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $companies_ids = Company::pluck('id')->unique()->toArray();
        if (!$is_admin) {

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {
                $accessRole = AccessRole::where('role_id', $role->id)->first();
                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }

        $attendance = EssentialsAttendance::whereHas('employee', function ($qu) use ($companies_ids) {
            $qu->whereIn('company_id', $companies_ids)->whereHas('contract', function ($qu2) {
                $qu2->where('contract_type_id', 3);
            });
        });

        if (request()->ajax()) {
            return Datatables::of($attendance)
                ->editColumn('clock_in', function ($row) {
                    $html = Carbon::parse($row->clock_in_time)->format('A h:i');
                    if (!empty($row->clock_in_location)) {
                        $html .= '<br>' . $row->clock_in_location . '<br>';
                    }

                    if (!empty($row->clock_in_note)) {
                        $html .= '<br>' . $row->clock_in_note . '<br>';
                    }

                    return $html;
                })
                ->editColumn('clock_out', function ($row) {
                    if ($row->clock_out_time) {
                        $html = Carbon::parse($row->clock_out_time)->format('A h:i');
                        if (!empty($row->clock_out_location)) {
                            $html .= '<br>' . $row->clock_out_location . '<br>';
                        }

                        if (!empty($row->clock_out_note)) {
                            $html .= '<br>' . $row->clock_out_note . '<br>';
                        }

                        return $html;
                    } else {
                        return '';
                    }
                })
                ->editColumn('date', function ($row) {

                    return Carbon::parse($row->clock_in_time)->format('Y-m-d');
                })
                ->addColumn('user', function ($row) {

                    return $row->employee->first_name . ' ' . $row->employee->last_name;
                })

                ->make(true);
        }
        $clock_in = '';
        $rand = $attendance->whereDate('clock_in_time', Carbon::today())->first();
        if ($rand && $rand->clock_in_time  &&  $rand->clock_out_time == null) {
            $clock_in = "not empty";
        }
        return view('essentials::attendance.manual_attendance')->with(compact('clock_in'));
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

        $can_crud_all_attendance = auth()->user()->can('essentials.crud_all_attendance');

        $can_edit_all_attendance = auth()->user()->can('essentials.edit_all_attendance');
        $can_delete_all_attendance = auth()->user()->can('essentials.delete_all_attendance');

        $can_view_own_attendance = auth()->user()->can('essentials.view_own_attendance');

        if (!$can_crud_all_attendance && !$can_view_own_attendance) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $statuses = EssentialsAttendanceStatus::pluck('name', 'id');
        if (request()->ajax()) {
            $attendance = EssentialsAttendance::where('essentials_attendances.business_id', $business_id)
                ->join('users as u', 'u.id', '=', 'essentials_attendances.user_id')
                ->leftjoin('essentials_shifts as es', 'es.id', '=', 'essentials_attendances.essentials_shift_id')
                ->select([
                    'essentials_attendances.id',
                    'essentials_attendances.status_id as status',
                    'clock_in_time',
                    'clock_out_time',
                    'clock_in_note',
                    'clock_out_note',
                    'ip_address',
                    DB::raw('DATE(clock_in_time) as date'),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'es.name as shift_name',
                    'clock_in_location',
                    'clock_out_location',
                ])->groupBy('essentials_attendances.id');

            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $permitted_locations_array = [];

                foreach ($permitted_locations as $loc_id) {
                    $permitted_locations_array[] = 'location.' . $loc_id;
                }
                $permission_ids = Permission::whereIn('name', $permitted_locations_array)
                    ->pluck('id');

                $attendance->join('model_has_permissions as mhp', 'mhp.model_id', '=', 'u.id')->whereIn('mhp.permission_id', $permission_ids);
            }

            if (!empty(request()->input('employee_id'))) {
                $attendance->where('essentials_attendances.user_id', request()->input('employee_id'));
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $attendance->whereDate('clock_in_time', '>=', $start)
                    ->whereDate('clock_in_time', '<=', $end);
            }

            if (!$can_crud_all_attendance && $can_view_own_attendance) {
                $attendance->where('essentials_attendances.user_id', auth()->user()->id);
            }

            return Datatables::of($attendance)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin,  $can_edit_all_attendance,   $can_delete_all_attendance) {
                        $html = '';
                        if ($is_admin || $can_edit_all_attendance) {
                            $html .= '<a href="{{action(\'\Modules\Essentials\Http\Controllers\AttendanceController@edit\', [$row->id])}}  " class="btn btn-xs btn-primary btn-modal" data-container="#edit_attendance_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                            &nbsp;';
                        }
                        if ($is_admin || $can_delete_all_attendance) {
                            $html .= '<button class="btn btn-xs btn-danger delete-attendance" data-href="{{action(\'\Modules\Essentials\Http\Controllers\AttendanceController@destroy\', [$row->id])}}"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }
                        return $html;
                    }
                    // '<button data-href="{{action(\'\Modules\Essentials\Http\Controllers\AttendanceController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container="#edit_attendance_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    //     <button class="btn btn-xs btn-danger delete-attendance" data-href="{{action(\'\Modules\Essentials\Http\Controllers\AttendanceController@destroy\', [$id])}}"><i class="fa fa-trash"></i> @lang("messages.delete")</button>
                    //     '
                )
                ->addColumn(
                    'status',
                    function ($row) use ($statuses) {
                        return $statuses[$row->status] ?? '';
                    }
                )

                ->editColumn('work_duration', function ($row) {
                    $clock_in = \Carbon::parse($row->clock_in_time);
                    if (!empty($row->clock_out_time)) {
                        $clock_out = \Carbon::parse($row->clock_out_time);
                    } else {
                        $clock_out = \Carbon::now();
                    }

                    $html = $clock_in->diffForHumans($clock_out, true, true, 2);

                    return $html;
                })
                ->editColumn('clock_in', function ($row) {
                    $html = $this->moduleUtil->format_date($row->clock_in_time, true);
                    if (!empty($row->clock_in_location)) {
                        $html .= '<br>' . $row->clock_in_location . '<br>';
                    }

                    if (!empty($row->clock_in_note)) {
                        $html .= '<br>' . $row->clock_in_note . '<br>';
                    }

                    return $html;
                })
                ->editColumn('clock_out', function ($row) {
                    $html = $this->moduleUtil->format_date($row->clock_out_time, true);
                    if (!empty($row->clock_out_location)) {
                        $html .= '<br>' . $row->clock_out_location . '<br>';
                    }

                    if (!empty($row->clock_out_note)) {
                        $html .= '<br>' . $row->clock_out_note . '<br>';
                    }

                    return $html;
                })
                ->editColumn('date', '{{@format_date($date)}}')
                ->rawColumns(['status', 'action', 'clock_in', 'work_duration', 'clock_out'])
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->make(true);
        }

        $settings = request()->session()->get('business.essentials_settings');
        $settings = !empty($settings) ? json_decode($settings, true) : [];

        $is_employee_allowed = auth()->user()->can('essentials.allow_users_for_attendance_from_web');
        $clock_in = EssentialsAttendance::where('business_id', $business_id)
            ->where('user_id', auth()->user()->id)
            ->whereNull('clock_out_time')
            ->first();
        $employees = [];
        if ($can_crud_all_attendance) {
            $employees = User::forDropdown($business_id, false);
        }

        $days = $this->moduleUtil->getDays();

        return view('essentials::attendance.index')
            ->with(compact('is_employee_allowed', 'clock_in', 'employees', 'days'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $employees = User::forDropdown($business_id, false);

        return view('essentials::attendance.create')->with(compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_crud_all_attendance = auth()->user()->can('essentials.crud_all_attendance');


        try {
            $attendance = $request->input('attendance');
            $ip_address = $this->moduleUtil->getUserIpAddr();
            if (!empty($attendance)) {
                foreach ($attendance as $user_id => $value) {
                    $data = [
                        'business_id' => $business_id,
                        'user_id' => $user_id,
                    ];

                    if (!empty($value['clock_in_time'])) {
                        $data['clock_in_time'] = $this->moduleUtil->uf_date($value['clock_in_time'], true);
                    }
                    if (!empty($value['id'])) {
                        $data['id'] = $value['id'];
                    }
                    EssentialsAttendance::updateOrCreate(
                        $data,
                        [
                            'clock_out_time' => !empty($value['clock_out_time']) ? $this->moduleUtil->uf_date($value['clock_out_time'], true) : null,
                            'ip_address' => !empty($value['ip_address']) ? $value['ip_address'] : $ip_address,
                            'clock_in_note' => $value['clock_in_note'],
                            'clock_out_note' => $value['clock_out_note'],
                            'essentials_shift_id' => $value['essentials_shift_id'],
                        ]
                    );
                }
            }

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
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_crud_all_attendance = auth()->user()->can('essentials.crud_all_attendance');


        $attendance = EssentialsAttendance::where('business_id', $business_id)
            ->with(['employee'])
            ->find($id);

        return view('essentials::attendance.edit')->with(compact('attendance'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_crud_all_attendance = auth()->user()->can('essentials.crud_all_attendance');


        try {
            $input = $request->only(['clock_in_time', 'clock_out_time', 'ip_address', 'clock_in_note', 'clock_out_note']);

            $input['clock_in_time'] = $this->moduleUtil->uf_date($input['clock_in_time'], true);
            $input['clock_out_time'] = !empty($input['clock_out_time']) ? $this->moduleUtil->uf_date($input['clock_out_time'], true) : null;

            $attendance = EssentialsAttendance::where('business_id', $business_id)
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
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_crud_all_attendance = auth()->user()->can('essentials.crud_all_attendance');

        if (request()->ajax()) {
            try {
                EssentialsAttendance::where('business_id', $business_id)->where('id', $id)->delete();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.deleted_success'),
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

    /**
     * Clock in / Clock out the logged in user.
     *
     * @return Response
     */
    public function clockInClockOut(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');



        //Check if employees allowed to add their own attendance
        $settings = request()->session()->get('business.essentials_settings');
        $settings = !empty($settings) ? json_decode($settings, true) : [];
        if (!auth()->user()->can('essentials.allow_users_for_attendance_from_web')) {
            return [
                'success' => false,
                'msg' => __('essentials::lang.not_allowed'),
            ];
        } elseif ((!empty($settings['is_location_required']) && $settings['is_location_required']) && empty($request->input('clock_in_out_location'))) {
            return [
                'success' => false,
                'msg' => __('essentials::lang.you_must_enable_location'),
            ];
        }

        try {
            $type = $request->input('type');

            if ($type == 'clock_in') {
                $data = [
                    'business_id' => $business_id,
                    'user_id' => auth()->user()->id,
                    'clock_in_time' => \Carbon::now(),
                    'clock_in_note' => $request->input('clock_in_note'),
                    'ip_address' => $this->moduleUtil->getUserIpAddr(),
                    'clock_in_location' => $request->input('clock_in_out_location'),
                ];

                $output = $this->essentialsUtil->clockin($data, $settings);
            } elseif ($type == 'clock_out') {
                $data = [
                    'business_id' => $business_id,
                    'user_id' => auth()->user()->id,
                    'clock_out_time' => \Carbon::now(),
                    'clock_out_note' => $request->input('clock_out_note'),
                    'clock_out_location' => $request->input('clock_in_out_location'),
                ];

                $output = $this->essentialsUtil->clockout($data, $settings);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
                'type' => $type,
            ];
        }

        return $output;
    }

    /**
     * Function to get attendance summary of a user
     *
     * @return Response
     */
    public function getUserAttendanceSummary()
    {
        $business_id = request()->session()->get('user.business_id');



        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_id = $is_admin ? request()->input('user_id') : auth()->user()->id;

        if (empty($user_id)) {
            return '';
        }

        $start_date = !empty(request()->start_date) ? request()->start_date : null;
        $end_date = !empty(request()->end_date) ? request()->end_date : null;

        $total_work_duration = $this->essentialsUtil->getTotalWorkDuration('hour', $user_id, $business_id, $start_date, $end_date);

        return $total_work_duration;
    }

    /**
     * Function to validate clock in and clock out time
     *
     * @return string
     */
    public function validateClockInClockOut(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_ids = explode(',', $request->input('user_ids'));
        $clock_in_time = $request->input('clock_in_time');
        $clock_out_time = $request->input('clock_out_time');
        $attendance_id = $request->input('attendance_id');

        $is_valid = 'true';
        if (!empty($user_ids)) {

            //Check if clock in time falls under any existing attendance range
            $is_clock_in_exists = false;
            if (!empty($clock_in_time)) {
                $clock_in_time = $this->essentialsUtil->uf_date($clock_in_time, true);

                $is_clock_in_exists = EssentialsAttendance::where('business_id', $business_id)
                    ->where('id', '!=', $attendance_id)
                    ->whereIn('user_id', $user_ids)
                    ->where('clock_in_time', '<', $clock_in_time)
                    ->where('clock_out_time', '>', $clock_in_time)
                    ->exists();
            }

            //Check if clock out time falls under any existing attendance range
            $is_clock_out_exists = false;
            if (!empty($clock_out_time)) {
                $clock_out_time = $this->essentialsUtil->uf_date($clock_out_time, true);

                $is_clock_out_exists = EssentialsAttendance::where('business_id', $business_id)
                    ->where('id', '!=', $attendance_id)
                    ->whereIn('user_id', $user_ids)
                    ->where('clock_in_time', '<', $clock_out_time)
                    ->where('clock_out_time', '>', $clock_out_time)
                    ->exists();
            }

            if ($is_clock_in_exists || $is_clock_out_exists) {
                $is_valid = 'false';
            }
        }

        return $is_valid;
    }

    /**
     * Get attendance summary by shift
     */
    public function getAttendanceByShift()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_crud_attendance_by_shift = auth()->user()->can('essentials.crud_attendance_by_shift');
        // $can_add_attendance_by_shift= auth()->user()->can('essentials.add_attendance_by_shift');
        // $can_edit_attendance_by_shift= auth()->user()->can('essentials.edit_attendance_by_shift');

        $date = $this->moduleUtil->uf_date(request()->input('date'));

        $attendance_data = EssentialsAttendance::where('business_id', $business_id)
            ->whereDate('clock_in_time', $date)
            ->whereNotNull('essentials_shift_id')
            ->with(['shift', 'shift.user_shifts', 'shift.user_shifts.user', 'employee'])
            ->get();
        $attendance_by_shift = [];
        $date_obj = \Carbon::parse($date);
        foreach ($attendance_data as $data) {
            if (empty($attendance_by_shift[$data->essentials_shift_id])) {
                //Calculate total users in the shift
                $total_users = 0;
                $all_users = [];
                foreach ($data->shift->user_shifts as $user_shift) {
                    if (!empty($user_shift->start_date) && !empty($user_shift->end_date) && $date_obj->between(\Carbon::parse($user_shift->start_date), \Carbon::parse($user_shift->end_date))) {
                        $total_users++;
                        $all_users[] = $user_shift->user->user_full_name;
                    }
                }
                $attendance_by_shift[$data->essentials_shift_id] = [
                    'present' => 1,
                    'shift' => $data->shift->name,
                    'total' => $total_users,
                    'present_users' => [$data->employee->user_full_name],
                    'all_users' => $all_users,
                ];
            } else {
                if (!in_array($data->employee->user_full_name, $attendance_by_shift[$data->essentials_shift_id]['present_users'])) {
                    $attendance_by_shift[$data->essentials_shift_id]['present']++;
                    $attendance_by_shift[$data->essentials_shift_id]['present_users'][] = $data->employee->user_full_name;
                }
            }
        }

        return view('essentials::attendance.attendance_by_shift_data')->with(compact('attendance_by_shift'));
    }

    /**
     * Get attendance summary by date
     */
    public function getAttendanceByDate()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $start_date = request()->input('start_date');
        $end_date = request()->input('end_date');

        $attendance_data = EssentialsAttendance::where('business_id', $business_id)
            ->whereDate('clock_in_time', '>=', $start_date)
            ->whereDate('clock_in_time', '<=', $end_date)
            ->select(
                'essentials_attendances.*',
                DB::raw('COUNT(DISTINCT essentials_attendances.user_id) as total_present'),
                DB::raw('CAST(clock_in_time AS DATE) as clock_in_date')
            )
            ->groupBy(DB::raw('CAST(clock_in_time AS DATE)'))
            ->get();

        $all_users = User::where('business_id', $business_id)
            ->user()
            ->count();

        $attendance_by_date = [];
        foreach ($attendance_data as $data) {
            $total_present = !empty($data->total_present) ? $data->total_present : 0;
            $attendance_by_date[] = [
                'present' => $total_present,
                'absent' => $all_users - $total_present,
                'date' => $data->clock_in_date,
            ];
        }

        return view('essentials::attendance.attendance_by_date_data')->with(compact('attendance_by_date'));
    }

    /**
     * Function to import attendance.
     *
     * @param  Request  $request
     * @return Response
     */
    public function importAttendance(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $notAllowed = $this->moduleUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);

            if ($request->hasFile('attendance')) {
                $file = $request->file('attendance');
                $parsed_array = Excel::toArray([], $file);
                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $formated_data = [];

                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();
                $ip_address = $this->moduleUtil->getUserIpAddr();
                foreach ($imported_data as $key => $value) {
                    $row_no = $key + 1;
                    $temp = [];

                    //Add user
                    if (!empty($value[0])) {
                        $email = trim($value[0]);
                        $user = User::where('business_id', $business_id)->where('email', $email)->first();
                        if (!empty($user)) {
                            $temp['user_id'] = $user->id;
                        } else {
                            $is_valid = false;
                            $error_msg = "User not found in row no. $row_no";
                            break;
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = "Email is required in row no. $row_no";
                        break;
                    }

                    //clockin time
                    if (!empty($value[1])) {
                        $temp['clock_in_time'] = trim($value[1]);
                    } else {
                        $is_valid = false;
                        $error_msg = "Clock in time is required in row no. $row_no";
                        break;
                    }
                    $temp['clock_out_time'] = !empty($value[2]) ? trim($value[2]) : null;

                    //Add shift
                    if (!empty($value[3])) {
                        $shift_name = trim($value[3]);
                        $shift = Shift::where('business_id', $business_id)->where('name', $shift_name)->first();
                        if (!empty($shift)) {
                            $temp['essentials_shift_id'] = $shift->id;
                        } else {
                            $is_valid = false;
                            $error_msg = "Shift not found in row no. $row_no";
                            break;
                        }
                    }

                    $temp['clock_in_note'] = !empty($value[4]) ? trim($value[4]) : null;
                    $temp['clock_out_note'] = !empty($value[5]) ? trim($value[5]) : null;
                    $temp['ip_address'] = !empty($value[6]) ? trim($value[6]) : $ip_address;
                    $temp['business_id'] = $business_id;
                    $formated_data[] = $temp;
                }

                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {
                    EssentialsAttendance::insert($formated_data);
                }

                $output = [
                    'success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                ];

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return redirect()->back()->with('notification', $output);
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Adds attendance row for an employee on add latest attendance form
     *
     * @param  int  $user_id
     * @return Response
     */
    public function getAttendanceRow($user_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $user = User::where('business_id', $business_id)
            ->findOrFail($user_id);

        $attendance = EssentialsAttendance::where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->first();

        $shifts = Shift::join('essentials_user_shifts as eus', 'eus.essentials_shift_id', '=', 'essentials_shifts.id')
            ->where('essentials_shifts.business_id', $business_id)
            ->where('eus.user_id', $user_id)
            ->where('eus.start_date', '<=', \Carbon::now()->format('Y-m-d'))
            ->pluck('essentials_shifts.name', 'essentials_shifts.id');

        return view('essentials::attendance.attendance_row')->with(compact('attendance', 'shifts', 'user'));
    }
}
