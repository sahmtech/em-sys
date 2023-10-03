<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
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

    
   public function index()
   {
    $business_id = request()->session()->get('user.business_id');

    if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
        abort(403, 'Unauthorized action.');
    }

    $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
  

    $departments = EssentialsDepartment::where('parent_department_id', '=', 0)->get();
    $alldepartments = EssentialsDepartment::pluck('name','id')->all();
    return view('essentials::settings.partials.departments.index',  compact('departments','alldepartments'));
    
   }


   





    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
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
        $order = EssentialsDepartment::first();
if(is_null($order)) {
    $newNode = EssentialsDepartment::create([
        'name' => $request->input('new_text'),
        'parent_department_id' => 0,
        'level'=> 1
    ]);

    return response()->json(['message' => 'Node added successfully'], 200);
}else
{

     // Validate the request data
     $Pid=$request->input('parent_id');
     $level=$request->input('level');
     
     $newNode = EssentialsDepartment::create([
         'name' => $request->input('new_text'),
         'parent_department_id' => $Pid,
         'level'=> $level+1
     ]);

     return response()->json(['message' => 'Node added successfully'], 200);
}
       
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
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $newText = $request->input('new_text');
                // Retrieve the model instance by its ID
        $model = EssentialsDepartment::findOrFail($id);

                // Update the desired attribute based on the request data
        $model->name =  $newText;
        
        $model->save();
        
        
        return response()->json(['message' => 'Node edited successfully']);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */

     // Recursive function to delete a node and its children
    private function deleteNodeRecursively($node)
    {
        foreach ($node->childs as $child) {
            $this->deleteNodeRecursively($child);
        }

        $node->delete();
    }

    public function deletenode($id)
    {
        // Retrieve the node
        $node = EssentialsDepartment::find($id);

        if (!$node) {
            return response()->json(['error' => 'Node not found'], 404);
        }
       else{
         // Delete the node and its children recursively
         $this->deleteNodeRecursively($node);

         return response()->json(['message' => 'Node and its children deleted successfully']);
       }
       
    }
    public function destroy($id)
    {
        
    }
    
   
}
