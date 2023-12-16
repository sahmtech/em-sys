<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\CarModel;
use Modules\HousingMovements\Entities\CarType;
use Yajra\DataTables\Facades\DataTables;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $Cars = Car::all();
        $carTypes = CarModel::all();
        $after_serch = false;
        $essentials_specializations_ids = EssentialsSpecialization::where('name', 'like', "%سائق%")->get()->pluck('id');
        $essentials_employee_appointmets_ids = EssentialsEmployeeAppointmet::whereIn('specialization_id', $essentials_specializations_ids)->get()->pluck('employee_id');
        $drivers = User::where('user_type', 'worker')
            ->whereIn('id', $essentials_employee_appointmets_ids)->get();

        if (request()->ajax()) {

            if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {
                // $CarModel = CarType::find()->CarModel;

                $Cars = $Cars->where('car_model_id', request()->input('carTypeSelect'));
                // $Cars = $Cars->whereIn('car_model_id', $CarModel_ids);
            }

            if (!empty(request()->input('driver_select')) && request()->input('driver_select') !== 'all') {

                $Cars = $Cars->where('user_id', request()->input('driver_select'));
            }

            return DataTables::of($Cars)



                ->editColumn('driver', function ($row) {
                    return $row->User->id_proof_number . ' - ' . $row->User->first_name . ' ' . $row->User->last_name . ' - ' . $row->User->essentialsEmployeeAppointmets->specialization->name ?? '';
                })

                ->editColumn('car_typeModel', function ($row) {
                    return $row->CarModel->CarType->name_ar . ' - ' . $row->CarModel->name_ar ?? '';
                })

                ->editColumn('plate_number', function ($row) {
                    return $row->plate_number ?? '';
                })

                ->editColumn('number_seats', function ($row) {
                    return  $row->number_seats ?? '';
                })
                ->editColumn('color', function ($row) {
                    return $row->color ?? '';
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
                            href="' . route('car.edit', ['id' => $row->id]) . ' "
                            data-href="' . route('car.edit', ['id' => $row->id]) . ' "
                            data-container="#edit_car_model">

                            <i class="fas fa-edit cursor-pointer"
                                style="padding: 2px;color:rgb(8, 158, 16);"></i>
                            تعديل </a>

                        <a class="dropdown-item btn-modal" style="margin: 2px;" 
                            href="' . route('car.delete', ['id' => $row->id]) . '"
                            data-href="' . route('car.delete', ['id' => $row->id]) . '"
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

                ->rawColumns(['action', 'driver', 'car_typeModel', 'plate_number', 'number_seats', 'color'])
                ->make(true);
        }
        return view('housingmovements::movementMangment.cars.index', compact('carTypes', 'after_serch', 'drivers', 'Cars'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {


        $carTypes = CarType::all();
        $essentials_specializations_ids = EssentialsSpecialization::where('name', 'like', "%سائق%")->get()->pluck('id');
        $essentials_employee_appointmets_ids = EssentialsEmployeeAppointmet::whereIn('specialization_id', $essentials_specializations_ids)->get()->pluck('employee_id');
        $workers = User::where('user_type', 'worker')
            ->whereIn('id', $essentials_employee_appointmets_ids)->get();

        return view('housingmovements::movementMangment.cars.create', compact('carTypes', 'workers'));
    }

    // 	
    public function getCarModelByCarType_id($carType_id)
    {
        if (request()->ajax()) {
            $carType = CarType::find($carType_id);
            return $carModels = $carType->CarModel;
        }
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

            Car::create([
                'plate_number' => $request->input('plate_number'),
                'color' => $request->input('color'),
                'user_id' => $request->input('user_id'),
                'car_model_id' => $request->input('car_model_id'),
                'number_seats' => $request->input('number_seats'),

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
        $car = Car::find($id);
        if ($car) {
            $carModel = CarModel::find($car->car_model_id);
            $carModels = CarModel::where('car_type_id', $carModel->car_type_id)->get();
            $carTypes = CarType::all();
            $essentials_specializations_ids = EssentialsSpecialization::where('name', 'like', "%سائق%")->get()->pluck('id');
            $essentials_employee_appointmets_ids = EssentialsEmployeeAppointmet::whereIn('specialization_id', $essentials_specializations_ids)->get()->pluck('employee_id');
            $workers = User::where('user_type', 'worker')
                ->whereIn('id', $essentials_employee_appointmets_ids)->get();
        }

        return view('housingmovements::movementMangment.cars.edit', compact('car', 'carModel', 'carModels', 'carTypes', 'workers'));
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
            $car = Car::find($id);
            $car->update([
                'plate_number' => $request->input('plate_number'),
                'color' => $request->input('color'),
                'user_id' => $request->input('user_id'),
                'car_model_id' => $request->input('car_model_id'),
                'number_seats' => $request->input('number_seats'),
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
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try {
            Car::find($id)->delete();
            return redirect()->back()->with(__('deleted_success'));
        } catch (Exception $e) {
            return redirect()->back();
        }
    }

    public function search(Request $request)
    {
        $carModels_ids = [];

        $query = Car::query();

        if ($request->has('search')) {
            $filters = $request->input("search");
            $filters_search_carModle = $request->input("search_carModle");
            $filters_search_plate_number = $request->input("search_plate_number");


            $query->whereHas('User', function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters}%")
                    ->orwhere('last_name', 'like', "%{$filters}%");
            });

            $query->where('plate_number', 'like', "%{$filters_search_plate_number}%");

            $query->whereHas('CarModel', function ($q) use ($filters_search_carModle) {
                $q->orwhere('name_ar', 'like', "%{$filters_search_carModle}%")
                    ->orwhere('name_en', 'like', "%{$filters_search_carModle}%");
            });

            if ($request->car_type_id && $request->car_type_id != 'all') {
                $carModels = CarType::find($request->car_type_id)->CarModel;
                $carModels_ids = $carModels->pluck('id');
                $query->whereIn('car_model_id', $carModels_ids);
            }
        }

        $Cars = $query->paginate(5);

        // return  $users_ids = User::where('user_type', 'worker')
        //     ->pluck('id');

        // if ($request->search == null) {
        //     $Cars = Car::whereIn('car_model_id', $carModels_ids)
        //         ->orWhereIn('user_id', $users_ids)->paginate(5);
        // } else {
        //     $Cars = Car::where('plate_number', 'like', "%{$request->search}%")
        //         ->orWhereIn('car_model_id', $carModels_ids)
        //         ->orWhereIn('user_id', $users_ids)->paginate(5);
        // }

        $carTypes = CarType::all();
        $after_serch = true;
        return view('housingmovements::movementMangment.cars.index', compact('carTypes', 'after_serch', 'Cars'));
    }
}