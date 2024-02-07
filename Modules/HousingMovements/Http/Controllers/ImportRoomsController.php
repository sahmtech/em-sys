<?php

namespace Modules\HousingMovements\Http\Controllers;
use DB;
use Excel;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Modules\HousingMovements\Entities\HtrBuilding;
use Modules\HousingMovements\Entities\HtrRoom;

class ImportRoomsController extends Controller
{
    
    protected $moduleUtil;
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    } 

    public function index()
    {
       
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housingmovements_view_import_rooms = auth()->user()->can('housingmovements.view_import_rooms');
      
       
        if (!($is_admin || $can_housingmovements_view_import_rooms)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $zip_loaded = extension_loaded('zip') ? true : false;
        
        if ($zip_loaded === false) 
        {
            $output = ['success' => 0, 'msg' => 'Please install/enable PHP Zip archive for import',];
            return view('housingmovements::rooms.import_rooms')->with('notification', $output);
        } 
        else 
        {
            return view('housingmovements::rooms.import_rooms');
        }

      
    }

    public function sendImportRooms(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            if ($request->hasFile('rooms_csv'))
            {
                $file = $request->file('rooms_csv');
                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1);
                $formated_data = [];
                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();
                foreach ($imported_data as $key => $value)
                {
                    $row_no = $key ;
                    $emp_array = []; 
                    
                    $emp_array['room_number'] = $value[0]; 
                    if (empty($value[0])) 
                    {
                        $is_valid = false;
                        $error_msg = __('housingmovements::lang.room_number_required') .$row_no+1;
                        break;
                    }  

                    $emp_array['htr_building_id'] = $value[1];
                    if(!empty($value[1]))
                    {
                        $building = HtrBuilding::where('id', $emp_array['htr_building_id'])->first();
                        if (!$building)
                         {
                        
                            $is_valid = false;
                            $error_msg = __('housingmovements::lang.htr_building_id_not_found').$row_no+1;
                            break;
                         }
                    }
                    else
                    {
                        $is_valid = false;
                        $error_msg = __('housingmovements::lang.building_id_required') .$row_no+1;
                        break; 
                    }

                    $emp_array['area'] = $value[2]; 
                    if(!empty($value[2]) && !is_numeric($value[2]))
                    {
                        $is_valid = false;
                        $error_msg = __('housingmovements::lang.area_should_be_numeric') .$row_no+1;
                        break;  
                    }

                    $emp_array['beds_count'] = $value[3]; 
                    $emp_array['total_beds'] = $value[3];
                    $emp_array['contents'] = $value[4];  

                    $formated_data[] = $emp_array;  
                }
                if (!$is_valid) 
                {
                    throw new \Exception($error_msg);
                } 

                if (! empty($formated_data)) 
                {
                    foreach ($formated_data as $emp_data) {

                        $room =new HtrRoom();
                        $room->room_number= $emp_data['room_number'];
                        $room->htr_building_id= $emp_data['htr_building_id'];
                        $room->area= $emp_data['area'];
                        $room->beds_count= $emp_data['beds_count'];
                        $room->total_beds= $emp_data['total_beds'];
                        $room->contents= $emp_data['contents'];
                        $room->save();

                    }

                }
                DB::commit();
            }
 

        }
        catch (\Exception $e)
        {
 
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,'msg' =>$e->getMessage()
           // 'msg' => __('messages.something_went_wrong'),
            ];

            return redirect()->route('import_rooms')
            ->with('notification', $output);
        }

        $output = ['success' => 1,'msg' => __('messages.added_success'), ];
        return redirect()->route('rooms')
        ->with('notification', $output);
    }

   
  
}
