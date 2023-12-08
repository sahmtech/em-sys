<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\CarModel;
use Modules\HousingMovements\Entities\CarType;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $Cars = Car::paginate(5);
        $carTypes = CarType::all();
        $after_serch = false;
        return view('housingmovements::movementMangment.cars.index', compact('carTypes', 'after_serch', 'Cars'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $carTypes = CarType::all();
        $workers = User::where('user_type', 'worker')->get();
        return view('housingmovements::movementMangment.cars.create', compact('carTypes', 'workers'));
    }

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
            $workers = User::where('user_type', 'worker')->get();
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
            return redirect()->back();
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