<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsTravelCategorie;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;

class EssentialsTravelCategorieController extends Controller
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
           $travel_categories = DB::table('essentials_travel_ticket_categories')->select(['id','name', 'employee_ticket_value','wife_ticket_value' ,'children_ticket_value','details', 'is_active']);
                      

           return Datatables::of($travel_categories)
         
           ->addColumn(
               'action',
               function ($row) use ($is_admin) {
                   $html = '';
                   if ($is_admin) {
                       $html .= '<a href="'. route('travel_categorie.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                       &nbsp;';
                       $html .= '<button class="btn btn-xs btn-danger delete_travel_categorie_button" data-href="' . route('travel_categorie.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
     return view('essentials::settings.partials.travel_categories.index');
   }

   public function create()
   {   
      
       $business_id = request()->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
                    abort(403, 'Unauthorized action.');}
      
       return view('essentials::settings.partials.travel_categories.create');
       
       
   }

  
   public function store(Request $request)
   {
       $business_id = $request->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
           abort(403, 'Unauthorized action.');
       }

       try {
           $input = $request->only(['name', 'employee_ticket_value','wife_ticket_value' ,'children_ticket_value','details', 'is_active']);
           

           $input2['name'] =  $input['name'];
           
           $input2['employee_ticket_value'] = $input['employee_ticket_value'];

           $input2['wife_ticket_value'] = $input['wife_ticket_value'];

           $input2['children_ticket_value'] = $input['children_ticket_value'];
          
           $input2['details'] = $input['details'];
           
           $input2['is_active'] = $input['is_active'];
           
           
           EssentialsTravelTicketCategorie::create($input2);

           $output = ['success' => true,
               'msg' => __('lang_v1.added_success'),
           ];
       } catch (\Exception $e) {
           \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

           $output = ['success' => false,
               'msg' => __('messages.something_went_wrong'),
           ];
       }

       return view('essentials::settings.partials.travel_categories.index');
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

       $travel_categorie= EssentialsTravelTicketCategorie::findOrFail($id);



       return view('essentials::settings.partials.travel_categories.edit')->with(compact('travel_categorie'));
   }


   public function update(Request $request, $id)
   {
     
       $business_id = $request->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
           abort(403, 'Unauthorized action.');
       }

       try {
        $input = $request->only(['name', 'employee_ticket_value','wife_ticket_value' ,'children_ticket_value','details', 'is_active']);
           

        $input2['name'] =  $input['name'];
        
        $input2['employee_ticket_value'] = $input['employee_ticket_value'];

        $input2['wife_ticket_value'] = $input['wife_ticket_value'];

        $input2['children_ticket_value'] = $input['children_ticket_value'];
       
        $input2['details'] = $input['details'];
        
        $input2['is_active'] = $input['is_active'];
        
        EssentialsTravelTicketCategorie::where('id', $id)->update($input2);
           $output = ['success' => true,
               'msg' => __('lang_v1.updated_success'),
           ];
       } catch (\Exception $e) {
           \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

           $output = ['success' => false,
               'msg' => __('messages.something_went_wrong'),
           ];
       }


       return view('essentials::settings.partials.travel_categories.index');
   }

   public function destroy($id)
   {
       $business_id = request()->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
           abort(403, 'Unauthorized action.');
       }

       try {
        EssentialsTravelTicketCategorie::where('id', $id)
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
