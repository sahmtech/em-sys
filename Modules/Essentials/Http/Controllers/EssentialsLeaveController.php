<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsLeave;

use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use Modules\Essentials\Notifications\LeaveStatusNotification;
use Modules\Essentials\Notifications\NewLeaveNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class EssentialsLeaveController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    protected $leave_statuses;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->leave_statuses = [
            'pending' => [
                'name' => __('lang_v1.pending'),
                'class' => 'bg-yellow',
            ],
            'approved' => [
                'name' => __('essentials::lang.approved'),
                'class' => 'bg-green',
            ],
            'cancelled' => [
                'name' => __('essentials::lang.cancelled'),
                'class' => 'bg-red',
            ],
        ];
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
        $can_crud_all_leave = auth()->user()->can('essentials.crud_all_leave');
        $can_crud_own_leave = auth()->user()->can('essentials.crud_own_leave');

        $can_delete_leave = auth()->user()->can('essentials.delete_leave');
        $can_edit_leave = auth()->user()->can('essentials.edit_leave');
        $can_change_status_leave = auth()->user()->can('essentials.change_status_leave');
        if (!$can_crud_all_leave && !$can_crud_own_leave) {
            //temp  abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $leaves = EssentialsLeave::where('essentials_leaves.business_id', $business_id)
                ->join('users as u', 'u.id', '=', 'essentials_leaves.user_id')
                ->join('essentials_leave_types as lt', 'lt.id', '=', 'essentials_leaves.essentials_leave_type_id')
                ->select([
                    'essentials_leaves.id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'lt.leave_type',
                    'start_date',
                    'end_date',
                    'ref_no',
                    'essentials_leaves.status',
                    'essentials_leaves.business_id',
                    'reason',
                    'status_note',
                ]);

            if (!empty(request()->input('user_id'))) {
                $leaves->where('essentials_leaves.user_id', request()->input('user_id'));
            }

            if (!$can_crud_all_leave && $can_crud_own_leave) {
                $leaves->where('essentials_leaves.user_id', auth()->user()->id);
            }

            if (!empty(request()->input('status'))) {
                $leaves->where('essentials_leaves.status', request()->input('status'));
            }

            if (!empty(request()->input('leave_type'))) {
                $leaves->where('essentials_leaves.essentials_leave_type_id', request()->input('leave_type'));
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $leaves->whereDate('essentials_leaves.start_date', '>=', $start)
                    ->whereDate('essentials_leaves.start_date', '<=', $end);
            }

            return Datatables::of($leaves)

                // ->addColumn(
                //     'leave_type', function ($leave_type) {
                //         return trans("essentials::lang.$leave_type->leave_type");
                //     }
                // )
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_leave, $can_delete_leave) {
                        $html = '';

                        if ($is_admin  || $can_delete_leave) {
                            $html .= '<button class="btn btn-xs btn-danger delete-leave" data-href="' . action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'destroy'], [$row->id]) . '"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        if ($is_admin  || $can_edit_leave) {

                            $html .= '&nbsp;<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal"  data-href="' . action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'activity'], [$row->id]) . '"><i class="fa fa-edit"></i> ' . __('essentials::lang.activity') . '</button>';
                        }
                        return $html;
                    }
                )
                ->editColumn('start_date', function ($row) {
                    $start_date = \Carbon::parse($row->start_date);
                    $end_date = \Carbon::parse($row->end_date);

                    $diff = $start_date->diffInDays($end_date);
                    $diff += 1;
                    $start_date_formated = $this->moduleUtil->format_date($start_date);
                    $end_date_formated = $this->moduleUtil->format_date($end_date);

                    return $start_date_formated . ' - ' . $end_date_formated . ' (' . $diff . \Str::plural(__('lang_v1.day'), $diff) . ')';
                })

                ->editColumn('status', function ($row)  use ($is_admin, $can_change_status_leave) {
                    $status = ' ';
                    if ($is_admin || $can_change_status_leave) {
                        $status = '<span class="label ' . $this->leave_statuses[$row->status]['class'] . '">'
                            . $this->leave_statuses[$row->status]['name'] . '</span>';

                        if (auth()->user()->can('essentials.crud_all_leave') || auth()->user()->can('essentials.approve_leave')) {
                            $status = '<a href="#" class="change_status" data-status_note="' . $row->status_note . '" data-leave-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->leave_statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                        }
                    }


                    return $status;
                })
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        $users = [];
        if ($can_crud_all_leave || auth()->user()->can('essentials.approve_leave')) {
            $query = User::where('business_id', $business_id)->whereIn('user_type', ['employee', 'worker', 'manager']);
            $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
            $users = $all_users->pluck('full_name', 'id');
        }


        $leave_statuses = $this->leave_statuses;

        $leave_types = EssentialsLeaveType::forDropdown($business_id);

        return view('essentials::leave.index')->with(compact('leave_statuses', 'users', 'leave_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    public function getAdmissionDate(Request $request)
    {
        $employeeIds = $request->input('employeeIds');


        $admissionDates = EssentialsAdmissionToWork::where('employee_id', $employeeIds)->pluck('admissions_date')->first();

        if (!empty($admissionDates)) {

            $formattedDate =  $admissionDates;
        } else {
            $formattedDate = 'N/A';
        }
        //dd($formattedDate);

        return $formattedDate;
    }
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $leave_types = EssentialsLeaveType::forDropdown($business_id);
        // dd($leave_types);
        $settings = request()->session()->get('business.essentials_settings');
        $settings = !empty($settings) ? json_decode($settings, true) : [];

        $instructions = !empty($settings['leave_instructions']) ? $settings['leave_instructions'] : '';

        $employees = [];
        $alt_employee = [];
        if (auth()->user()->can('essentials.crud_all_leave')) {
            $query = User::where('business_id', $business_id)->whereIn('user_type', ['employee', 'worker', 'manager']);
            $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
            $employees = $all_users->pluck('full_name', 'id');
            $alt_employees = $all_users->pluck('full_name', 'id');
            //  $employees = User::forDropdown($business_id, false, false, false, true);
            //  $alt_employee=User::forDropdown($business_id, false, false, false, true);
        }
        $travel_ticket_categorie = EssentialsTravelTicketCategorie::forDropdown();

        return view('essentials::leave.create')
            ->with(compact(
                'leave_types',
                'instructions',
                'employees',
                'alt_employees',
                'travel_ticket_categorie'
            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        $can_crud_all_leave = auth()->user()->can('essentials.crud_all_leave');
        $can_crud_own_leave = auth()->user()->can('essentials.crud_own_leave');

        if (!$can_crud_all_leave && !$can_crud_own_leave) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(
                [
                    'essentials_leave_type_id',
                    'start_date',
                    'end_date',
                    'reason',
                    'attachments_path',
                    'travel_destination',
                    'travel_ticket_categorie',
                    'alt_employees',
                    'travel_ticket_categorie',
                    'travel_ticket_categorie_id'

                ]
            );



            if ($request->hasFile('attachments_path')) {
                $file = $request->file('attachments_path');
                $filePath = $file->store('/public');

                $input['attachments_path'] = $filePath;
            } else {
                $input['attachments_path'] = null;
            }

            $input['user_id'] = $request->input('employee_id');
            $user_id =  $input['user_id'];
            $input['Alternative_id'] = $request->input('alt_employee_id');
            $mysql_format = 'Y-m-d';
            $input['business_id'] = $business_id;
            $input['status'] = 'pending';
            $input['start_date'] = \Carbon::parse($input['start_date'])->format($mysql_format);
            $input['end_date'] = \Carbon::parse($input['end_date'])->format($mysql_format);
            DB::beginTransaction();


            if (!empty($request->input('travel_ticket_categorie'))) {
                $travel = new EssentialsEmployeeTravelCategorie();
                $travel->employee_id = $user_id;
                $travel->categorie_id = $request->input('travel_ticket_categorie_id');
                $travel->save();
            }

            if (auth()->user()->can('essentials.crud_all_leave') && !empty($request->input('employees'))) {

                $input['user_id'] = !empty($user_id) ? $user_id : request()->session()->get('user.id');
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('leave');

                if (empty($input['ref_no'])) {
                    $settings = request()->session()->get('business.essentials_settings');
                    $settings = !empty($settings) ? json_decode($settings, true) : [];
                    $prefix = !empty($settings['leave_ref_no_prefix']) ? $settings['leave_ref_no_prefix'] : '';
                    $input['ref_no'] = $this->moduleUtil->generateReferenceNumber('leave', $ref_count, null, $prefix);
                }



                $leave = EssentialsLeave::create($input);

                $admins = $this->moduleUtil->get_admins($input['business_id']);

                \Notification::send($admins, new NewLeaveNotification($leave));
                // $this->__addLeave($input, $user_id);
                // foreach ($request->input('alt_employees') as $user_id) 
                // {
                //     $this->__addLeave($input, $user_id);
                // }
            }


            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return  $output;
    }

    private function __addLeave($input, $user_id = null)
    {
        $input['user_id'] = !empty($user_id) ? $user_id : request()->session()->get('user.id');
        //Update reference count
        $ref_count = $this->moduleUtil->setAndGetReferenceCount('leave');
        //Generate reference number
        if (empty($input['ref_no'])) {
            $settings = request()->session()->get('business.essentials_settings');
            $settings = !empty($settings) ? json_decode($settings, true) : [];
            $prefix = !empty($settings['leave_ref_no_prefix']) ? $settings['leave_ref_no_prefix'] : '';
            $input['ref_no'] = $this->moduleUtil->generateReferenceNumber('leave', $ref_count, null, $prefix);
        }

        $leave = EssentialsLeave::create($input);

        $admins = $this->moduleUtil->get_admins($input['business_id']);

        \Notification::send($admins, new NewLeaveNotification($leave));
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');



        if (!auth()->user()->can('essentials.crud_all_leave')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                EssentialsLeave::where('business_id', $business_id)->where('id', $id)->delete();

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

    public function changeStatus(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['status', 'leave_id', 'status_note']);

            $leave = EssentialsLeave::where('business_id', $business_id)
                ->find($input['leave_id']);

            $leave->status = $input['status'];
            $leave->status_note = $input['status_note'];
            $leave->save();

            $leave->status = $this->leave_statuses[$leave->status]['name'];

            $leave->changed_by = auth()->user()->id;

            $leave->user->notify(new LeaveStatusNotification($leave));

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
     * Function to show activity log related to a leave
     *
     * @return Response
     */
    public function activity($id)
    {
        $business_id = request()->session()->get('user.business_id');



        $leave = EssentialsLeave::where('business_id', $business_id)
            ->find($id);

        $activities = Activity::forSubject($leave)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        return view('essentials::leave.activity_modal')->with(compact('leave', 'activities'));
    }

    /**
     * Function to get leave summary of a user
     *
     * @return Response
     */
    public function getUserLeaveSummary()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $user_id = $is_admin ? request()->input('user_id') : auth()->user()->id;



        if (empty($user_id)) {
            return '';
        }

        $query = EssentialsLeave::where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->with(['leave_type'])
            ->select(
                'status',
                'essentials_leave_type_id',
                'start_date',
                'end_date'
            );

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end = request()->end_date;
            $query->whereDate('start_date', '>=', $start)
                ->whereDate('start_date', '<=', $end);
        }
        $leaves = $query->get();
        $statuses = $this->leave_statuses;
        $leaves_summary = [];
        $status_summary = [];

        foreach ($statuses as $key => $value) {
            $status_summary[$key] = 0;
        }
        foreach ($leaves as $leave) {
            $start_date = \Carbon::parse($leave->start_date);
            $end_date = \Carbon::parse($leave->end_date);
            $diff = $start_date->diffInDays($end_date) + 1;

            $leaves_summary[$leave->essentials_leave_type_id][$leave->status] =
                isset($leaves_summary[$leave->essentials_leave_type_id][$leave->status]) ?
                $leaves_summary[$leave->essentials_leave_type_id][$leave->status] + $diff : $diff;

            $status_summary[$leave->status] = isset($status_summary[$leave->status]) ? ($status_summary[$leave->status] + $diff) : $diff;
        }

        $leave_types = EssentialsLeaveType::where('business_id', $business_id)
            ->get();
        $user = User::where('business_id', $business_id)
            ->find($user_id);

        return view('essentials::leave.user_leave_summary')->with(compact('leaves_summary', 'leave_types', 'statuses', 'user', 'status_summary'));
    }
}
