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


        $can_crud_rooms = auth()->user()->can('housingmovements.crud_rooms');
        if (! $can_crud_rooms) {
           
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
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
                function ($row)  {
                    $html = '';
                  
                        $html .= '<a href="'. route('show_room_workers', ['id' => $row->id]) .  '" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-eye"></i> '.__('housingmovements::lang.show_room_workers').'</a>

                        &nbsp;';

                        $html .= '<button class="btn btn-xs btn-primary open-edit-modal" data-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</button>';

                        
                        $html .= '<button class="btn btn-xs btn-danger delete_room_button" data-href="' . route('room.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                  
        
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

    public function emptyRooms() {
    
        $business_id = request()->session()->get('user.business_id');
 
 
         $can_crud_rooms = auth()->user()->can('housingmovements.crud_rooms');
         if (! $can_crud_rooms) {
            
         }
         $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
         $buildings=DB::table('htr_buildings')->get()->pluck('name','id');
         if (request()->ajax()) {
 
            $HtrRoomsWorkersHistory_roomIds= HtrRoomsWorkersHistory::all()->pluck('room_id');
            $empty_rooms= HtrRoom::whereNotIn('id',$HtrRoomsWorkersHistory_roomIds);
            
             if (!empty(request()->input('htr_building')) && request()->input('htr_building') !== 'all') {
                 $empty_rooms->where('htr_building_id', request()->input('htr_building'));
             }
 
             if (!empty(request()->input('room_status')) && request()->input('room_status') !== 'all') {
                 if (request()->input('room_status') === 'busy') {
                     $empty_rooms->where('beds_count', '=', 0);
                 }
                 else{ $empty_rooms->where('beds_count', '>', 0);}
                
             }
             return Datatables::of($empty_rooms)
 
 
             ->editColumn('htr_building_id',function($row)use($buildings){
                 $item = $buildings[$row->htr_building_id]??'';
 
                 return $item;
             })
             
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
             return view('housingmovements::rooms.emptyRooms')->with(compact('buildings','workers','roomStatusOptions'));
 
     }

    public function show_room_workers($id)
    {
        $roomWorkersHistory = HtrRoomsWorkersHistory::where('room_id', $id)
            ->where('still_housed',1)
            ->get();
    
        $userIds = $roomWorkersHistory->pluck('worker_id');
    
        $users = User::whereIn('id', $userIds)
            ->with([
                'country',
                'appointment.profession',
                'UserallowancesAndDeductions',
                'appointment.location',
                'contract',
                'OfficialDocument',
                'workCard'
            ])
            ->get();
    
        return view('housingmovements::room_workers.index', ['users' => $users, 'roomWorkersHistory' => $roomWorkersHistory]);
    }
    


    public function postRoomsData(Request $request)
    {

    }
    public function getSelectedroomsData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');
    
        $data = [
            'rooms' => [],
            'workers' => [],
            'otherRooms'=>[],
        ];
    
        foreach ($selectedRows as $roomId) {
            $room = HtrRoom::find($roomId);
            $otherRooms = HtrRoom::where('id', '!=', $roomId)->get();
            
            foreach ($otherRooms as $otherRoom) {
                
                $data['otherRooms'][] = [
                    'room_id' => $otherRoom->id,
                    'room_number' => $otherRoom->room_number,
                    'beds_count' => $otherRoom->beds_count,
                ];
            }



            if ($room) {
                $existingWorkerIds = HtrRoomsWorkersHistory::where('room_id', $roomId)
                   ->where('still_housed',1)
                    ->pluck('worker_id')
                    ->toArray();
                  
                    if ($room->beds_count == 0) {
                        $historyWorkerIds = HtrRoomsWorkersHistory::where('room_id', '=', $roomId)
                            ->where('still_housed',1)
                            ->pluck('worker_id')
                            ->toArray();
                                       
                        $workers = User::whereIn('id', $historyWorkerIds)
                            ->select(
                                'id',
                                DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                                    ' - ',COALESCE(id_proof_number,'')) as full_name")
                            )
                            ->pluck('full_name', 'id');
                    }

                 else {
                    
                    $workers = User::whereNotIn('id', $existingWorkerIds)
                        ->select(
                            'id',
                            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                                ' - ',COALESCE(id_proof_number,'')) as full_name")
                        )
                        ->pluck('full_name', 'id');
                }

    
                $data['rooms'][] = [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'beds_count' => $room->beds_count,
                ];
    
                $data['workers'][$room->id] = $workers;
            }
        }
    
        return response()->json($data);
    }
    
    

   
    public function room_data(Request $request)
    {
        try {
            $jsonData = $request->input('roomData');
            
            \Log::info('JSON Data: ' . $jsonData);
    
            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);
                DB::beginTransaction();
    
                foreach ($selectedData as $roomNumber => $roomData) {
                    foreach ($roomData['worker_ids'] as $workerId) {
                        $room = DB::table('htr_rooms')
                            ->where('room_number', $roomNumber)
                            ->select('id', 'beds_count')
                            ->first();
    
                        if ($room) {
                            
                            if (!empty($roomData['transfer_to_room_ids']) && count($roomData['transfer_to_room_ids']) > 0) {
                                foreach ($roomData['transfer_to_room_ids'] as $transferRoomId) {
                                    $existingHistory = HtrRoomsWorkersHistory::where('worker_id', $workerId)
                                        ->where('room_id', $room->id)
                                        ->where('still_housed', 1)
                                        ->first();
    
                                    if ($existingHistory) {
                                        $existingHistory->still_housed = 0;
                                        $existingHistory->save();
    
                                        DB::table('htr_rooms')
                                            ->where('id', $existingHistory->room_id)
                                            ->increment('beds_count');
                                    }
    
                                    
                                    $transferHistory = new HtrRoomsWorkersHistory();
                                    $transferHistory->room_id = $transferRoomId;
                                    $transferHistory->worker_id = $workerId;
                                    $transferHistory->still_housed = 1;
                                    $transferHistory->save();
    
                                    
                                    $user = User::find($workerId);
                                    $user->update(['room_id' => $transferRoomId]);
    
                                    
                                    DB::table('htr_rooms')
                                        ->where('id', $transferRoomId)
                                        ->decrement('beds_count');
                                }
                            } else {
                                
                                $existingHistory = HtrRoomsWorkersHistory::where('worker_id', $workerId)
                                    ->where('still_housed', 1)
                                    ->first();
    
                                if ($existingHistory) {
                                    $existingHistory->still_housed = 0;
                                    $existingHistory->save();
    
                                    DB::table('htr_rooms')
                                        ->where('id', $existingHistory->room_id)
                                        ->increment('beds_count');
                                }
    
                                $htrroom_histoty = new HtrRoomsWorkersHistory();
                                $htrroom_histoty->room_id = $room->id;
                                $htrroom_histoty->worker_id = $workerId;
                                $htrroom_histoty->still_housed = 1;
                                $htrroom_histoty->save();
    
                                $user = User::find($workerId);
                                $user->update(['room_id' => $room->id]);
    
                                DB::table('htr_rooms')
                                    ->where('id', $room->id)
                                    ->decrement('beds_count');
                            }
                        } 
                    }
                }
    
                DB::commit();
                $output = ['success' => 1, 'msg' => __('housingmovements::lang.housed_sucess')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }
    
        return response()->json($output);
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
      

        
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
    public function edit($roomId)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $room = DB::table('htr_rooms')->find($roomId);
        $buildings = DB::table('htr_buildings')->get()->pluck('name','id');
        $output = [
            'success' => true,
            'data' => [
                'room' => $room,
                'buildings' => $buildings,
            ],
            'msg' => __('lang_v1.fetched_success'),
        ];
   
        return response()->json($output);
    }

 
    public function update(Request $request, $roomId)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['room_number', 'htr_building', 'area', 'beds_count', 'contents']);
            

            $input2['room_number'] = $input['room_number'];
            $input2['htr_building_id'] = $input['htr_building'];
            $input2['area'] = $input['area'];
            $input2['beds_count'] = $input['beds_count'];
            $input2['contents'] = $input['contents'];
           
         
            DB::table('htr_rooms')->where('id', $roomId)->update($input2);


            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];


        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return response()->json($output);
    }



    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('htr_rooms')->where('id', $id)
                        ->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }
       
       return $output;

    }
}