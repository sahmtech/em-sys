<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;
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
              
                $carModles = $carModles->where('car_type_id', request()->input('carTypeSelect'));
            
            }

          

            return DataTables::of($carModles)

                ->editColumn('name_ar', function ($row) {
                    return $row->name_ar  ?? '';
                })

                ->editColumn('name_en', function ($row) {
                    return $row->name_en ?? '';
                })

                ->editColumn('carType', function ($row) {
                    return $row->CarType->name_ar . ' - ' . $row->CarType->name_en  ?? '';
                })

                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('essentials.carmodel.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.carmodel.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_carModel_button"  data-container="#edit_carModels_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        $html .= '
                    <button data-href="' .  route('essentials.carmodel.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_carModel_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';


                        return $html;
                    }
                )
              
                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'name_en', 'name_ar',  'carType'])
                ->make(true);
        }

        $after_serch = false;
        return view('essentials::movementMangment.carModel.index', compact('carModles', 'after_serch', 'carTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $carTypes = CarType::all();

        return view('essentials::movementMangment.carModel.create', compact('carTypes'));
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
        return view('essentials::show');
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
        return view('essentials::movementMangment.carModel.edit', compact('carModel', 'carTypes'));
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

        if (request()->ajax()) {
            try {
                CarModel::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف طراز السيارة بنجاح',
                ];
            } catch (Exception $e) {
                return redirect()->back();
            }
            return $output;
        }
    }
}