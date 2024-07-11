<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\BusinessLocation;
use App\Company;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Illuminate\Support\Facades\Auth;

class EssentialsEmployeeAppointmentController extends Controller
{
    protected $moduleUtil;
    protected $statuses;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->statuses = [
            'active' => [
                'name' => __('sales::lang.active'),
                'class' => 'bg-green',
            ],
            'inactive' => [
                'name' => __('sales::lang.inactive'),
                'class' => 'bg-red',
            ],
            'terminated' => [
                'name' => __('sales::lang.terminated'),
                'class' => 'bg-blue',
            ],
            'vecation' => [
                'name' => __('sales::lang.vecation'),
                'class' => 'bg-yellow',
            ],
        ];
    }


    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $can_crud_employee_appointments = auth()->user()->can('essentials.crud_employee_appointments');
        $can_add_employee_appointments = auth()->user()->can('essentials.add_employee_appointments');
        $can_edit_employee_appointments = auth()->user()->can('essentials.edit_employee_appointments');
        $can_delete_employee_appointments = auth()->user()->can('essentials.delete_employee_appointments');
        $can_activate_employee_appointments = auth()->user()->can('essentials.activate_employee_appointments');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (!$can_crud_employee_appointments) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }


        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');
        $business_locations = Company::where('business_id', $business_id)->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');

        $employeeAppointments = EssentialsEmployeeAppointmet::join('users as u', 'u.id', '=', 'essentials_employee_appointmets.employee_id')
            ->whereIn('u.id', $userIds)
            //->where('u.status', '!=', 'inactive')
            ->select([
                'essentials_employee_appointmets.id',
                'essentials_employee_appointmets.employee_id',

                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ',COALESCE(u.mid_name, ''),' ', COALESCE(u.last_name, '')) as user"),
                'u.id_proof_number',
                'essentials_employee_appointmets.business_location_id',
                'essentials_employee_appointmets.department_id',
                'essentials_employee_appointmets.profession_id',
                'essentials_employee_appointmets.is_active',
                'u.status as status',


            ])->orderby('id', 'desc');


        if (request()->ajax()) {


            if (!empty(request()->input('job_title')) && request()->input('job_title') !== 'all') {
                $employeeAppointments = $employeeAppointments->where('essentials_employee_appointmets.profession_id', request()->input('job_title'));
            }

            if (!empty(request()->input('location')) && request()->input('location') !== 'all') {
                $employeeAppointments->where('essentials_employee_appointmets.business_location_id', request()->input('location'));
            }

            if (!empty(request()->input('department')) && request()->input('department') !== 'all') {
                $employeeAppointments->where('essentials_employee_appointmets.department_id', request()->input('department'));
            }

            return Datatables::of($employeeAppointments)

                ->editColumn('department_id', function ($row) use ($departments) {
                    $item = $departments[$row->department_id] ?? '';

                    return $item;
                })
                ->editColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->profession_id] ?? '';

                    return $item;
                })

                ->editColumn('business_location_id', function ($row) use ($business_locations) {
                    $item = $business_locations[$row->business_location_id] ?? '';

                    return $item;
                })

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_edit_employee_appointments, $can_delete_employee_appointments, $can_activate_employee_appointments) {
                        $html = '';
                        if ($is_admin  || $can_edit_employee_appointments) {
                            $html .= '<a  href="' . route('appointment.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                            '&nbsp;';
                        }

                        if ($is_admin  || $can_delete_employee_appointments) {
                            $html .= '&nbsp; <button class="btn btn-xs btn-danger delete_appointment_button" data-href="' . route('appointment.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        if ($is_admin || $can_activate_employee_appointments) {
                            $html .= '&nbsp; <a href="#" class="btn btn-xs btn-warning change_activity"  data-appointment-id="' . $row->id . '" data-orig-value="' . $row->is_active . '"><i class="glyphicon glyphicon-stop"></i> ' . __('essentials::lang.end_activate') . '</a>';
                        }



                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $query->whereRaw("status  like ?", ["%{$keyword}%"]);
                })

                ->removeColumn('id')
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        $query = User::whereIn('id', $userIds);
        $all_users = $query->where('status', '!=', 'inactive')->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $statuses = $this->statuses;

        return view('essentials::employee_affairs.employee_appointments.index')
            ->with(compact('statuses', 'users', 'departments', 'business_locations', 'specializations', 'professions'));
    }

    public function change_activity(Request $request, $appointmentId)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $input = $request->only(['origValue']);

            $appointmet = EssentialsEmployeeAppointmet::find($appointmentId);

            if ($appointmet) {
                $appointmet->is_active = $input['origValue'];
                $appointmet->updated_by = Auth::user()->id;

                $appointmet->end_at = now();
                $appointmet->save();
                User::where('id',   $appointmet->employee_id)->update([
                    'essentials_department_id' => Null,
                ]);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.not_found'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }


    public function changeStatus(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');


        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $departments = EssentialsDepartment::forDropdown();
        $business_locations = BusinessLocation::all()->pluck('name', 'id');

        try {
            $input = $request->only(['status', 'offer_id']);
            $user = EssentialsEmployeeAppointmet::find($input['offer_id'])->select('employee_id')->first();
            // $user =User::find($userId);

            //  $user->status = $input['status'];

            // $user->save();
            $collection = User::where('id', '=', $user->employee_id)->get();

            foreach ($collection as $model) {
                $model->update([
                    'status' => $input['status'],
                    'created_by' => Auth::user()->id
                ]);
                $model->save();
            }


            //  $offer->status = $this->statuses[$user->status]['name'];


            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }




        return redirect()->route('appointments')->with(compact('users', 'departments', 'business_locations'));

        //   return view('essentials::employee_affairs.employee_appointments.index')->with(compact('users','departments','business_locations','specializations','professions'));

    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $input = $request->only(['employee', 'department', 'location', 'profession', 'start_from']);

            $input2['employee_id'] = $input['employee'];
            $input2['department_id'] = $input['department'];
            $input2['business_location_id'] = $input['location'];
            $input2['profession_id'] = $input['profession'];

            $input2['created_by'] = Auth::user()->id;

            $input2['is_active'] = 1;
            $input2['start_from'] = $input['start_from'];

            $previous_appoientement = EssentialsEmployeeAppointmet::where('employee_id', $input2['employee_id'])
                ->latest('created_at')
                ->first();


            if ($previous_appoientement) {
                $previous_appoientement->is_active = 0;
                $previous_appoientement->end_at = $input2['start_from'];
                $previous_appoientement->save();
            }


            EssentialsEmployeeAppointmet::create($input2);
            User::where('id', $input['employee'])->update(['essentials_department_id' => $input['department']]);

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

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $departments = EssentialsDepartment::forDropdown();
        $business_locations = BusinessLocation::all()->pluck('name', 'id');


        return redirect()->route('appointments')->with(compact('users', 'departments', 'business_locations'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            User::where('id', EssentialsEmployeeAppointmet::where('id', $id)->first()->employee_id)->update([
                'essentials_department_id' => Null,
            ]);
            EssentialsEmployeeAppointmet::where('id', $id)
                ->delete();

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


    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $business_locations = Company::where('business_id', $business_id)->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');

        $Appointmet = EssentialsEmployeeAppointmet::findOrFail($id);
        $departments = EssentialsDepartment::all()->pluck('name', 'id');

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        return view('essentials::employee_affairs.employee_appointments.edit')->with(compact('Appointmet', 'users', 'departments', 'business_locations', 'specializations', 'professions'));
    }
    public function update(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;




        try {
            $input = $request->only(['employee', 'department', 'location', 'profession', 'specialization']);

            $input2['employee_id'] = $input['employee'];
            $input2['department_id'] = $input['department'];
            $input2['business_location_id'] = $input['location'];

            //        $input2['superior'] = $input['superior'];
            $input2['profession_id'] = $input['profession'];
            $input2['updated_by'] = Auth::user()->id;

            User::where('id', $input['employee'])->update(['essentials_department_id' => $input['department']]);

            EssentialsEmployeeAppointmet::where('id', $id)->update($input2);
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('appointments');
    }
}
