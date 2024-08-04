<?php

namespace Modules\HousingMovements\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\NewArrivalUtil;

use App\Utils\Util;
use App\User;
use App\Events\ContactCreatedOrModified;
use Modules\Sales\Entities\SalesProject;
use App\Transaction;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsCity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;
use Modules\InternationalRelations\Entities\IrDelegation;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Illuminate\Support\Facades\Auth;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;

class TravelersController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;
    protected $newArrivalUtil;



    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil,
        NewArrivalUtil $newArrivalUtil

    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
        $this->newArrivalUtil = $newArrivalUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $view = 'housingmovements::travelers.index';
        return $this->newArrivalUtil->new_arrival_for_workers($request, $view);
    }

    public function getSelectedRowsData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');


        $data = IrProposedLabor::whereIn('id', $selectedRows)
            ->select(
                'id as worker_id',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as worker_name"),
                'passport_number'
            )

            ->get();




        return response()->json($data);
    }


    public function  housed_workers_index(Request $request)
    {
        $view = 'housingmovements::travelers.partials.housed_workers';
        return $this->newArrivalUtil->housed_workers_index($request, $view);
    }


    public function getRoomNumberOnStatus(Request $request)
    {
        $input = $request->input('option');
        $input_building = $request->input('htr_building');
        $roomNumbers = [];
        if ($input == "busy") {

            $rooms = DB::table('htr_rooms')
                ->where('htr_building_id', $input_building)
                ->where('beds_count', '=', 0)
                ->get(['id', 'room_number', 'beds_count']);

            foreach ($rooms as $room) {
                $roomNumbers[] = [
                    'id' => $room->id,
                    'text' => $room->room_number,
                    'beds_count' => $room->beds_count,
                ];
            }
        } else if ($input == "available") {
            $rooms = DB::table('htr_rooms')
                ->where('htr_building_id', $input_building)
                ->where('beds_count', '>', 0)
                ->get(['id', 'room_number', 'beds_count']);

            foreach ($rooms as $room) {
                $roomNumbers[] = [
                    'id' => $room->id,
                    'text' => $room->room_number,
                    'beds_count' => $room->beds_count,
                ];
            }
        }

        return response()->json($roomNumbers);
    }


    public function housed_data(Request $request)
    {

        try {


            $selectedRowsData = json_decode($request->input('selectedRowsData'), true);

            if (empty($selectedRowsData)) {
                return [
                    'success' => false,
                    'msg' => __('housingmovements::lang.please_select_rows'),
                ];
            }
            $room = HtrRoom::where('id', $request->room)->first();
            if (count($selectedRowsData) > $room->beds_count) {
                return [
                    'success' => false,
                    'msg' => __('housingmovements::lang.the number of users is more the number of available beds in this room'),
                ];
            }

            foreach ($selectedRowsData as $row) {

                $id = $row['id'];

                $carbon_now = \Carbon::now();

                $user = User::where('proposal_worker_id', $id)->first();
                if ($user) {
                    $userId = User::where('proposal_worker_id', $id)->first()->id;
                } else {
                    return [
                        'success' => false,
                        'msg' => __('housingmovements::lang.this user does not exist'),
                    ];
                }

                $newRoom = new HtrRoomsWorkersHistory();
                $newRoom->worker_id = $userId;
                $newRoom->room_id = $request->room;
                $newRoom->still_housed = 1;
                $newRoom->housed_date = $carbon_now;
                $newRoom->save();

                $user = User::find($userId);
                $user->update(['room_id' => $request->room, 'updated_by' => auth()->user()->id]);

                $room = HtrRoom::where('id', $request->room)->first();
                if ($room) {
                    $room->beds_count -= 1;
                    $room->save();
                }

                IrProposedLabor::find($id)->update(['housed_status' => 1]);
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
    //public function housed_data(Request $request)
    // {
    //     try {
    //         $requestData = $request->only(['htr_building', 'room_number', 'worker_id', 'shift_name']);

    //         $commonRoomNumber = isset($requestData['room_number'][0]) ? $requestData['room_number'][0] : null;
    //         $shift = isset($requestData['shift_name']) ? $requestData['shift_name'] : null;

    //         $jsonData = [];

    //         foreach ($requestData['worker_id'] as $index => $workerId) {

    //             $jsonObject = [
    //                 'worker_id' => $workerId,
    //                 'room_number' => $commonRoomNumber,
    //                 'shift_id' => $shift,
    //             ];

    //             $jsonData[] = $jsonObject;
    //         }

    //         $jsonData = json_encode($jsonData);

    //         \Log::info('JSON Data: ' . $jsonData);

    //         if (!empty($jsonData)) {
    //             $selectedData = json_decode($jsonData, true);

    //             DB::beginTransaction();

    //             foreach ($selectedData as $data) {

    //                 $room = DB::table('htr_rooms')
    //                     ->where('id', $data['room_number'])
    //                     ->where('beds_count', '>', 0)
    //                     ->select('id', 'beds_count')
    //                     ->first();

    //                 $worker = IrProposedLabor::find($data['worker_id']);

    //                 if ($room) {

    //                     User::where('proposal_worker_id', $data['worker_id'])
    //                         ->update([
    //                             'room_id' => $room->id,
    //                         ]);


    //                     DB::table('htr_rooms')
    //                         ->where('id', $room->id)
    //                         ->decrement('beds_count');



    //                     $worker->update(['housed_status' => 1]);


    //                     if ($shift) {
    //                         $user = User::where('proposal_worker_id', $data['worker_id'])->first();
    //                         $user_shifts = DB::table('essentials_user_shifts')->insert([
    //                             'user_id' => $user->id,
    //                             'essentials_shift_id' =>  $shift,
    //                         ]);
    //                     }
    //                 } else {

    //                     DB::rollBack();
    //                     $output = ['success' => 0, 'msg' => __('lang_v1.no_available_beds')];
    //                     return response()->json($output);
    //                 }
    //             }


    //             DB::commit();
    //             $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
    //         } else {
    //             $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
    //         }
    //     } catch (\Exception $e) {
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = ['success' => 0, 'msg' => $e->getMessage()];
    //     }

    //     //  return $output;
    //     return redirect()->back()->with(['status' => $output]);
    // }





    public function getRoomNumbers($buildingId)
    {
        $roomNumbers = DB::table('htr_rooms')->where('htr_building_id', $buildingId)->pluck('room_number', 'id');

        return response()->json(['roomNumber' => $roomNumbers]);
    }


    public function getBedsCount($roomId)
    {
        $roomNumbers = DB::table('htr_rooms')->where('id', $roomId)->pluck('beds_count', 'id');

        return response()->json(['roomNumber' => $roomNumbers]);
    }


    public function getShifts($projectId)
    {
        $shifts = DB::table('essentials_shifts')->where('project_id', $projectId)->pluck('name', 'id');

        return response()->json(['shifts' => $shifts]);
    }



    public function postarrivaldata(Request $request)
    {

        try {
            $requestData = $request->only([
                'worker_id',
                'worker_name',
                'passport_number',
                'border_no',

            ]);

            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'worker_id' => $workerId,
                    'worker_name' => $requestData['worker_name'][$index],
                    'passport_number' => $requestData['passport_number'][$index],
                    'border_no' => $requestData['border_no'][$index],
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            if (!empty($jsonData)) {
                $business_id = $request->session()->get('user.business_id');
                $selectedData = json_decode($jsonData, true);


                foreach ($selectedData as $data) {

                    $worker = IrProposedLabor::with('visa')->find($data['worker_id']);

                    if ($worker) {
                        $border_number = User::where('border_no', $data['border_no'])->first();

                        if ($border_number != null) {
                            $output = ['success' => 0, 'msg' => __('housingmovements.border_no_exist')];
                        } else {


                            $user = User::create([
                                'first_name' => $worker->first_name,
                                'mid_name' => $worker->mid_name,
                                'last_name' => $worker->last_name,
                                'age' => $worker->age,
                                'gender' => $worker->gender,
                                'email' => $worker->email,
                                'profile_image' => $worker->profile_image,
                                'dob' => $worker->dob,
                                'marital_status' => $worker->marital_status,
                                'blood_group' => $worker->blood_group,
                                'assigned_to' => $worker->transactionSellLine?->transaction?->salesContract->project->id,
                                'contact_number' => $worker->contact_number,
                                'permanent_address' => $worker->permanent_address,
                                'current_address' => $worker->current_address,
                                'passport_number' => $worker->passport_number,
                                'nationality_id' => $worker->transactionSellLine?->service?->nationality?->id ?? null,
                                'business_id' => $business_id,
                                'user_type' => 'worker',
                                'border_no' => $data['border_no'],
                                'proposal_worker_id' => $data['worker_id'],
                                'created_by' => Auth::user()->id,


                            ]);

                            $admission = new EssentialsAdmissionToWork();
                            $admission->employee_id = $user->id;
                            $admission->admissions_type = 'first_time';
                            $admission->admissions_status = 'on_date';
                            $admission->admissions_date = $worker->arrival_date;
                            $admission->created_by = Auth::user()->id;
                            $admission->save();

                            // $worker->update(['arrival_status' => 1]);
                            $worker->arrival_status = 1;
                            $worker->save();

                            $allWorkersArrived =
                                IrProposedLabor::where('visa_id', $worker->visa_id)
                                ->where('arrival_status', 1)
                                ->count()
                                ==
                                IrProposedLabor::where('visa_id', $worker->visa_id)
                                ->count();

                            if ($allWorkersArrived) {

                                $visa = $worker->visa;
                                if ($visa) {
                                    $visa->update(['status' => 1]);
                                }
                            }
                        }
                    }
                    if ($worker->transaction_sell_line_id) {
                        $proposalWorkersCount = IrProposedLabor::where('transaction_sell_line_id', $worker->transaction_sell_line_id)->count();
                        $proposalWorkersWithArrivalStatus1Count = IrProposedLabor::where('transaction_sell_line_id', $worker->transaction_sell_line_id)
                            ->where('arrival_status', 1)
                            ->count();

                        if ($proposalWorkersCount > 0 && $proposalWorkersCount === $proposalWorkersWithArrivalStatus1Count) {

                            $orderOperation = $worker->transactionSellLine?->transaction?->salesContract?->salesOrderOperation;
                            $orderOperation->update(['status' => 'done']);
                        } else {
                            $orderOperation = $worker->transactionSellLine?->transaction?->salesContract?->salesOrderOperation;
                            $orderOperation->update(['status' => 'under_process']);
                        }
                    }
                }




                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => ('messages.somtheing_went_wrong')];
        }

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
        //
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
        return view('housingmovements::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
