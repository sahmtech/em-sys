<?php

namespace Modules\HousingMovements\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\ContactLocation;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\User;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Events\ContactCreatedOrModified;
use Modules\Sales\Entities\SalesProject;
use App\Transaction;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsCity;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;
use Modules\InternationalRelations\Entities\IrDelegation;
use Modules\InternationalRelations\Entities\IrProposedLabor;

class TravelersController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;


    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housing_crud_htr_trevelers = auth()->user()->can('housingmovements.crud_htr_trevelers');
        if (!($is_admin || $can_housing_crud_htr_trevelers)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $workers = IrProposedLabor::with([
            'transactionSellLine.service.profession',
            'transactionSellLine.service.nationality',
            'transactionSellLine.transaction.salesContract.salesOrderOperation.contact',
            'transactionSellLine.transaction.salesContract.project',
            'visa',
            'agency'
        ])
            ->select([
                'ir_proposed_labors.id',
                'passport_number',
                'medical_examination',
                'transaction_sell_line_id',
                'visa_id',
                'arrival_date',
                'agency_id',
                DB::raw("CONCAT(COALESCE(first_name, ''),
                 ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ])
            ->whereNotNull('visa_id')
            ->where('interviewStatus', 'acceptable')
            ->where('arrival_status', 0);



        if (!empty($request->input('project_name_filter'))) {
        }
        if (!empty(request()->input('project_name_filter')) && request()->input('project_name_filter') !== 'all') {

            if (request()->input('project_name_filter') == 'none') {
                $workers->whereNull('transaction_sell_line_id');
            } else {
                $workers->whereHas('transactionSellLine.transaction.salesContract.project', function ($query) use ($request) {
                    $query->where('id', '=', $request->input('project_name_filter'));
                });
            }
        }

        if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
            $start = request()->filter_start_date;
            $end = request()->filter_end_date;

            $workers->whereHas('visa', function ($query) use ($start, $end) {
                $query->whereDate('arrival_date', '>=', $start)
                    ->whereDate('arrival_date', '<=', $end);
            });
        }


        if (request()->ajax()) {


            return Datatables::of($workers)


                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })

                ->editColumn('project', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->project?->name ?? '';
                })

                ->editColumn('location', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->project?->Location ?? '';
                })

                ->editColumn('arrival_date', function ($row) {
                    return $row->arrival_date ?? '';
                })

                ->editColumn('profession', function ($row) {
                    return $row->transactionSellLine?->service?->profession?->name ?? '';
                })
                ->editColumn('nationality', function ($row) {
                    return $row->transactionSellLine?->service?->nationality?->nationality ?? '';
                })


                ->filter(function ($query) use ($request) {

                    if (!empty($request->input('full_name'))) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
                    }
                })

                ->rawColumns(['action', 'profession', 'nationality', 'checkbox'])
                ->make(true);
        }

        $buildings = DB::table('htr_buildings')->get()->pluck('name', 'id');

        $salesProjects = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();

        $roomStatusOptions = [
            'busy' => __('housingmovements::lang.busy_rooms'),
            'available' => __('housingmovements::lang.available_rooms'),
        ];
        return view('housingmovements::travelers.index')->with(compact('salesProjects', 'buildings', 'roomStatusOptions'));
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
        $business_id = request()->session()->get('user.business_id');
        // if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create')) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }
        $buildings = DB::table('htr_buildings')->get()->pluck('name', 'id');
        $availableRooms = HtrRoom::where('beds_count', '>', 0)->pluck('room_number', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $workers = IrProposedLabor::with([
            'transactionSellLine.service.profession',
            'transactionSellLine.service.nationality',
            'transactionSellLine.transaction.salesContract.salesOrderOperation.contact',
            'transactionSellLine.transaction.salesContract.project',
            'visa',
            'agency'
        ])
            ->select([
                'ir_proposed_labors.id',
                'passport_number',
                'arrival_date',
                'transaction_sell_line_id',
                'visa_id',
                'agency_id',
                DB::raw("CONCAT(COALESCE(first_name, ''),
                 ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ])
            ->whereNotNull('visa_id')
            ->where('interviewStatus', 'acceptable')
            ->where('arrival_status', 1)
            ->where('housed_status', 0);




        if (!empty($request->input('project_name_filter'))) {
            $workers->whereHas('transactionSellLine.transaction.salesContract.project', function ($query) use ($request) {
                $query->where('id', '=', $request->input('project_name_filter'));
            });
        }

        if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
            $start = request()->filter_start_date;
            $end = request()->filter_end_date;

            $workers->whereHas('visa', function ($query) use ($start, $end) {
                $query->whereDate('arrival_date', '>=', $start)
                    ->whereDate('arrival_date', '<=', $end);
            });
        }


        if (request()->ajax()) {


            return Datatables::of($workers)


                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })

                ->editColumn('project', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->project?->name ?? '';
                })

                ->editColumn('location', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->salesOrderOperation?->Location ?? '';
                })

                ->editColumn('arrival_date', function ($row) {
                    return $row->arrival_date ?? '';
                })

                ->editColumn('profession', function ($row) {
                    return $row->transactionSellLine?->service?->profession?->name ?? '';
                })
                ->editColumn('nationality', function ($row) {
                    return $row->transactionSellLine?->service?->nationality?->nationality ?? '';
                })

                ->filter(function ($query) use ($request) {

                    if (!empty($request->input('full_name'))) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
                    }
                })

                ->rawColumns(['action', 'profession', 'nationality', 'checkbox'])
                ->make(true);
        }


        $salesProjects = SalesProject::all()->pluck('name', 'id');
        $roomStatusOptions = [
            'busy' => __('housingmovements::lang.busy_rooms'),
            'available' => __('housingmovements::lang.available_rooms'),
        ];
        return view('housingmovements::travelers.partials.housed_workers')->with(compact('salesProjects', 'buildings', 'availableRooms', 'roomStatusOptions'));
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

                $userId = User::where('proposal_worker_id', $id)->first()->id;

                $newRoom = new HtrRoomsWorkersHistory();
                $newRoom->worker_id = $userId;
                $newRoom->room_id = $request->room;
                $newRoom->still_housed = 1;
                $newRoom->housed_date = $carbon_now;
                $newRoom->save();

                $user = User::find($userId);
                $user->update(['room_id' => $request->room]);

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

                DB::beginTransaction();
                foreach ($selectedData as $data) {

                    $worker = IrProposedLabor::find($data['worker_id']);
                    //dd($worker->transactionSellLine?->transaction?->salesContract->project->id);

                    if ($worker) {
                        User::create([
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

                        ]);
                        $worker->update(['arrival_status' => 1]);
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


                DB::commit();

                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => $e->getMessage()];
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
