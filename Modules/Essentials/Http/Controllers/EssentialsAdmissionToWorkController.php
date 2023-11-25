<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;

class EssentialsAdmissionToWorkController extends Controller
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
        $can_crud_employee_work_adminitions = auth()->user()->can('essentials.crud_employee_work_adminitions');
        if (! $can_crud_employee_work_adminitions) {
            abort(403, 'Unauthorized action.');
        }
        $departments=EssentialsDepartment::all()->pluck('name','id');
        if (request()->ajax()) {
            $admissionToWork = EssentialsAdmissionToWork::
                join('users as u', 'u.id', '=', 'essentials_admission_to_works.employee_id')->where('u.business_id', $business_id)
               
                ->select([
                    'essentials_admission_to_works.id',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'u.id_proof_number',
              
                    'essentials_admission_to_works.admissions_type',
                    'essentials_admission_to_works.admissions_status',
                    'essentials_admission_to_works.admissions_date',
               
        
        
                ]);

            if (!empty(request()->input('admissions_status')) && request()->input('admissions_status') !== 'all') {
                $admissionToWork->where('essentials_admission_to_works.admissions_status', request()->input('admissions_status'));
            }

            if (!empty(request()->input('admissions_type')) && request()->input('admissions_type') !== 'all') {
                $admissionToWork->where('essentials_admission_to_works.admissions_type', request()->input('admissions_type'));
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $admissionToWork->whereDate('essentials_admission_to_works.admissions_date', '>=', $start)
                    ->whereDate('essentials_admission_to_works.admissions_date', '<=', $end);
            }

            return Datatables::of($admissionToWork)
         
            ->addColumn(
                'action',
                 function ($row) {
                    $html = '';
                //    $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    $html .= '<a  href="'. route('admissionToWork.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>&nbsp;';
                    $html .= '<button class="btn btn-xs btn-danger delete_admissionToWork_button" data-href="' . route('admissionToWork.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    
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
                $query = User::where('business_id', $business_id)
                ->whereIn('user_type', ['employee', 'worker', 'manager']);
                $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
                $users = $all_users->pluck('full_name', 'id');
              

        return view('essentials::employee_affairs.admission_to_work.index')->with(compact('users','departments'));
    }

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
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['employee', 'admissions_type', 'admissions_status', 'admissions_date']);
          
            $input2['employee_id'] = $input['employee'];
         
            $input2['admissions_type'] = $input['admissions_type'];
            $input2['admissions_status'] = $input['admissions_status'];
            $input2['admissions_date'] = $input['admissions_date'];
        
         
       
            EssentialsAdmissionToWork::create($input2);
            
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        
        $departments = EssentialsDepartment::forDropdown();

       return redirect()->route('admissionToWork')->with(compact('users','departments'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsAdmissionToWork::where('id', $id)
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $work = EssentialsAdmissionToWork::findOrFail($id);
        $departments=EssentialsDepartment::all()->pluck('name','id');
        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        return view('essentials::employee_affairs.admission_to_work.edit')->with(compact('users','departments','work'));
      
    }

 
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['employee', 'admissions_type', 'admissions_status', 'admissions_date']);
          
            $input2['employee_id'] = $input['employee'];
         
            $input2['admissions_type'] = $input['admissions_type'];
            $input2['admissions_status'] = $input['admissions_status'];
            $input2['admissions_date'] = $input['admissions_date'];
        
         
       
            EssentialsAdmissionToWork::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        
        $departments = EssentialsDepartment::forDropdown();

       return redirect()->route('admissionToWork')->with(compact('users','departments'));
    }
}
