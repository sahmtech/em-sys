<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use Modules\HousingMovements\Entities\Htr_Building;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsCity;

class BuildingController extends Controller
{
    protected $moduleUtil;
   

    public function __construct(ModuleUtil $moduleUtil)
    {
         $this->moduleUtil = $moduleUtil;
    }


    public function index()
    {
    
       $business_id = request()->session()->get('user.business_id');


        $can_crud_buildings = auth()->user()->can('housingmovement_module.crud_buildings');
        if (! $can_crud_buildings) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $cities= EssentialsCity::forDropdown();
        if (request()->ajax()) {
            $buildings =DB::table('htr_buildings')->select(['id', 'name', 'city_id', 'address', 'guard_id', 'supervisor_id', 'cleaner_id']);
                       
            if (!empty(request()->input('city')) && request()->input('city') !== 'all') {
                $buildings->where('city_id', request()->input('city'));
            }
            return Datatables::of($buildings)
            ->editColumn('city_id',function($row)use($cities){
                $item = $cities[$row->city_id]??'';

                return $item;
            })
            ->editColumn('guard_id',function($row)use($users){
                $item = $users[$row->guard_id]??'';
          
                return $item;
            })
            ->editColumn('supervisor_id',function($row)use($users){
                $item = $users[$row->supervisor_id]??'';

                return $item;
            })
            ->editColumn('cleaner_id',function($row)use($users){
                $item = $users[$row->cleaner_id]??'';

                return $item;
            })
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('building.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_building_button" data-href="' . route('building.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
            $query = User::where('business_id', $business_id)->where('user_type','worker');
            $all_users = $query->select('id',
             DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),' ',COALESCE(id_proof_number,'')) as full_name"))->get();
            $users2 = $all_users->pluck('full_name', 'id');
     
            return view('housingmovements::buildings.index')->with(compact('users2','cities'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::buildings.index');
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
            $input = $request->only(['name', 'city', 'address', 'guard', 'supervisor','cleaner']);
            

            $input2['name'] = $input['name'];
            $input2['city_id'] = $input['city'];
            $input2['address'] = $input['address'];
            $input2['guard_id'] = $input['guard'];
            $input2['supervisor_id'] = $input['supervisor'];
            $input2['cleaner_id'] = $input['cleaner'];
         
            DB::table('htr_buildings')->insert($input2);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

     
       return redirect()->route('buildings');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('housingmovements::show');
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



        $building = DB::table('htr_buildings')->find($id);
        $query = User::where('business_id', $business_id)->where('user_type','worker');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users2 = $all_users->pluck('full_name', 'id');
        $cities= EssentialsCity::forDropdown();
   
        return view('housingmovements::buildings.edit')->with(compact('users2','cities','building'));
    }

 
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            $input = $request->only(['name', 'city', 'address', 'guard', 'supervisor','cleaner']);
            

            $input2['name'] = $input['name'];
            
            $input2['city_id'] = $input['city'];
            $input2['address'] = $input['address'];
            $input2['guard_id'] = $input['guard'];
            $input2['supervisor_id'] = $input['supervisor'];
            $input2['cleaner_id'] = $input['cleaner'];
            
            DB::table('htr_buildings')->where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('buildings');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            DB::table('htr_buildings')->where('id', $id)
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
