<?php

namespace Modules\HousingMovements\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;
use App\User;
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

            if (!empty(request()->input('room_status')) && request()->input('room_status') !== 'all') {
                if (request()->input('room_status') === 'busy') {
                    $rooms->where('beds_count', '=', 0);
                }
                else{ $rooms->where('beds_count', '>', 0);}
               
            }
            return Datatables::of($rooms)


            ->editColumn('htr_building_id',function($row)use($buildings){
                $item = $buildings[$row->htr_building_id]??'';

                return $item;
            })
            
           ->addColumn('checkbox', function ($row) {
            return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
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
          
            ->rawColumns(['action'])
            ->make(true);
        
        
            }
         
            $workers = User::where('user_type', 'worker')->select(
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
             ' - ',COALESCE(id_proof_number,'')) as full_name")
            )->pluck('full_name', 'id');

            $roomStatusOptions = [
                'busy' => __('housingmovements::lang.busy_rooms'),
                'available' => __('housingmovements::lang.available_rooms'),
            ];
            return view('housingmovements::rooms.index')->with(compact('buildings','workers','roomStatusOptions'));

    }

    public function postRoomsData(Request $request)
    {

    }
    public function getSelectedroomsData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');
        // dd(  $selectedRows);
       
        $rooms = HtrRoom::whereIn('id', $selectedRows)
        ->select('id as room_id', 'room_number as room_number')
        ->get();

    $workers = User::where('user_type', 'worker')->select(
        'id',
        DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
     ' - ',COALESCE(id_proof_number,'')) as full_name")
    )->pluck('full_name', 'id');

    $data = [
        'rooms' => $rooms,
        'workers' => $workers,
    ];
        return response()->json($data);
    }

   
   
    public function room_data(Request $request)
    {
        try {
            $requestData = $request->only(['room_number', 'room_id', 'worker_id']);

            $jsonData = [];
            
            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'worker_id' => $workerId,
                    'room_number' => isset($requestData['room_number'][$index]) ? $requestData['room_number'][$index] : null,
                    'room_id' => isset($requestData['room_id'][$index]) ? $requestData['room_id'][$index] : null,
                ];
            
                $jsonData[] = $jsonObject;
            }
            
            $jsonData = json_encode($jsonData);
    
           
          
            \Log::info('JSON Data: ' . $jsonData);
    
            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);
   
                DB::beginTransaction();
    
                foreach ($selectedData as $data) {
                   
                    $room = DB::table('htr_rooms')
                        ->where('id', $data['room_id'])
                        ->where('beds_count', '>', 0)
                        ->select('id', 'beds_count')
                        ->first();

                    if($room)
                    {
                        $htrroom_histoty= new   HtrRoomsWorkersHistory();
                        $htrroom_histoty->room_id = $data['room_id'];
                        $htrroom_histoty->worker_id =$data['worker_id'];
                        $htrroom_histoty->save();
  
                        DB::table('htr_rooms')
                        ->where('id', $data['room_id'])
                        ->decrement('beds_count');
                    }

                    else {
                        
                        DB::rollBack();
                        $output = ['success' => 0, 'msg' => __('lang_v1.no_available_beds')];
                        return redirect()->back()->withErrors([$output['msg']]);
                    }
                   

                }
    
            
                DB::commit();
                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            }
            
            else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }
    
     // return $jsonData;
        return redirect()->back()->with(['status' => $output]);
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
