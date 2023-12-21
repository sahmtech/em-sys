<?php

namespace Modules\HousingMovements\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
class RoomController extends Controller
{
    protected $moduleUtil;
   

    public function __construct(ModuleUtil $moduleUtil)
    {
         $this->moduleUtil = $moduleUtil;
    }


    public function index()
    {
    
       $business_id = request()->session()->get('user.business_id');


        $can_crud_rooms = auth()->user()->can('housingmovement_module.crud_rooms');
        if (! $can_crud_rooms) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $buildings=DB::table('htr_buildings')->get()->pluck('name','id');
        if (request()->ajax()) {
            $rooms =DB::table('htr_rooms')->select(['id', 'room_number', 'htr_building_id', 'area', 'beds_count', 'contents']);
                       
            if (!empty(request()->input('htr_building')) && request()->input('htr_building') !== 'all') {
                $rooms->where('htr_building_id', request()->input('htr_building'));
            }
            return Datatables::of($rooms)
            ->editColumn('htr_building_id',function($row)use($buildings){
                $item = $buildings[$row->htr_building_id]??'';

                return $item;
            })
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('room.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_room_button" data-href="' . route('room.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    }
        
                    return $html;
                }
            )
            ->filterColumn('number', function ($query, $keyword) {
                $query->where('number', 'like', "%{$keyword}%");
            })
            ->removeColumn('id')
            ->rawColumns(['action'])
            ->make(true);
        
        
            }
         
     
            return view('housingmovements::rooms.index')->with(compact('buildings'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::create');
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
            $input = $request->only(['room_number', 'htr_building', 'area', 'beds_count', 'contents']);
            
            
            $input2['room_number'] = $input['room_number'];
            $input2['htr_building_id'] = $input['htr_building'];
            $input2['area'] = $input['area'];
            $input2['beds_count'] = $input['beds_count'];
            $input2['contents'] = $input['contents'];
           
            DB::table('htr_rooms')->insert($input2);
      
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

     
       return redirect()->route('rooms');
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



        $room = DB::table('htr_rooms')->find($id);
        $buildings = DB::table('htr_buildings')->get()->pluck('name','id');

   
        return view('housingmovements::rooms.edit')->with(compact('room','buildings'));
    }

 
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            $input = $request->only(['room_number', 'htr_building', 'area', 'beds_count', 'contents']);
            

            $input2['room_number'] = $input['room_number'];
            $input2['htr_building_id'] = $input['htr_building'];
            $input2['area'] = $input['area'];
            $input2['beds_count'] = $input['beds_count'];
            $input2['contents'] = $input['contents'];
           
         
            DB::table('htr_rooms')->where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('rooms');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            DB::table('htr_rooms')->where('id', $id)
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
