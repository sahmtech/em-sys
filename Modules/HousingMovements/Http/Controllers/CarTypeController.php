<?php

namespace Modules\HousingMovements\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\CarType;
use Yajra\DataTables\Facades\DataTables;

class CarTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $carTypes = CarType::all();
        $after_serch = false;
        if (request()->ajax()) {

            // if (!empty(request()->input('name')) && request()->input('name') !== 'all') {
            //     // $CarModel = CarType::find()->CarModel;

            //     $carTypes = $carTypes->where('car_type_id', request()->input('name'));
            //     // $Cars = $Cars->whereIn('car_model_id', $CarModel_ids);
            // }

            // if (!empty(request()->input('driver_select')) && request()->input('driver_select') !== 'all') {

            //     $carModles = $carModles->where('user_id', request()->input('driver_select'));
            // }

            return DataTables::of($carTypes)

                ->editColumn('name_ar', function ($row) {
                    return $row->name_ar  ?? '';
                })

                ->editColumn('name_en', function ($row) {
                    return $row->name_en ?? '';
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
                            href="' . route('cartype.edit', ['id' => $row->id]) . ' "
                            data-href="' . route('cartype.edit', ['id' => $row->id]) . ' "
                            data-container="#edit_car_type_model">

                            <i class="fas fa-edit cursor-pointer"
                                style="padding: 2px;color:rgb(8, 158, 16);"></i>
                            تعديل </a>

                        <a class="dropdown-item btn-modal" style="margin: 2px;" 
                            href=" ' . route('cartype.delete', ['id' => $row->id]) . ' "
                            data-href="' . route('cartype.delete', ['id' => $row->id]) . '"
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

                ->rawColumns(['action', 'name_en', 'name_ar'])
                ->make(true);
        }
        return view('housingmovements::movementMangment.carType.index', compact('carTypes', 'after_serch'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::movementMangment.carType.create');
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

            CarType::create([
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
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
        $carType = CarType::find($id);
        return view('housingmovements::movementMangment.carType.edit', compact('carType'));
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

            $carType = CarType::find($id);
            $carType->update([
                'name_ar' => $request->input('name_ar'),
                'name_en' => $request->input('name_en'),
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
            CarType::find($id)->delete();
            return redirect()->back()->with(__('deleted_success'));
        } catch (Exception $e) {
            return redirect()->back();
        }
    }

    public function search(Request $request)
    {
        $carTypes = CarType::Where('name_ar', 'like', "%{$request->search}%")
            ->orWhere('name_en', 'like', "%{$request->search}%")->paginate(5);
        $after_serch = true;
        return view('housingmovements::movementMangment.carType.index', compact('carTypes', 'after_serch'));
    }
}
