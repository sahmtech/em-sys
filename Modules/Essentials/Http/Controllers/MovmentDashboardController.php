<?php

namespace Modules\Essentials\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\CarModel;
use Modules\HousingMovements\Entities\HousingMovementsCarsChangeOil;
use Modules\HousingMovements\Entities\HousingMovementsMaintenance;
use Modules\HousingMovements\Entities\HousingMovmentInsurance;
use Yajra\DataTables\Facades\DataTables;

class MovmentDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_movement_management_dashbord =  auth()->user()->can('essentials.movement_management_dashbord');
        if (!($is_admin ||  $can_movement_management_dashbord)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $last_15_days = Carbon::now()->subDays(15);
        $to_15_days = Carbon::now()->addDays(15);

        $latestForm_count = Car::whereBetween('expiry_date',[Carbon::now(), $to_15_days])->count();
        $latestChangeOil_count = HousingMovementsCarsChangeOil::where('date', '>=', $last_15_days)->count();
        $latestMaintenance_count = HousingMovementsMaintenance::where('date', '>=', $last_15_days)->count();
        $latestInsurance_count = HousingMovmentInsurance::where('insurance_end_date', '>=', $last_15_days)->count();
        return view('essentials::dashboard.movment_dashboard', compact('latestMaintenance_count','latestInsurance_count', 'latestForm_count', 'latestChangeOil_count'));
    }


    public function latestChangeOil()
    {

        $last_15_days = Carbon::now()->subDays(15);
        $CarsChangeOil = HousingMovementsCarsChangeOil::where('date', '>=', $last_15_days)->get();


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

                    return  $row->next_change_oil ?? '';
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



                ->rawColumns(['action', 'car'])
                ->make(true);
        }
        return view('essentials::movementMangment.fillters.latestChangeOil');
    }
    public function latestForm()
    {

        $to_15_days = Carbon::now()->addDays(15);

        $Cars = Car::whereBetween('expiry_date',[Carbon::now(), $to_15_days])->get();

        $carTypes = CarModel::all();


        if (request()->ajax()) {

            if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {


                $Cars = $Cars->where('car_model_id', request()->input('carTypeSelect'));
            }



            return DataTables::of($Cars)


                ->editColumn('car_typeModel', function ($row) {
                    return $row->CarModel->CarType->name_ar . ' - ' . $row->CarModel->name_ar ?? '';
                })

                ->editColumn('plate_number', function ($row) {
                    return $row->plate_number ?? '';
                })
                ->editColumn('plate_registration_type', function ($row) {
                    return __('housingmovements::lang.' . $row->plate_registration_type) ?? '';
                })
                ->editColumn('serial_number', function ($row) {
                    return $row->serial_number ?? '';
                })
                ->editColumn('structure_no', function ($row) {
                    return $row->structure_no ?? '';
                })
                ->editColumn('manufacturing_year', function ($row) {
                    $manufacturingYear = $row->manufacturing_year ?? '';
                    $year = '';
                    if (!empty($manufacturingYear)) {
                        $carbonDate = \Carbon\Carbon::createFromFormat('Y-m-d', $manufacturingYear);
                        $year = $carbonDate->year;
                    }
                    return $year;
                })
                ->editColumn('vehicle_status', function ($row) {
                    return $row->vehicle_status ?? '';
                })
                ->editColumn('expiry_date', function ($row) {
                    return $row->expiry_date ?? '';
                })
                ->editColumn('test_end_date', function ($row) {
                    return $row->test_end_date ?? '';
                })
                ->editColumn('examination_status', function ($row) {
                    return __('housingmovements::lang.' . $row->examination_status) ?? '';
                })

                ->editColumn('number_seats', function ($row) {
                    return  $row->number_seats ?? '';
                })
                ->editColumn('color', function ($row) {
                    return $row->color ?? '';
                })
                ->editColumn('insurance_status', function ($row) {
                    return __('housingmovements::lang.' . $row->insurance_status) ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                            <a href="' . route('essentials.car.edit', ['id' => $row->id])  . '"
                            data-href="' . route('essentials.car.edit', ['id' => $row->id])  . ' "
                             class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_car_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                        ';
                        $html .= '
                        <button data-href="' .  route('essentials.car.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_car_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                    ';


                        return $html;
                    }
                )

                ->rawColumns(['action', 'car_typeModel', 'plate_number', 'number_seats', 'color'])
                ->make(true);
        }
        return view('essentials::movementMangment.fillters.latestForm', compact('carTypes', 'Cars'));
    }
    public function latestMaintenances()
    {
        $last_15_days = Carbon::now()->subDays(15);
        $carsMaintenance = HousingMovementsMaintenance::where('date', '>=', $last_15_days)->get();



        if (request()->ajax()) {

            // if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {


            //     $Cars = $Cars->where('car_model_id', request()->input('carTypeSelect'));
            // }


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

                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('essentials.cars-maintenances.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.cars-maintenances.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_carMaintenances_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        $html .= '
                    <button data-href="' .  route('essentials.cars-maintenances.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_carMaintenances_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';

                        if (!empty($row->attachment)) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->attachment . '\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                            '&nbsp;';
                        } else {
                            $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                        }
                        return $html;
                    }
                )



                ->rawColumns(['action', 'car'])
                ->make(true);
        }
        return view('essentials::movementMangment.fillters.latestMaintenances');
    }

    public function latestInsurance(){
        $last_15_days = Carbon::now()->subDays(15);
        $latestInsurance_ids = HousingMovmentInsurance::where('insurance_end_date', '>=', $last_15_days)->pluck('car_id');

        $Cars = Car::whereIn('id',$latestInsurance_ids)->get();
        $carTypes = CarModel::all();


        if (request()->ajax()) {

            if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {


                $Cars = $Cars->where('car_model_id', request()->input('carTypeSelect'));
            }



            return DataTables::of($Cars)


                ->editColumn('car_typeModel', function ($row) {
                    return $row->CarModel->CarType->name_ar . ' - ' . $row->CarModel->name_ar ?? '';
                })

                ->editColumn('plate_number', function ($row) {
                    return $row->plate_number ?? '';
                })
                ->editColumn('plate_registration_type', function ($row) {
                    return __('housingmovements::lang.' . $row->plate_registration_type) ?? '';
                })
                ->editColumn('serial_number', function ($row) {
                    return $row->serial_number ?? '';
                })
                ->editColumn('structure_no', function ($row) {
                    return $row->structure_no ?? '';
                })
                ->editColumn('manufacturing_year', function ($row) {
                    $manufacturingYear = $row->manufacturing_year ?? '';
                    $year = '';
                    if (!empty($manufacturingYear)) {
                        $carbonDate = \Carbon\Carbon::createFromFormat('Y-m-d', $manufacturingYear);
                        $year = $carbonDate->year;
                    }
                    return $year;
                })
                ->editColumn('vehicle_status', function ($row) {
                    return $row->vehicle_status ?? '';
                })
                ->editColumn('expiry_date', function ($row) {
                    return $row->expiry_date ?? '';
                })
                ->editColumn('test_end_date', function ($row) {
                    return $row->test_end_date ?? '';
                })
                ->editColumn('examination_status', function ($row) {
                    return __('housingmovements::lang.' . $row->examination_status) ?? '';
                })

                ->editColumn('number_seats', function ($row) {
                    return  $row->number_seats ?? '';
                })
                ->editColumn('color', function ($row) {
                    return $row->color ?? '';
                })
                ->editColumn('insurance_status', function ($row) {
                    return __('housingmovements::lang.' . $row->insurance_status) ?? '';
                })
                ->editColumn('insurance_company_id', function ($row) {
                    return $row->insurance->insurance_company_id ?? '';
                })
                
                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('essentials.car.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.car.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_car_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        $html .= '
                    <button data-href="' .  route('essentials.car.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_car_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';


                        return $html;
                    }
                )

              

                ->rawColumns(['action', 'car_typeModel','insurance_company_id', 'plate_number', 'number_seats', 'color'])
                ->make(true);
        }
        return view('essentials::movementMangment.fillters.latestInsurances', compact('carTypes', 'Cars'));
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