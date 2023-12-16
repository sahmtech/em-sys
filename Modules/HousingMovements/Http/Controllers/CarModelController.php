<?php

namespace Modules\HousingMovements\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\CarModel;
use Modules\HousingMovements\Entities\CarType;
use Yajra\DataTables\Facades\DataTables;

class CarModelController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $carModles = CarModel::all();
        $carTypes = CarType::all();
        if (request()->ajax()) {

            if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {
                // $CarModel = CarType::find()->CarModel;

                $carModles = $carModles->where('car_type_id', request()->input('carTypeSelect'));
                // $Cars = $Cars->whereIn('car_model_id', $CarModel_ids);
            }

            // if (!empty(request()->input('driver_select')) && request()->input('driver_select') !== 'all') {

            //     $carModles = $carModles->where('user_id', request()->input('driver_select'));
            // }

            return DataTables::of($carModles)

                ->editColumn('name_ar', function ($row) {
                    return $row->name_ar  ?? '';
                })

                ->editColumn('name_en', function ($row) {
                    return $row->name_en ?? '';
                })

                ->editColumn('carType', function ($row) {
                    return$row->CarType->name_ar . ' - ' . $row->CarType->name_en  ?? '';
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
                            href="' . route('carmodel.edit', ['id' => $row->id]) . ' "
                            data-href="' . route('carmodel.edit', ['id' => $row->id]) . ' "
                            data-container="#edit_carModels_model">

                            <i class="fas fa-edit cursor-pointer"
                                style="padding: 2px;color:rgb(8, 158, 16);"></i>
                            تعديل </a>

                        <a class="dropdown-item btn-modal" style="margin: 2px;" 
                            href=" ' . route('carmodel.delete', ['id' => $row->id]) . ' "
                            data-href="' . route('carmodel.delete', ['id' => $row->id]) . '"
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

                ->rawColumns(['action', 'name_en', 'name_ar',  'carType'])
                ->make(true);
        }

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
            return redirect()->back()->with(__('deleted_success'));

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