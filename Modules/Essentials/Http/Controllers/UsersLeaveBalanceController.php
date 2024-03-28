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

class UsersLeaveBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index()
    {
        $leaveTypes = EssentialsLeaveType::all();
        $userTypes = ['employee', 'worker', 'manager'];
        if (request()->ajax()) {
            $users = User::whereIn('user_type', $userTypes)
                ->orderBy('id')
                ->with('leaveBalances')
                ->get();

            $data = $users->map(function ($user) use ($leaveTypes) {

                $user->name = trim($user->first_name . ' ' . ($user->last_name ?? ''));


                foreach ($leaveTypes as $type) {

                    $propertyName = Str::slug($type->leave_type, '_');
                    $user->{$propertyName} = $user->leaveBalances->where('essentials_leave_type_id', $type->id)->first()->amount ?? '-';
                }

                return $user;
            });

            return Datatables::of($data)->make(true);
        }

        return view('essentials::leave_balance.index', compact('leaveTypes'));
    }


    public function populateLeaveBalances()
    {
        DB::beginTransaction();
        try {
            $userTypes = ['employee', 'worker', 'manager'];
            $leaveTypes = EssentialsLeaveType::where('leave_type', 'not like', '%سنوية%')->get();
            $users = User::whereIn('user_type', $userTypes)->get();

            foreach ($users as $user) {

                foreach ($leaveTypes as $leaveType) {
                    $amount = is_null($leaveType->max_leave_count) ? $leaveType->duration : $leaveType->duration * $leaveType->max_leave_count;
                    UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'essentials_leave_type_id' => $leaveType->id
                        ],
                        ['amount' => $amount]
                    );
                }


                if ($user->max_anuual_leave_days == 21) {
                    UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'essentials_leave_type_id' => 1
                        ],
                        ['amount' => 21]
                    );
                } elseif (in_array($user->max_anuual_leave_days, [30, 31])) {
                    UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'essentials_leave_type_id' => 9
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
