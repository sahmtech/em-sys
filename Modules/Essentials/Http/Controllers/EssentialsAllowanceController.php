<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\essentialsAllowanceType;

class EssentialsAllowanceController extends Controller
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
 
        $can_crud_allowances = auth()->user()->can('essentials.crud_allowances');
        if (! $can_crud_allowances) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
 
        if (request()->ajax()) {
            $types= DB::table('essentials_allowance_types')->select(['id','type', 'name','allowance_value','number_of_months','added_to_salary','details', 'is_active']);
                       
 
            return Datatables::of($types)
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('Allowance.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_allowance_type_button" data-href="' . route('Allowance.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
      return view('essentials::settings.partials.allowances.index');
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {   
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
                     abort(403, 'Unauthorized action.');}
       
        return view('essentials::settings.partials.allowances.create');
     
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
            $input = $request->only(['type', 'name','allowance_value','number_of_months','added_to_salary','details', 'is_active']);
            
            $input2['type'] = $input['type'];

            $input2['name'] = $input['name'];

            $input2['allowance_value'] = $input['allowance_value'];

            $input2['number_of_months'] = $input['number_of_months'];

            $input2['added_to_salary'] = $input['added_to_salary'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            essentialsAllowanceType::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('allowances');
    
    }

    /**
     * Show the specified resource.
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
 
         $allowance = essentialsAllowanceType::findOrFail($id);
 
 
         return view('essentials::settings.partials.allowances.edit')->with(compact('allowance'));
     }
 
  
     public function update(Request $request, $id)
     {
       
         $business_id = $request->session()->get('user.business_id');
         $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
 
         if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
             abort(403, 'Unauthorized action.');
         }
 
         try {
            $input = $request->only(['type', 'name','allowance_value','number_of_months','added_to_salary','details', 'is_active']);
            
            $input2['type'] = $input['type'];

            $input2['name'] = $input['name'];

            $input2['allowance_value'] = $input['allowance_value'];

            $input2['number_of_months'] = $input['number_of_months'];

            $input2['added_to_salary'] = $input['added_to_salary'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
             essentialsAllowanceType::where('id', $id)->update($input2);
             $output = ['success' => true,
                 'msg' => __('lang_v1.updated_success'),
             ];
         } catch (\Exception $e) {
             \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
 
             $output = ['success' => false,
                 'msg' => __('messages.something_went_wrong'),
             ];
         }
 
         return redirect()->route('allowances');

     }
 
     public function destroy($id)
     {
         $business_id = request()->session()->get('user.business_id');
         $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
 
         if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
             abort(403, 'Unauthorized action.');
         }
 
         try {
            essentialsAllowanceType::where('id', $id)
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
