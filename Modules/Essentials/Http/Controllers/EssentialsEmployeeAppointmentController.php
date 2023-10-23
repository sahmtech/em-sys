<?php

namespace Modules\Essentials\Http\Controllers;

use App\BusinessLocation;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;


class EssentialsEmployeeAppointmentController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    
    
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        $can_crud_employee_appointments = auth()->user()->can('essentials.crud_employee_appointments');
        if (! $can_crud_employee_appointments) {
            abort(403, 'Unauthorized action.');
        }
        $departments=EssentialsDepartment::all()->pluck('name','id');
        $business_locations=BusinessLocation::all()->pluck('name','id');
        if (request()->ajax()) {
            $employeeAppointments = EssentialsEmployeeAppointmet::
                join('users as u', 'u.id', '=', 'essentials_employee_appointmets.employee_id')->where('u.business_id', $business_id)
                ->select([
                    'essentials_employee_appointmets.id',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'u.id_proof_number',
                    'essentials_employee_appointmets.business_location_id',
                    'essentials_employee_appointmets.department_id',
                    'essentials_employee_appointmets.superior',
                    'essentials_employee_appointmets.job_title',
                    'essentials_employee_appointmets.employee_status',

                ]);

            if (!empty(request()->input('job_title')) && request()->input('job_title') !== 'all') {
                $employeeAppointments->where('essentials_employee_appointmets.job_title', request()->input('job_title'));
            }

            if (!empty(request()->input('location')) && request()->input('location') !== 'all') {
                $employeeAppointments->where('essentials_employee_appointmets.business_location_id', request()->input('location'));
            }
          
            if (!empty(request()->input('department')) && request()->input('department') !== 'all') {
                $employeeAppointments->where('essentials_employee_appointmets.department_id', request()->input('department'));
            }
            return Datatables::of($employeeAppointments)
            ->editColumn('department_id',function($row)use($departments){
                $item = $departments[$row->department_id]??'';

                return $item;
            })
            ->editColumn('business_location_id',function($row)use($business_locations){
                $item = $business_locations[$row->business_location_id]??'';

                return $item;
            })
            ->addColumn(
                'action',
                 function ($row) {
                    $html = '';
                //    $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                //    $html .= '<a  href="'. route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                    $html .= '<button class="btn btn-xs btn-danger delete_appointment_button" data-href="' . route('appointment.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    
                    return $html;
                 }
                )
            
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
                $query = User::where('business_id', $business_id);
                $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
                $users = $all_users->pluck('full_name', 'id');
               
                

        return view('essentials::employee_affairs.employee_appointments.index')->with(compact('users','departments','business_locations'));
    }
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['employee', 'department','location', 'superior', 'job_title', 'employee_status']);
          
            $input2['employee_id'] = $input['employee'];
            $input2['department_id'] = $input['department'];
            $input2['business_location_id'] = $input['location'];

            $input2['superior'] = $input['superior'];
            $input2['job_title'] = $input['job_title'];
            $input2['employee_status'] = $input['employee_status'];
        
         
       
            EssentialsEmployeeAppointmet::create($input2);
            
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $query = User::where('business_id', $business_id)
        ->user();
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        
        $departments = EssentialsDepartment::forDropdown();
        $business_locations=BusinessLocation::all()->pluck('name','id');


       return redirect()->route('appointments')->with(compact('users','departments','business_locations'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsEmployeeAppointmet::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       
       return $output;

    }

}
