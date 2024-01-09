<?php

namespace Modules\Essentials\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\HousingMovementsCarsChangeOil;
use Modules\HousingMovements\Entities\HousingMovementsMaintenance;
use Yajra\DataTables\Facades\DataTables;

class CarsReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function carMaintenances(Request $request)
    {
        $carsMaintenance = HousingMovementsMaintenance::all();

        if (request()->ajax()) {

            if (!empty(request()->input('carSelect')) && request()->input('carSelect') !== 'all') {


                $carsMaintenance = $carsMaintenance->where('car_id', request()->input('carSelect'));
            }


            return DataTables::of($carsMaintenance)


                ->editColumn('car', function ($row) {
                    return $row->car->CarModel->CarType->name_ar . ' - ' . $row->car->CarModel->name_ar ?? '';
                })

                ->editColumn('current_speedometer', function ($row) {
                    return $row->current_speedometer ?? '';
                })
                ->editColumn('maintenance_type', function ($row) {
                    return $row->maintenance_type ?? '';
                })
                ->editColumn('maintenance_description', function ($row) {
                    return $row->maintenance_description ?? '';
                })
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ?? '';
                })
                ->editColumn('date', function ($row) {
                    return  Carbon::parse($row->date)->format('Y-m-d') ?? '';
                })

             
                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'car'])
                ->make(true);
        }
      $cars= Car::all();

        return view('essentials::movementMangment.reports.carMaintenances',compact('cars'));
    }

    public function CarsChangeOil(Request $request)
    {
        $CarsChangeOil = HousingMovementsCarsChangeOil::all();



        if (request()->ajax()) {

            if (!empty(request()->input('carSelect')) && request()->input('carSelect') !== 'all') {


                $CarsChangeOil = $CarsChangeOil->where('car_id', request()->input('carSelect'));
            }


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
               
               

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'car'])
                ->make(true);
        }
      $cars= Car::all();
        return view('essentials::movementMangment.reports.carsChangeOil',compact('cars'));
    }


    
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::create');
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
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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