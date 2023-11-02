<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\BusinessLocation;
use App\User;
use App\Category;
use DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Events\UserCreatedOrModified;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;

class EssentialsManageEmployeeController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function getAmount($salaryType)
    {
     
     
      //  $allowance= EssentialsAllowanceAndDeduction::where('id', 1)->find();
                  
        $categories=EssentialsAllowanceAndDeduction::where('id', $salaryType)->select('amount')
        ->first();
        return response()->json($categories); // Return 0 if allowance not found
    }

    public function employ($id)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)
                    ->with(['contactAccess'])
                    ->findOrFail($id);

        $roles = $this->getRolesArray($business_id);

        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();
        

        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        $locations = BusinessLocation::where('business_id', $business_id)
                                    ->get();

        $permitted_locations = $user->permitted_locations();
        $username_ext = $this->moduleUtil->getUsernameExtension();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);

        return view('essentials::employee_affairs.employee_affairs.employ_user')
                ->with(compact('roles', 'user', 'contact_access', 'is_checked_checkbox', 'locations', 'permitted_locations', 'form_partials', 'username_ext'));
    }


    public function usersIndex()
    {
        if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $users = User::where('business_id', $business_id)->where('user_type', 'LIKE', '%user%' )
                        ->user()
                        ->where('is_cmmsn_agnt', 0)
                        ->select(['id', 'username',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email', 'allow_login', ]);

            return Datatables::of($users)
                ->editColumn('username', '{{$username}} @if(empty($allow_login)) <span class="label bg-gray">@lang("lang_v1.login_not_allowed")</span>@endif')
                ->addColumn(
                    'action',
                    '@can("user.update")
                        <a href="{{ route("employ",["id"=>$id]) }}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.employe")</a>
                        &nbsp;
                    @endcan'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'username'])
                ->make(true);
        }

        return view('essentials::employee_affairs.employee_affairs.usersIndex');
    }


    /**
     * Display a listing of the resource.
     * @return Renderable
     */ 
   
    public function index()
    {
        if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }
        $appointments=EssentialsEmployeeAppointmet::all()->pluck('profession_id','employee_id');
        $appointments2=EssentialsEmployeeAppointmet::all()->pluck('specialization_id','employee_id');
        $categories=Category::all()->pluck('name','id');
        $departments=EssentialsDepartment::all()->pluck('name','id');
        $contract_types = EssentialsContractType::all()->pluck('type','id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $users = User::where('users.business_id', $business_id)->where('users.is_cmmsn_agnt', 0)
            ->select(['users.id',
                    'users.username',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as full_name"),
                    'users.dob',
                    'users.email',
                    'users.allow_login',
                    'users.contact_number',
                    'users.essentials_department_id',
                    
                    'users.status'
                        ]);

            return Datatables::of($users)
                ->editColumn('essentials_department_id',function($row)use($departments){
                        $item = $departments[$row->essentials_department_id]??'';

                        return $item;
                    })
                    ->addColumn('profession', function ($row) use ($appointments, $professions) {
                        $professionId = $appointments[$row->id] ?? '';
                
                        $professionName = $professions[$professionId] ?? '';
                
                        return $professionName;
                    })
                    ->addColumn('specialization', function ($row) use ($appointments2, $specializations) {
                        $specializationId = $appointments2[$row->id] ?? '';
                
                        $specializationName = $specializations[$specializationId] ?? '';
                
                        return $specializationName;
                    })
                ->addColumn(
                    'action',
                    '@can("user.update")
                        <a href="{{route(\'editEmployee\',[\'id\'=>$id]) }}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;
                    @endcan
                    @can("user.view")
                    <a href="{{route(\'showEmployee\',[\'id\'=>$id])}}" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> @lang("messages.view")</a>
                    &nbsp;
                    @endcan
                    @can("user.delete")
                        <button data-href="{{action(\'App\Http\Controllers\ManageUserController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                
                ->rawColumns(['action','profession','specialization'])
                ->make(true);
        }
        return view('essentials::employee_affairs.employee_affairs.index')->with(compact('contract_types','nationalities','specializations','professions'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } 
        elseif (! $this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }

        $roles = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $locations = BusinessLocation::where('business_id', $business_id)
                                    ->Active()
                                    ->get();
        $contract_types = EssentialsContractType::all()->pluck('type','id');
        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities=EssentialsCountry::nationalityForDropdown();

        return view('essentials::employee_affairs.employee_affairs.create')
                ->with(compact('roles','nationalities' ,'username_ext', 'locations', 'contract_types','form_partials'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
           
            if (! auth()->user()->can('user.create')) {
                abort(403, 'Unauthorized action.');
            }
    
            try {
                if (! empty($request->input('dob'))) {
                    $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
                }
    
                $request['cmmsn_percent'] = ! empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;
    
                $request['max_sales_discount_percent'] = ! is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;

                $user = $this->moduleUtil->createUser($request);
    
                event(new UserCreatedOrModified($user, 'added'));
    
                $output = ['success' => 1,
                    'msg' => __('user.user_added'),
                ];
            } 
            catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
    
                error_log('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                $output = ['success' => 0,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
    
            return redirect()->route('employees')->with('status', $output);
        }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (! auth()->user()->can('user.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $user = User::where('business_id', $business_id)
                    ->with(['contactAccess'])
                    ->find($id);

        //Get user view part from modules
        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);
       
        $users = User::forDropdown($business_id, false);

        $activities = Activity::forSubject($user)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        return view('essentials::employee_affairs.employee_affairs.show')->with(compact('user', 'view_partials', 'users', 'activities'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)
                    ->with(['contactAccess'])
                    ->findOrFail($id);
        $appointments=EssentialsEmployeeAppointmet::select([
          
            'profession_id',
            'specialization_id'
        ])->where('employee_id', $id)
        ->get()[0];
        $user->profession_id =$appointments['profession_id'];
        $user->specialization_id =$appointments['specialization_id'];
       
        $roles = $this->getRolesArray($business_id);

        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();
        $contract_types = EssentialsContractType::all()->pluck('type','id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('id', 'name');
        $professions = EssentialsProfession::all()->pluck('id', 'name');
        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        $locations = BusinessLocation::where('business_id', $business_id)
                                    ->get();

        $permitted_locations = $user->permitted_locations();
        $username_ext = $this->moduleUtil->getUsernameExtension();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);

        return view('essentials::employee_affairs.employee_affairs.edit')
                ->with(compact('roles', 'user', 'contact_access', 'is_checked_checkbox', 'locations', 'permitted_locations', 'form_partials','appointments' ,'username_ext','contract_types','nationalities','specializations','professions'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request,$id)
    {
       
            if (!auth()->user()->can('user.update')) {
                abort(403, 'Unauthorized action.');
            }
            try {
                $user_data = $request->only([
                    'surname', 'first_name', 'last_name', 'email', 'selected_contacts', 'marital_status',
                    'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1',
                    'social_media_2', 'permanent_address', 'current_address','profession','specialization',
                    'guardian_name', 'custom_field_1', 'custom_field_2','nationality',
                    'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'max_sales_discount_percent', 'family_number', 'alt_number',
                ]);
    
                $user_data['status'] = !empty($request->input('is_active')) ? 'active' : 'inactive';
                $business_id = request()->session()->get('user.business_id');
                if (!isset($user_data['selected_contacts'])) {
                    $user_data['selected_contacts'] = 0;
                }
                if (empty($request->input('allow_login'))) {
                    $user_data['username'] = null;
                    $user_data['password'] = null;
                    $user_data['allow_login'] = 0;
                } else {
                    $user_data['allow_login'] = 1;
                }
    
                if (!empty($request->input('password'))) {
                    $user_data['password'] = $user_data['allow_login'] == 1 ? Hash::make($request->input('password')) : null;
                }
                //Sales commission percentage
                $user_data['cmmsn_percent'] = !empty($user_data['cmmsn_percent']) ? $this->moduleUtil->num_uf($user_data['cmmsn_percent']) : 0;
                //$user_data['max_sales_discount_percent'] = ! is_null($user_data['max_sales_discount_percent']) ? $this->moduleUtil->num_uf($user_data['max_sales_discount_percent']) : null;
                $user_data['max_sales_discount_percent'] = null;
                if (!empty($request->input('dob'))) {
                    $user_data['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
                }
                if (!empty($request->input('nationality'))) 
                     { $user_data['nationality_cs'] = $request->input('nationality');}
                if (!empty($request->input('bank_details'))) {
                    $user_data['bank_details'] = json_encode($request->input('bank_details'));
                }
    
                DB::beginTransaction();
                if ($user_data['allow_login'] && $request->has('username')) {
                    $user_data['username'] = $request->input('username');
                    $ref_count = $this->moduleUtil->setAndGetReferenceCount('username');
                    if (blank($user_data['username'])) {
                        $user_data['username'] = $this->moduleUtil->generateReferenceNumber('username', $ref_count);
                    }
    
                    $username_ext = $this->moduleUtil->getUsernameExtension();
                    if (!empty($username_ext)) {
                        $user_data['username'] .= $username_ext;
                    }
                }
    
                $user = User::where('business_id', $business_id)
                    ->findOrFail($id);
    
                $user->update($user_data);
             
             
    
                //Update module fields for user
                $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_saved', 'model_instance' => $user,'request'=>$user_data]);
    
                $this->moduleUtil->activityLog($user, 'edited', null, ['name' => $user->user_full_name]);
    
                event(new UserCreatedOrModified($user, 'updated'));
    
                $output = [
                    'success' => 1,
                    'msg' => __('user.user_update_success'),
                ];
    
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
    
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
                $output = [
                    'success' => 0,
                    'msg' => $e->getMessage(),
                ];
            }
    
            return redirect()->route('employees')->with('status', $output);
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

    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        foreach ($roles_array as $key => $value) {
            if (! $is_admin && $value == 'Admin#'.$business_id) {
                continue;
            }
            $roles[$key] = str_replace('#'.$business_id, '', $value);
        }
        return $roles;
    }
}
