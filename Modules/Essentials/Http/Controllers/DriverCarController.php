<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\CarModel;
use Modules\HousingMovements\Entities\DriverCar;
use Yajra\DataTables\Facades\DataTables;


class DriverCarController extends Controller
{


    protected $commonUtil;
    protected $moduleUtil;

    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $car_driver_edit = auth()->user()->can('driver.edit');
        $car_driver_delete  = auth()->user()->can('driver.delete');


        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $carDrivers = DriverCar::whereIn('user_id', $userIds)->get();


        if (request()->ajax()) {

            if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {
                $car_ids = Car::where('car_model_id', request()->input('carTypeSelect'))->get()->pluck('id');
                $carDrivers = $carDrivers->whereIn('car_id', $car_ids);
            }

            if (!empty(request()->input('driver_select')) && request()->input('driver_select') !== 'all') {
                $carDrivers = $carDrivers->where('user_id', request()->input('driver_select'));
            }

            return DataTables::of($carDrivers)

                ->editColumn('driver', function ($row) {
                    return $row->user->id_proof_number . ' - ' . $row->user->first_name . ' ' . $row->user->last_name . ' - ' . $row->user->essentialsEmployeeAppointmets->specialization->name ?? '';
                })

                ->editColumn('car_typeModel', function ($row) {
                    return $row->car->CarModel->CarType->name_ar . ' - ' . $row->car->CarModel->name_ar ?? '';
                })

                ->editColumn('counter_number', function ($row) {
                    return $row->counter_number ?? '';
                })

                ->editColumn('delivery_date', function ($row) {
                    if ($row->delivery_date) {
                        return \Carbon\Carbon::parse($row->delivery_date)->format('Y-m-d');
                    }
                    return '';
                })
                ->editColumn('plate_number', function ($row) {
                    return $row->Car->plate_number ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $car_driver_edit, $car_driver_delete) {

                        $html = '';
                        if ($is_admin  ||  $car_driver_edit) {
                            $html .= '
                        <a href="' . action([\Modules\Essentials\Http\Controllers\DriverCarController::class, 'edit'], ['id' => $row->id]) . '"
                        data-href="' . action([\Modules\Essentials\Http\Controllers\DriverCarController::class, 'edit'], ['id' => $row->id]) . ' "
                         class="btn btn-xs btn-modal btn-info edit_user_button"  data-container="#edit_driver_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        }
                        if ($is_admin  || $car_driver_delete) {
                            $html .= '
                    <button data-href="' .  action([\Modules\Essentials\Http\Controllers\DriverCarController::class, 'destroy'], ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        }

                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'driver', 'car_typeModel', 'plate_number', 'counter_number', 'delivery_date'])
                ->make(true);
        }
        $car_Drivers = DriverCar::whereIn('user_id', $userIds)->get();

        $carTypes = CarModel::all();
        return view('essentials::movementMangment.driverCar.index', compact('car_Drivers', 'carTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $essentials_specializations_ids = EssentialsSpecialization::where('name', 'like', "%سائق%")->get()->pluck('id');
        $essentials_employee_appointmets_ids = EssentialsEmployeeAppointmet::whereIn('specialization_id', $essentials_specializations_ids)->get()->pluck('employee_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $driver_ids = DriverCar::whereIn('user_id', $userIds)->pluck('user_id');



        $workers = User::where('user_type', 'worker')
            ->whereIn('id', $essentials_employee_appointmets_ids)
            ->whereNotIn('id', $driver_ids)->get();
        if (count($workers) == 0) {
            $message = 'notFountAvilableWorkers';
            return view('essentials::movementMangment.driverCar.message', compact('message'));
        }
        $carDriver_ids = DriverCar::all()->pluck('car_id');

        $cars = Car::whereNotIn('id', $carDriver_ids)->get();
        if (count($cars) == 0) {
            $message = 'notFountAvilableCars';
            return view('essentials::movementMangment.driverCar.message', compact('message'));
        }



        return view('essentials::movementMangment.driverCar.create', compact('workers', 'cars'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $carImage_name = null;
            if ($request->hasFile('car_image')) {
                $image = $request->file('car_image');
                $carImage_name = $image->store('/cars_image');
            }
            DriverCar::create([
                'car_id' => $request->input('car_id'),
                'counter_number' => $request->input('counter_number'),
                'user_id' => $request->input('user_id'),
                'delivery_date' => $request->input('delivery_date'),
                'car_image' => $carImage_name,

            ]);

            DB::commit();
            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('housingmovements::lang.added_success'),
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
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
        $essentials_specializations_ids = EssentialsSpecialization::where('name', 'like', "%سائق%")->get()->pluck('id');
        $essentials_employee_appointmets_ids = EssentialsEmployeeAppointmet::whereIn('specialization_id', $essentials_specializations_ids)->get()->pluck('employee_id');
        $driver = DriverCar::find($id);
        $driver_ids = DriverCar::all()->pluck('user_id');

        $workers = User::where('user_type', 'worker')
            ->whereIn('id', $essentials_employee_appointmets_ids)
            ->whereNotIn('id', $driver_ids)
            ->orwhere('id', $driver->user_id)
            ->get();
        if (count($workers) == 0) {
            $message = 'notFountAvilableWorkers';
            return view('essentials::movementMangment.driverCar.message', compact('message'));
        }
        $carDriver_ids = DriverCar::all()->pluck('car_id');

        $cars = Car::whereNotIn('id', $carDriver_ids)
            ->orwhere('id', $driver->car_id)->get();



        return view('essentials::movementMangment.driverCar.edit', compact('workers', 'cars', 'driver'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $update_data = [];
            if ($request->hasFile('car_image')) {
                $image = $request->file('car_image');
                $update_data['car_image'] = $image->store('/cars_image');
            }
            $update_data['car_id'] = $request->input('car_id');
            $update_data['counter_number'] = $request->input('counter_number');
            $update_data['user_id'] = $request->input('user_id');
            $update_data['delivery_date'] =  $request->input('delivery_date');
            $driver = DriverCar::find($id);
            $driver->update($update_data);

            DB::commit();
            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('housingmovements::lang.updated_success'),
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            try {
                DriverCar::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف السائق بنجاح',
                ];
            } catch (Exception $e) {
                return redirect()->back()
                    ->with('status', [
                        'success' => false,
                        'msg' => __('messages.something_went_wrong'),
                    ]);
            }
            return $output;
        }
    }
}
