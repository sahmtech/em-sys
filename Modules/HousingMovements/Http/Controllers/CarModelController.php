<?php

namespace Modules\HousingMovements\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\CarModel;
use Modules\HousingMovements\Entities\CarType;

class CarModelController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $carModles = CarModel::paginate(5);
        $carTypes = CarType::all();


        $after_serch = false;
        return view('housingmovements::movementMangment.carModel.index', compact('carModles', 'after_serch', 'carTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $carTypes = CarType::all();

        return view('housingmovements::movementMangment.carModel.create', compact('carTypes'));
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

            CarModel::create([
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
                'car_type_id' => $request->input('car_type_id'),
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
        $carModel = CarModel::find($id);
        $carTypes = CarType::all();
        return view('housingmovements::movementMangment.carModel.edit', compact('carModel', 'carTypes'));
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

            $carModel = CarModel::find($id);
            $carModel->update([
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
                'car_type_id' => $request->input('car_type_id'),
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
            CarModel::find($id)->delete();
            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }

    public function search(Request $request)
    {
        if ($request->car_type_id == 'all' && $request->search != '') {
            $carModles = CarModel::Where('name_ar', 'like', "%{$request->search}%")
                ->orWhere('name_en', 'like', "%{$request->search}%")
                ->paginate(5);
        } elseif ($request->car_type_id != 'all' && $request->search == '') {
            $carModles = CarModel::Where('car_type_id', $request->car_type_id)
                ->paginate(5);
        } else {
            $carModles = CarModel::Where('car_type_id', $request->car_type_id)
                ->orWhere('name_ar', 'like', "%{$request->search}%")
                ->orWhere('name_en', 'like', "%{$request->search}%")->Where('car_type_id', $request->car_type_id)
                ->paginate(5);
        }
        $carTypes = CarType::all();
        $after_serch = true;
        return view('housingmovements::movementMangment.carModel.index', compact('carModles', 'after_serch', 'carTypes'));
    }
}