<?php

namespace Modules\Essentials\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\HousingMovementsCarsChangeOil;
use Yajra\DataTables\Facades\DataTables;

class CarsChangeOilController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $CarsChangeOil = HousingMovementsCarsChangeOil::all();



        if (request()->ajax()) {

            // if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {


            //     $Cars = $Cars->where('car_model_id', request()->input('carTypeSelect'));
            // }


            return DataTables::of($CarsChangeOil)


                ->editColumn('car', function ($row) {
                    return $row->car->CarModel->CarType->name_ar . ' - ' . $row->car->CarModel->name_ar ?? '';
                })

                ->editColumn('current_speedometer', function ($row) {
                    return $row->current_speedometer ?? '';
                })

                ->editColumn('next_change_oil', function ($row) {
                    
                    return  Carbon::parse($row->next_change_oil)->format('Y-m-d') ?? '';
                })
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ?? '';
                })
                ->editColumn('date', function ($row) {
                    return  Carbon::parse($row->date)->format('Y-m-d') ?? '';
                })
               
                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('essentials.cars-change-oil.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.cars-change-oil.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_carsChangeOil_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        $html .= '
                    <button data-href="' .  route('essentials.cars-change-oil.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_carsChangeOil_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';


                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'car'])
                ->make(true);
        }
        return view('essentials::movementMangment.carsChangeOil.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $cars = Car::all();
        return view('essentials::movementMangment.carsChangeOil.create', compact('cars'));
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

            HousingMovementsCarsChangeOil::create([
                'car_id' => $request->input('car_id'),
                'current_speedometer' => $request->input('current_speedometer'),

                'next_change_oil' => $request->input('next_change_oil'),
                'invoice_no' => $request->input('invoice_no'),
                'date' => $request->input('date'),
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
        $cars = Car::all();
        $carChangeOil = HousingMovementsCarsChangeOil::find($id);
        return view('essentials::movementMangment.carsChangeOil.edit', compact('carChangeOil', 'cars'));
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

           $CarsChangeOil= HousingMovementsCarsChangeOil::find($id);
           $CarsChangeOil->update([
                'car_id' => $request->input('car_id'),
                'current_speedometer' => $request->input('current_speedometer'),
                'next_change_oil' => $request->input('next_change_oil'),
                'invoice_no' => $request->input('invoice_no'),
                'date' => $request->input('date'),
            ]);


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
                HousingMovementsCarsChangeOil::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم الحذف بنجاح',
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