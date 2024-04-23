<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\UserLeaveBalance;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Utils\ModuleUtil;
use Illuminate\Support\Carbon;

class UsersLeaveBalanceController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    // public function index()
    // {

    //     $leaveTypes = EssentialsLeaveType::all();
    //     $userTypes = ['employee', 'worker', 'manager'];
    //     if (request()->ajax()) {
    //         $users = User::whereIn('user_type', $userTypes)
    //             ->orderBy('id')
    //             ->with('leaveBalances')
    //             ->get();

    //         $data = $users->map(function ($user) use ($leaveTypes) {

    //             $user->name = trim($user->first_name . ' ' . ($user->last_name ?? ''));


    //             foreach ($leaveTypes as $type) {

    //                 $propertyName = Str::slug($type->leave_type, '_');
    //                 $user->{$propertyName} = $user->leaveBalances->where('essentials_leave_type_id', $type->id)->first()->amount ?? '-';
    //             }

    //             return $user;
    //         });

    //         return Datatables::of($data)->make(true);
    //     }

    //     return view('essentials::leave_balance.index', compact('leaveTypes'));
    // }
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $leaveTypes = EssentialsLeaveType::all();
        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $selectedUserIds = $request->input('user_ids', []);
        $selectedUsers = User::whereIn('id', $selectedUserIds)
            ->with('leaveBalances')
            ->get();

        if ($request->ajax()) {



            $data = $selectedUsers->map(function ($user) use ($leaveTypes) {

                $user->name = trim($user->first_name . ' ' . ($user->last_name ?? ''));


                foreach ($leaveTypes as $type) {

                    $propertyName = Str::slug($type->leave_type, '_');
                    $user->{$propertyName} = $user->leaveBalances->where('essentials_leave_type_id', $type->id)->first()->amount ?? '-';
                }

                return $user;
            });



            return response()->json([
                'data' => $data,
                'leaveTypes' => $leaveTypes,
            ]);
        }

        return view('essentials::leave_balance.index', compact('leaveTypes', 'users'));
    }



    public function populateLeaveBalances()
    {
        DB::beginTransaction();
        try {
            $userTypes = ['employee', 'worker', 'manager'];
            $leaveTypes = EssentialsLeaveType::where('leave_type', 'not like', '%سنوية%')->get();
            $users = User::whereIn('user_type', $userTypes)->with('appointment')->get();
            $today = now();

            $annualLeaveTypeIdFor21 = EssentialsLeaveType::where('leave_type', 'like', '%سنوية_21%')
                ->first()
                ->id ?? null;
            $annualLeaveDueDate21 = $annualLeaveTypeIdFor21 ? EssentialsLeaveType::find($annualLeaveTypeIdFor21)->due_date ?? 0 : 0;

            $annualLeaveTypeIdFor30 = EssentialsLeaveType::where('leave_type', 'like', '%سنوية_30%')
                ->first()
                ->id ?? null;
            $annualLeaveDueDate30 = $annualLeaveTypeIdFor30 ? EssentialsLeaveType::find($annualLeaveTypeIdFor30)->due_date ?? 0 : 0;

            foreach ($users as $user) {
                $appointmentStartDate = optional($user->appointment)->start_from;

                if (is_null($appointmentStartDate) || now()->lt(new Carbon($appointmentStartDate))) {
                    continue;
                }

                $appointmentStartCarbon = new Carbon($appointmentStartDate);
                $monthsDifference = $appointmentStartCarbon ? now()->diffInMonths($appointmentStartCarbon) : 0;


                foreach ($leaveTypes as $leaveType) {
                    if (
                        $monthsDifference < $leaveType->due_date ||
                        ($user->gender != $leaveType->gender && $leaveType->gender != 'both')
                    ) {
                        continue;
                    }

                    $amount = is_null($leaveType->max_leave_count)
                        ? $leaveType->duration
                        : $leaveType->duration * $leaveType->max_leave_count;

                    UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'essentials_leave_type_id' => $leaveType->id,
                        ],
                        ['amount' => $amount]
                    );
                }

                if ($user->max_anuual_leave_days == 21 && $monthsDifference > $annualLeaveDueDate21 && $annualLeaveTypeIdFor21) {
                    UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'essentials_leave_type_id' => $annualLeaveTypeIdFor21,
                        ],
                        ['amount' => 21]
                    );
                } elseif (in_array($user->max_anuual_leave_days, [30, 31]) && $monthsDifference > $annualLeaveDueDate30 && $annualLeaveTypeIdFor30) {
                    UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'essentials_leave_type_id' => $annualLeaveTypeIdFor30,
                        ],
                        ['amount' => 30]
                    );
                }
            }

            DB::commit();
            return response()->json(['success' => 'Leave balances populated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while populating leave balances.'], 500);
        }
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
