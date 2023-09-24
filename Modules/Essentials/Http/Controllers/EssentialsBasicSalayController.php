<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsBasicSalaryType;

class EssentialsBasicSalayController extends Controller
{
    protected $moduleUtil;
   

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
   public function index()
   {
      $business_id = request()->session()->get('user.business_id');

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
           abort(403, 'Unauthorized action.');
       }

       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (request()->ajax()) {
           $types= DB::table('essentials_basic_salary_types')->select(['id','type', 'details', 'is_active']);
                      

           return Datatables::of($types)
           ->addColumn(
               'action',
               function ($row) use ($is_admin) {
                   $html = '';
                   if ($is_admin) {
                       $html .= '<a href="'. route('BasicSalary.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                       &nbsp;';
                       $html .= '<button class="btn btn-xs btn-danger delete_basic_salary_type_button" data-href="' . route('BasicSalary.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                   }
       
                   return $html;
               }
           )
           ->filterColumn('type', function ($query, $keyword) {
               $query->where('type', 'like', "%{$keyword}%");
           })
           ->removeColumn('id')
           ->rawColumns(['action'])
           ->make(true);
       
       
           }
     return view('essentials::settings.partials.basic_salary.index');
   }

    public function create()
    {   
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
                     abort(403, 'Unauthorized action.');}
       
        return view('essentials::settings.partials.basic_salary.create');
     
    }
    public function store(Request $request)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['type',  'details', 'is_active']);
            
            $input['type'] = $input['type'];
           
            $input['details'] = $input['details'];
            
            $input['is_active'] = $input['is_active'];
            
            EssentialsBasicSalaryType::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('basic_salary_types');
    }
  
    public function show($id)
    {
        return view('essentials::show');
    }

  
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $basic_salary_type = EssentialsBasicSalaryType::findOrFail($id);


        return view('essentials::settings.partials.basic_salary.edit')->with(compact('basic_salary_type'));
    }

 
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([ 'type', 'details', 'is_active']);
       
            
            $input2['type'] = $input['type'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsBasicSalaryType::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

      

        return redirect()->route('basic_salary_types');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsBasicSalaryType::where('id', $id)
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
