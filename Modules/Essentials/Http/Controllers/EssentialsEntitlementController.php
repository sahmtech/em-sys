<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsEntitlementType;

class EssentialsEntitlementController extends Controller
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
       $can_crud_entitlements= auth()->user()->can('essentials.crud_entitlements');
       if (! $can_crud_entitlements) {
           abort(403, 'Unauthorized action.');
       }
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (request()->ajax()) {
           $entitlement_types = DB::table('essentials_entitlement_types')->select(['id', 'name','percentage','from', 'details', 'is_active']);
                      

           return Datatables::of($entitlement_types)
           ->addColumn(
               'action',
               function ($row) use ($is_admin) {
                   $html = '';
                   if ($is_admin) {
                       $html .= '<a href="'. route('Entitlement.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                       &nbsp;';
                       $html .= '<button class="btn btn-xs btn-danger delete_entitlement_type_button" data-href="' . route('Entitlement.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                   }
       
                   return $html;
               }
           )
           ->filterColumn('name', function ($query, $keyword) {
               $query->where('name', 'like', "%{$keyword}%");
           })
           ->removeColumn('id')
           ->rawColumns(['action'])
           ->make(true);
       
       
           }
     return view('essentials::settings.partials.entitlements.index');
   }

   public function create()
   {   
      
       $business_id = request()->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
                    abort(403, 'Unauthorized action.');}
      
       return view('essentials::settings.partials.entitlements.create');
       
       
   }

  
   public function store(Request $request)
   {
     
       $business_id = $request->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
           abort(403, 'Unauthorized action.');
       }

       try {
           $input = $request->only(['name', 'percentage', 'from', 'details', 'is_active']);
           

           $input2['name'] =  $input['name'];
           
           $input2['percentage'] = $input['percentage'];

           $input2['from'] = $input['from'];
          
           $input2['details'] = $input['details'];
           
           $input2['is_active'] = $input['is_active'];
           
           EssentialsEntitlementType::create($input2);

           $output = ['success' => true,
               'msg' => __('lang_v1.added_success'),
           ];
       } catch (\Exception $e) {
           \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

           $output = ['success' => false,
               'msg' => __('messages.something_went_wrong'),
           ];
       }

       return redirect()->route('entitlements');
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

       $entitlement = EssentialsEntitlementType::findOrFail($id);


       return view('essentials::settings.partials.entitlements.edit')->with(compact('entitlement'));
   }


   public function update(Request $request, $id)
   {
     
       $business_id = $request->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
           abort(403, 'Unauthorized action.');
       }

       try {
        $input = $request->only(['name', 'percentage', 'from', 'details', 'is_active']);
           

        $input2['name'] =  $input['name'];
        
        $input2['percentage'] = $input['percentage'];

        $input2['from'] = $input['from'];
       
        $input2['details'] = $input['details'];
        
        $input2['is_active'] = $input['is_active'];
        
           
           EssentialsEntitlementType::where('id', $id)->update($input2);
           $output = ['success' => true,
               'msg' => __('lang_v1.updated_success'),
           ];
       } catch (\Exception $e) {
           \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

           $output = ['success' => false,
               'msg' => __('messages.something_went_wrong'),
           ];
       }


       return redirect()->route('entitlements');
   }

   public function destroy($id)
   {
       $business_id = request()->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
           abort(403, 'Unauthorized action.');
       }

       try {
           EssentialsEntitlementType::where('id', $id)
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
