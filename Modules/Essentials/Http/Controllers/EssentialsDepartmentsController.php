<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
class EssentialsDepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
   

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    
   public function treeIndex()
   {
    $business_id = request()->session()->get('user.business_id');

    if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
        abort(403, 'Unauthorized action.');
    }
    $can_crud_organizational_structure = auth()->user()->can('essentials.crud_organizational_structure');
    if (! $can_crud_organizational_structure) {
        abort(403, 'Unauthorized action.');
    }
    $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
  

    $departments = EssentialsDepartment::where('parent_department_id', '=', 0)
    ->where('business_id','=',$business_id)->get();
    $alldepartments = EssentialsDepartment::pluck('name','id')
    ->where('business_id','=',$business_id)->all();

    return view('essentials::settings.partials.departments.index',  compact('departments','alldepartments'));
    
   }

    public function storeNode(Request $request)
    {
        $order = EssentialsDepartment::first();
        $business_id = request()->session()->get('user.business_id');

        if(is_null($order)) {
            $newNode = EssentialsDepartment::create([
                'name' => $request->input('new_text'),
                'parent_department_id' => 0,
                'level'=> 1,
                'business_id'=>$business_id
            ]);

            return response()->json(['message' => 'Node added successfully'], 200);
                }
            else
                 {

    
                    $Pid=$request->input('parent_id');
                    $level=$request->input('level');
                    
                    $newNode = EssentialsDepartment::create([
                        'name' => $request->input('new_text'),
                        'parent_department_id' => $Pid,
                        'level'=> $level+1,
                        'business_id'=>$business_id
                    ]);

                return response()->json(['message' => 'Node added successfully'], 200);
                }
       
    }

    public function updateNode(Request $request, $id)
    {
        $newText = $request->input('new_text');
             
        $model = EssentialsDepartment::findOrFail($id);

        $model->name =  $newText;
        
        $model->save();
        
        
        return response()->json(['message' => 'Node edited successfully']);
    }


    private function deleteNodeRecursively($node)
    {
        foreach ($node->childs as $child) {
            $this->deleteNodeRecursively($child);
        }

        $node->delete();
    }

    public function deletenode($id)
    {
     
        $node = EssentialsDepartment::find($id);

        if (!$node) {
            return response()->json(['error' => 'Node not found'], 404);
        }
       else{
     
         $this->deleteNodeRecursively($node);

         return response()->json(['message' => 'Node and its children deleted successfully']);
       }
       
    }
  
    ////////////////////////////////////////////////////////////////////
    public function index()
    {
    
       $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_depatments = auth()->user()->can('essentials.crud_departments');
        if (! $can_crud_depatments) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $departments=EssentialsDepartment::all()->pluck('name','id');
        $parent_departments=EssentialsDepartment::where('is_main','1')->pluck('name','id');
       if (request()->ajax()) {
            $depatments = DB::table('essentials_departments')->select(['id','name', 'level','is_main','parent_department_id','creation_date' ,'location','details','business_id','is_active'])->orderBy('id', 'asc');
                       

            return Datatables::of($depatments)
            ->editColumn('parent_department_id',function($row)use($departments){
                $item = $departments[$row->parent_department_id]??'';

                return $item;
            })
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                     //   $html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                     //   &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_department_button" data-href="' . route('department.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
      return view('essentials::settings.partials.departments.index')->with(compact('parent_departments'));
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsDepartment::where('id', $id)
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
    public function store(Request $request)
    {
        
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['name', 'level', 'parent_level','is_main','creation_date','address', 'details', 'is_active']);
            

            $input2['name'] = $input['name'];
            $input2['level'] = $input['level'];
            $input2['parent_department_id'] = $input['parent_level'];
            $input2['is_main'] = $input['is_main'];
            $input2['creation_date'] = $input['creation_date'];
            $input2['address'] = $input['address'];
            $input2['business_id'] = $business_id;           
            $input2['details'] = $input['details'];
            $input2['is_active'] = $input['is_active'];
     
            EssentialsDepartment::create($input2);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

   
       return redirect()->route('departments');
    }
   
}
