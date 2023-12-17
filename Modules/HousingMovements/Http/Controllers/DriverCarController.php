<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use App\Utils\Util;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\DriverCar;
use Yajra\DataTables\Facades\DataTables;

class DriverCarController extends Controller
{

    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

        $carDrivers = DriverCar::all();
        if (request()->ajax()) {

            // if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {
            //     // $CarModel = CarType::find()->CarModel;

            //     $carDrivers = $carDrivers->where('car_model_id', request()->input('carTypeSelect'));
            //     // $Cars = $Cars->whereIn('car_model_id', $CarModel_ids);
            // }

            // if (!empty(request()->input('driver_select')) && request()->input('driver_select') !== 'all') {

            //     $Cars = $Cars->where('user_id', request()->input('driver_select'));
            // }

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

                ->addColumn('action', function ($row) {
                    $html = '';
                    $html = '<div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button"
                        style="background-color: transparent;
                    font-size: x-large;
                    padding: 0px 20px;"
                        class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cog" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item btn-modal" style="margin: 2px;"
                            title="تعديل"
                            href="' . route('cardrivers.edit', ['id' => $row->id]) . ' "
                            data-href="' . route('cardrivers.edit', ['id' => $row->id]) . ' "
                            data-container="#edit_driver_model">

                            <i class="fas fa-edit cursor-pointer"
                                style="padding: 2px;color:rgb(8, 158, 16);"></i>
                            تعديل </a>

                        <a class="dropdown-item btn-modal" style="margin: 2px;" 
                            href="' . route('cardrivers.delete', ['id' => $row->id]) . '"
                            data-href="' . route('cardrivers.delete', ['id' => $row->id]) . '"
                            {{-- data-target="#active_auto_migration" data-toggle="modal" --}} {{-- id="delete_auto_migration" --}}>

                            <i class="fa fa-trash cursor-pointer"
                                style="padding: 2px;color:red;"></i>
                            حذف

                        </a>
                    </div>
                </div>';
                    return $html;
                })
                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'driver', 'car_typeModel', 'plate_number', 'counter_number', 'delivery_date'])
                ->make(true);
        }
        return view('housingmovements::movementMangment.driverCar.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $essentials_specializations_ids = EssentialsSpecialization::where('name', 'like', "%سائق%")->get()->pluck('id');
        $essentials_employee_appointmets_ids = EssentialsEmployeeAppointmet::whereIn('specialization_id', $essentials_specializations_ids)->get()->pluck('employee_id');
        $driver_ids = DriverCar::all()->pluck('user_id');

        $workers = User::where('user_type', 'worker')
            ->whereIn('id', $essentials_employee_appointmets_ids)
            ->whereNotIn('id', $driver_ids)->get();
        if (count($workers) == 0) {
            $message = 'notFountAvilableWorkers';
            return view('housingmovements::movementMangment.driverCar.message', compact('message'));
        }
        $carDriver_ids = DriverCar::all()->pluck('car_id');

        $cars = Car::whereNotIn('id', $carDriver_ids)->get();
        if (count($cars) == 0) {
            $message = 'notFountAvilableCars';
            return view('housingmovements::movementMangment.driverCar.message', compact('message'));
        }



        return view('housingmovements::movementMangment.driverCar.create', compact('workers', 'cars'));
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

            $output = [
                'success' => true,
                'msg' => __('account.account_updated_success'),
            ];
            DB::commit();
            return redirect()->back()->with(__('account.account_updated_success'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
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
            return view('housingmovements::movementMangment.driverCar.message', compact('message'));
        }
        $carDriver_ids = DriverCar::all()->pluck('car_id');

        $cars = Car::whereNotIn('id', $carDriver_ids)
            ->orwhere('id', $driver->car_id)->get();



        return view('housingmovements::movementMangment.driverCar.edit', compact('workers', 'cars', 'driver'));
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
            $output = [
                'success' => true,
                'msg' => __('account.account_updated_success'),
            ];
            DB::commit();
            return redirect()->back()->with(__('account.account_updated_success'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try {
            DriverCar::find($id)->delete();
            return redirect()->back()->with(__('deleted_success'));
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}