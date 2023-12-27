<?php

namespace Modules\Essentials\Http\Controllers\Api;

use App\Business;
use App\BusinessLocation;
use App\BusinessLocationPolygonMarker;
use App\Category;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\ToDo;
use Modules\Essentials\Utils\EssentialsUtil;

class ApiEssentialsController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;
    protected $essentialsUtil;
    protected $commonUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, EssentialsUtil $essentialsUtil, Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getEditUserInfo()
    {

        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();


            $user = User::where('id', $user->id)->first();
            $res = [
                'first_name' =>   $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function updateUserInfo(Request $request)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            if ($request->otp == '1111') {
                $user = User::where('id', $user->id)->first();
                $res = [
                    'first_name' =>  $request->first_name ?? $user->first_name,
                    'mid_name' => $request->mid_name ?? $user->mid_name,
                    'last_name' => $request->last_name ?? $user->last_name,
                    'email' => $request->email ?? $user->email,
                    'contact_number' => $request->contact_number ?? $user->contact_number,
                ];

                $user->update($res);
                return new CommonResource($res);
            } else {
                throw new \Exception("The provided OTP is incorrect.");
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function resetPassword(Request $request)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
          //  $user = Auth::user();

            if ($request->otp == '1111') {
                $user = User::where('id', $request->user_id)->first();
                $user->update(['password' => Hash::make($request->new_password)]);
                return new CommonResource(['msg' => 'تم تغيير كلمة المرور بنجاح']);
            } else {
                throw new \Exception("The provided OTP is incorrect.");
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public  function changeToDoStatus(Request $request, $id)
    {
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $toDo = ToDo::find($id);
            $status = ['new', 'in_progress', 'on_hold', 'completed',];
            $toDo->update(['status' => $status[$request->status]]);
            return new CommonResource(['msg' => 'تم تغيير حالة المهمة بنجاح']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function getPayrollDetails()
    {
        try {
            $user = Auth::user();
            $business_id = $user->business_id;



            $payrolls = Transaction::where('transactions.business_id', $business_id)
                ->where('type', 'payroll')->where('transactions.expense_for', $user->id)
                ->join('users as u', 'u.id', '=', 'transactions.expense_for')
                ->leftJoin('categories as dept', 'u.essentials_department_id', '=', 'dept.id')
                ->leftJoin('categories as dsgn', 'u.essentials_designation_id', '=', 'dsgn.id')
                ->leftJoin('essentials_payroll_group_transactions as epgt', 'transactions.id', '=', 'epgt.transaction_id')
                ->leftJoin('essentials_payroll_groups as epg', 'epgt.payroll_group_id', '=', 'epg.id')
                ->select([
                    'transactions.id as id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'final_total',
                    'transaction_date',
                    'ref_no',
                    'transactions.payment_status',
                    'dept.name as department',
                    'dsgn.name as designation',
                    'epgt.payroll_group_id',
                ])->get();
            $res = [];
            foreach ($payrolls as $payroll) {
                $payrollId = $payroll->id;
                $query = Transaction::where('business_id', $business_id)
                    ->with(['transaction_for', 'payment_lines']);


                $payroll = $query->findOrFail($payrollId);

                $transaction_date = Carbon::parse($payroll->transaction_date);

                $department = Category::where('category_type', 'hrm_department')
                    ->find($payroll->transaction_for->essentials_department_id);

                $designation = Category::where('category_type', 'hrm_designation')
                    ->find($payroll->transaction_for->essentials_designation_id);

                $location = BusinessLocation::where('business_id', $business_id)
                    ->find($payroll->transaction_for->location_id);

                $month_name = $transaction_date->format('F');
                $year = $transaction_date->format('Y');
                $allowances = !empty($payroll->essentials_allowances) ? json_decode($payroll->essentials_allowances, true) : [];
                $deductions = !empty($payroll->essentials_deductions) ? json_decode($payroll->essentials_deductions, true) : [];
                $bank_details = json_decode($payroll->transaction_for->bank_details, true);
                $payment_types = $this->moduleUtil->payment_types();
                $final_total_in_words = $this->commonUtil->numToIndianFormat($payroll->final_total);

                $start_of_month = Carbon::parse($payroll->transaction_date);
                $end_of_month = Carbon::parse($payroll->transaction_date)->endOfMonth();

                $leaves = EssentialsLeave::where('business_id', $business_id)
                    ->where('user_id', $payroll->transaction_for->id)
                    ->whereDate('start_date', '>=', $start_of_month)
                    ->whereDate('end_date', '<=', $end_of_month)
                    ->get();

                $total_leaves = 0;
                $days_in_a_month = Carbon::parse($start_of_month)->daysInMonth;
                foreach ($leaves as $key => $leave) {
                    $start_date = Carbon::parse($leave->start_date);
                    $end_date = Carbon::parse($leave->end_date);

                    $diff = $start_date->diffInDays($end_date);
                    $diff += 1;
                    $total_leaves += $diff;
                }

                $total_days_present = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee(
                    $business_id,
                    $payroll->transaction_for->id,
                    $start_of_month->format('Y-m-d'),
                    $end_of_month->format('Y-m-d')
                );

                $total_work_duration = $this->essentialsUtil->getTotalWorkDuration(
                    'hour',
                    $payroll->transaction_for->id,
                    $business_id,
                    $start_of_month->format('Y-m-d'),
                    $end_of_month->format('Y-m-d')
                );

                $res[] = [
                    'payroll' => $payroll,
                    'month_name' => $month_name,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'year' => $year,
                    'payment_types' => $payment_types,
                    'bank_details' => $bank_details,
                    'designation' => $designation,
                    'department' => $department,
                    'final_total_in_words' => $final_total_in_words,
                    'total_leaves' => $total_leaves,
                    'days_in_a_month' => $days_in_a_month,
                    'total_work_duration' => $total_work_duration,
                    'location' => $location,
                    'total_days_present' => $total_days_present
                ];
            }
            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function clockin(Request $request)
    {
        // modified to not need a user_id, it can depend on the token
        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
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
            // if (!empty($settings['is_location_required'])) {
            $long = $request->input('longitude');
            $lat = $request->input('latitude');

            if (empty($long) || empty($lat)) {
                throw new \Exception('Latitude and longitude are required');
            }

            $response = $essentialsUtil->getLocationFromCoordinates($lat, $long);

            if (!empty($response)) {
                $data['clock_in_location'] = $response;
            }
            $data['clock_in_lat'] = $lat;
            $data['clock_in_lng'] = $long;
            //  }

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
}
