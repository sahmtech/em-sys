<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;

class EssentialsEmployeeQualificationController extends Controller
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
        
        if (request()->ajax()) {
            $employees_qualifications = EssentialsEmployeesQualification::
                join('users as u', 'u.id', '=', 'essentials_employees_qualifications.employee_id')
                ->select([
                    'essentials_employees_qualifications.id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'essentials_employees_qualifications.qualification_type',
                    'essentials_employees_qualifications.major',
                    'essentials_employees_qualifications.graduation_year',
                    'essentials_employees_qualifications.graduation_institution',
                    'essentials_employees_qualifications.graduation_country',
                    'essentials_employees_qualifications.degree',
        
                ]);

            if (!empty(request()->input('qualification_type')) && request()->input('qualification_type') !== 'all') {
                $employees_qualifications->where('essentials_employees_qualifications.qualification_type', request()->input('qualification_type'));
            }

            if (!empty(request()->input('major')) && request()->input('major') !== 'all') {
                $employees_qualifications->where('essentials_employees_qualifications.major', request()->input('major'));
            }


            return Datatables::of($employees_qualifications)
            ->addColumn(
                'action',
                 function ($row) {
                    $html = '';
                //    $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                //    $html .= '<a  href="'. route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                    $html .= '<button class="btn btn-xs btn-danger delete_qualification_button" data-href="' . route('qualification.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    
                    return $html;
                 }
                )
            
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
                $query = User::where('business_id', $business_id)->user();
                $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
                $users = $all_users->pluck('full_name', 'id');
                $countries = EssentialsCountry::forDropdown();

        return view('essentials::employee_affairs.employees_qualifications.index')->with(compact('users','countries'));
    }
   

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['employee', 'qualification_type', 'major', 'graduation_year', 'graduation_institution', 'graduation_country','degree']);
          

            $input2['qualification_type'] = $input['qualification_type'];
            $input2['major'] = $input['major'];
            $input2['graduation_year'] = $input['graduation_year'];
            $input2['graduation_institution'] = $input['graduation_institution'];
            $input2['employee_id'] = $input['employee'];
            $input2['graduation_country'] = $input['graduation_country'];
            $input2['degree'] = $input['degree'];
        
            
            EssentialsEmployeesQualification::create($input2);
            
 
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
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $countries = EssentialsCountry::forDropdown();
    
       return redirect()->route('qualifications')->with(compact('users','countries'));
    }

    public function show($id)
    {
        return view('essentials::show');
    }

    public function edit($id)
    {
        return view('essentials::edit');
    }

 
    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsEmployeesQualification::where('id', $id)
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
