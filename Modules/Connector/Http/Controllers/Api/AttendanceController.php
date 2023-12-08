<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Business;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsAttendance;

/**
 * @group Attendance management
 * @authenticated
 *
 * APIs for managing attendance
 */
class AttendanceController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Get Attendance
     *
     * @urlParam user_id required id of the user Example: 1
     * @response {
            "data": {
                "id": 4,
                "user_id": 1,
                "business_id": 1,
                "clock_in_time": "2020-09-12 13:13:00",
                "clock_out_time": "2020-09-12 13:15:00",
                "essentials_shift_id": 3,
                "ip_address": null,
                "clock_in_note": "test clock in from api",
                "clock_out_note": "test clock out from api",
                "created_at": "2020-09-12 13:14:39",
                "updated_at": "2020-09-12 13:15:39"
            }
        }
     */
    public function getAttendance($user_id)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $business_id = $user->business_id;

        $attendance = \Modules\Essentials\Entities\EssentialsAttendance::where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->orderBy('clock_in_time', 'desc')
            ->first();

        return new CommonResource($attendance);
    }

    public function getAttendanceByDate()
    {

        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $business_id = $user->business_id;
        $business = Business::where('id', $business_id)->first();
        $year = request()->year;
        $month = request()->month;

        $attendanceList = EssentialsAttendance::where([['user_id', '=', $user->id], ['business_id', '=', $business_id]])->with('shift')->get();
        $essentials_settings = json_decode($business->essentials_settings, true);
        $grace_before_checkin = $essentials_settings['grace_before_checkin'];
        $grace_after_checkin = $essentials_settings['grace_after_checkout'];
        $grace_before_checkout = $essentials_settings['grace_before_checkout'];
        $grace_after_checkout = $essentials_settings['grace_after_checkout'];
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();


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
                $status = 3;

                foreach ($attendanceList as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->clock_in_time)->toDateString();
                    $clock_in_time = null;
                    $clock_out_time = null;
                    if ($day->toDateString() == $attendanceDate) {
                        $start_time = Carbon::parse($attendance->shift->start_time);
                        $clock_in_time = Carbon::parse($attendance->clock_in_time);
                        $clock_out_time = Carbon::parse($attendance->clock_out_time);
                        $checkin_start_range = $start_time->copy()->subMinutes($grace_before_checkin);
                        $checkin_end_range = $start_time->copy()->addMinutes($grace_after_checkin);

                        if ($clock_in_time->between($checkin_start_range, $checkin_end_range)) {
                            $status = 1;
                        } elseif ($clock_in_time->gt($checkin_end_range)) {
                            $status = 2;
                        }
                        break;
                    }
                }
                if ($status == 1) {
                    $attended += 1;
                } elseif ($status == 2) {
                    $late += 1;
                } elseif ($status == 3) {
                    $absent += 1;
                }
            }
            $daysBefore[] = [
                'number_in_month' => $day->day,
                'number_in_week' => ($day->dayOfWeek + 1) % 8 ,
                'month' => $month == 1 ? 12 : $month - 1,
                'year' => $year,
                'name' => $day->format('l'), // Full day name (Sunday, Monday, ...)
                'status' => $status,
                'start_time' => $clock_in_time ? Carbon::parse($clock_in_time)->format('h:i A') : null,
                'end_time' => $clock_out_time ? Carbon::parse($clock_out_time)->format('h:i A') : null,
            ];
            $day->addDay();
        }

        //days
        $days = [];
        $attended = 0;
        $late = 0;
        $absent = 0;
        for ($day = $firstDayOfMonth; $day->lte($lastDayOfMonth); $day->addDay()) {
            $clock_in_time = null;
            $clock_out_time = null;
            if ($day->isFuture()) {
                $status = 0;
            } else {
                $status = 3;

                foreach ($attendanceList as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->clock_in_time)->toDateString();
                    $clock_in_time = null;
                    $clock_out_time = null;
                    if ($day->toDateString() == $attendanceDate) {
                        $start_time = Carbon::parse($attendance->shift->start_time);
                        $clock_in_time = Carbon::parse($attendance->clock_in_time);
                        $clock_out_time = Carbon::parse($attendance->clock_out_time);
                        $checkin_start_range = $start_time->copy()->subMinutes($grace_before_checkin);
                        $checkin_end_range = $start_time->copy()->addMinutes($grace_after_checkin);

                        if ($clock_in_time->between($checkin_start_range, $checkin_end_range)) {
                            $status = 1;
                        } elseif ($clock_in_time->gt($checkin_end_range)) {
                            $status = 2;
                        }
                        break;
                    }
                }
                if ($status == 1) {
                    $attended += 1;
                } elseif ($status == 2) {
                    $late += 1;
                } elseif ($status == 3) {
                    $absent += 1;
                }
            }

            $days[] = [
                'number_in_month' => $day->day,
                'number_in_week' => ($day->dayOfWeek + 1) % 8 ,
                'month' => (int)$month,
                'year' => $year,
                'name' => $day->format('l'), // Full day name (Sunday, Monday, ...)
                'status' => $status,
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
                $status = 3;

                foreach ($attendanceList as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->clock_in_time)->toDateString();
                    $clock_in_time = null;
                    $clock_out_time = null;
                    if ($day->toDateString() == $attendanceDate) {
                        $start_time = Carbon::parse($attendance->shift->start_time);
                        $clock_in_time = Carbon::parse($attendance->clock_in_time);
                        $clock_out_time = Carbon::parse($attendance->clock_out_time);
                        $checkin_start_range = $start_time->copy()->subMinutes($grace_before_checkin);
                        $checkin_end_range = $start_time->copy()->addMinutes($grace_after_checkin);

                        if ($clock_in_time->between($checkin_start_range, $checkin_end_range)) {
                            $status = 1;
                        } elseif ($clock_in_time->gt($checkin_end_range)) {
                            $status = 2;
                        }
                        break;
                    }
                }
                if ($status == 1) {
                    $attended += 1;
                } elseif ($status == 2) {
                    $late += 1;
                } elseif ($status == 3) {
                    $absent += 1;
                }
            }

            $daysAfter[] = [
                'number_in_month' => $day->day,
                'number_in_week' => ($day->dayOfWeek + 1) % 8 ,
                'month' => $month == 12 ? 1 : $month + 1,
                'year' => $year,
                'name' => $day->format('l'), // Full day name (Sunday, Monday, ...)
                'status' => $status,
                'start_time' => $clock_in_time ? Carbon::parse($clock_in_time)->format('h:i A') : null,
                'end_time' => $clock_out_time ? Carbon::parse($clock_out_time)->format('h:i A') : null,
            ];
            $day->addDay();
        }


        $res = [
            'attended' => $attended,
            'late' => $late,
            'absent' => $absent,
            'days_before' => $daysBefore,
            'days' => $days,
            'days_after' => $daysAfter,
        ];
        return new CommonResource($res);
    }



    /**
     * Clock In
     *
     * [User must have "essentials.allow_users_for_attendance_from_api" permission to Clock in]
     *
     * @bodyParam user_id integer required id of the user Example: 1
     * @bodyParam clock_in_time string Clock in time.If not given current date time will be used Fromat: Y-m-d H:i:s Example:2000-06-13 13:13:00
     * @bodyParam clock_in_note string Clock in note.
     * @bodyParam ip_address string IP address.
     * @bodyParam latitude string Latitude of the clock in location.
     * @bodyParam longitude string Longitude of the clock in location.
     *
     * @response {
         "success":true,
         "msg":"Clocked In successfully",
         "type":"clock_in"
     }
     */
    public function clockin(Request $request)
    {
        // modified to not need a user_id, it can depend on the token
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $business = Business::findOrFail($business_id);
            $settings = $business->essentials_settings;
            $settings = !empty($settings) ? json_decode($settings, true) : [];
            $essentialsUtil = new \Modules\Essentials\Utils\EssentialsUtil;

            $data = [
                'business_id' => $business_id,
                // 'user_id' => $request->input('user_id'),
                'clock_in_time' => empty($request->input('clock_in_time')) ? \Carbon::now() : $request->input('clock_in_time'),
                'clock_in_note' => $request->input('clock_in_note'),
                'ip_address' => $request->input('ip_address'),
            ];
            $data['user_id'] = $user->id;
            if (!empty($settings['is_location_required'])) {
                $long = $request->input('longitude');
                $lat = $request->input('latitude');

                if (empty($long) || empty($lat)) {
                    throw new \Exception('Latitude and longitude are required');
                }

                $response = $essentialsUtil->getLocationFromCoordinates($lat, $long);

                if (!empty($response)) {
                    $data['clock_in_location'] = $response;
                }
            }

            $output = $essentialsUtil->clockin($data, $settings);

            if ($output['success']) {
                return $this->respond($output);
            } else {
                return $this->otherExceptions($output['msg']);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    /**
     * Clock Out
     *
     * [User must have "essentials.allow_users_for_attendance_from_api" permission to Clock out]
     *
     * @bodyParam user_id integer required id of the user Example: 1
     * @bodyParam clock_out_time string Clock out time.If not given current date time will be used Fromat: Y-m-d H:i:s Example:2000-06-13 13:13:00
     * @bodyParam clock_out_note string Clock out note.
     * @bodyParam latitude string Latitude of the clock out location.
     * @bodyParam longitude string Longitude of the clock out location.
     *
     * @response {
         "success":true,
         "msg":"Clocked Out successfully",
         "type":"clock_out"
     }
     */
    public function clockout(Request $request)
    {
        // modified to not need a user_id, it can depend on the token
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $business = Business::findOrFail($business_id);
            $settings = $business->essentials_settings;
            $settings = !empty($settings) ? json_decode($settings, true) : [];

            $data = [
                'business_id' => $business_id,
                //     'user_id' => $request->input('user_id'),
                'clock_out_time' => empty($request->input('clock_out_time')) ? \Carbon::now() : $request->input('clock_out_time'),
                'clock_out_note' => $request->input('clock_out_note'),
            ];
            $data['user_id'] = $user->id;
            $essentialsUtil = new \Modules\Essentials\Utils\EssentialsUtil;

            if (!empty($settings['is_location_required'])) {
                $long = $request->input('longitude');
                $lat = $request->input('latitude');

                if (empty($long) || empty($lat)) {
                    throw new \Exception('Latitude and longitude are required');
                }

                $response = $essentialsUtil->getLocationFromCoordinates($lat, $long);

                if (!empty($response)) {
                    $data['clock_out_location'] = $response;
                }
            }

            $output = $essentialsUtil->clockout($data, $settings);

            if ($output['success']) {
                return $this->respond($output);
            } else {
                return $this->otherExceptions($output['msg']);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    /**
     * List Holidays
     *
     * @queryParam location_id id of the location Example: 1
     * @queryParam start_date format:Y-m-d Example: 2020-06-25
     * @queryParam end_date format:Y-m-d Example: 2020-06-25
     *
     * @response {
            "data": [
                {
                    "id": 2,
                    "name": "Independence Day",
                    "start_date": "2020-08-15",
                    "end_date": "2020-09-15",
                    "business_id": 1,
                    "location_id": null,
                    "note": "test holiday",
                    "created_at": "2020-09-15 11:25:56",
                    "updated_at": "2020-09-15 11:25:56"
                }
            ]
        }
     */
    public function getHolidays()
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $business_id = $user->business_id;

        $query = \Modules\Essentials\Entities\EssentialsHoliday::where('business_id', $business_id);

        $permitted_locations = $user->permitted_locations($business_id);
        if ($permitted_locations != 'all') {
            $query->where(function ($q) use ($permitted_locations) {
                $q->whereIn('location_id', $permitted_locations)
                    ->orWhereNull('location_id');
            });
        }

        if (!empty(request()->input('location_id'))) {
            $query->where('location_id', request()->input('location_id'));
        }

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end = request()->end_date;
            $query->whereDate('start_date', '>=', $start)
                ->whereDate('start_date', '<=', $end);
        }
        $holidays = $query->get();

        return new CommonResource($holidays);
    }
}
