<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsContractType;

class EssentialsContractTypeController extends Controller
{
   
    protected $moduleUtil;
   

     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
     public function index()
    {
    
       $business_id = request()->session()->get('user.business_id');


        $can_crud_contract_types = auth()->user()->can('essentials.crud_contract_types');
        if (! $can_crud_contract_types) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (request()->ajax()) {
            $contract_types = DB::table('essentials_contract_types')->select(['id','type', 'details', 'is_active']);
                       

            return Datatables::of($contract_types)
           
           
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('contractType.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_contractType_button" data-href="' . route('contractType.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
      return view('essentials::settings.partials.contractTypes.index');
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
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

 
 
        try {
            $input = $request->only(['type', 'details', 'is_active']);
            

            $input2['type'] = $input['type'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsContractType::create($input2);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

     
       return redirect()->route('contract_types');
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

  

        $contract_type = EssentialsContractType::findOrFail($id);
        return view('essentials::settings.partials.contractTypes.edit')->with(compact('contract_type'));
    }

 
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

  

        try {
            $input = $request->only(['type', 'details', 'is_active']);
            

            $input2['type'] = $input['type'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsContractType::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


    
        return redirect()->route('contract_types');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

   

        try {
            EssentialsContractType::where('id', $id)
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
