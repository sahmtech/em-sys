<?php

namespace Modules\Essentials\Http\Controllers;

use App\Contact;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\CarImage;
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

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_index_cars = auth()->user()->can('essentials.cars');
        if (!($is_admin || $can_index_cars)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $Cars = Car::all();
        $carTypes = CarModel::all();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (request()->ajax()) {

            if (!empty(request()->input('carTypeSelect')) && request()->input('carTypeSelect') !== 'all') {

                $Cars = $Cars->where('car_model_id', request()->input('carTypeSelect'));
            }

            $can_edit_car = auth()->user()->can('car.edit');
            $can_delete_car = auth()->user()->can('car.delete');
            $can_insurance_car = auth()->user()->can('car.insurance');

            return DataTables::of($Cars)

                ->editColumn('car_typeModel', function ($row) {
                    return $row->CarModel->CarType?->name_ar ?? '' . ' - ' . $row->CarModel?->name_ar ?? '';
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
                    return $row->examination_status ? __('housingmovements::lang.' . $row->examination_status) : '';
                })

                ->editColumn('number_seats', function ($row) {
                    return $row->number_seats ?? '';
                })
                ->editColumn('color', function ($row) {
                    return $row->color ?? '';
                })
                ->editColumn('insurance_status', function ($row) {
                    return __('housingmovements::lang.' . $row->insurance_status) ?? '';
                })
                ->editColumn('insurance_company_id', function ($row) {
                    return $row->insurance->contact->supplier_business_name ?? '';
                })

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_car, $can_delete_car, $can_insurance_car) {

                        $html = '';
                        if ($is_admin || $can_edit_car) {
                            $html .= '
                            <a href="' . route('essentials.car.edit', ['id' => $row->id]) . '"
                            data-href="' . route('essentials.car.edit', ['id' => $row->id]) . ' "
                             class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_car_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                             <a href="' . route('essentials.car.show', ['id' => $row->id]) . '"
                             data-href="' . route('essentials.car.show', ['id' => $row->id]) . ' "
                              class="btn btn-xs btn-modal btn-info show_car_button"  data-container="#show_car_model">' . __("housingmovements::lang.show") . '</a>
                         ';
                        }
                        if ($is_admin || $can_delete_car) {
                            $html .= '
                            <button data-href="' . route('essentials.car.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_car_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        }
                        if ($is_admin || $can_insurance_car) {
                            $html .= '
                <a href="' . route('essentials.car-insurance', ['id' => $row->id]) . '" style="margin-top: 2px;" class="btn btn-xs btn-info">' . __("essentials::lang.insurance") . '</a>
            ';
                        }

                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'car_typeModel', 'insurance_company_id', 'plate_number', 'number_seats', 'color'])
                ->make(true);
        }
        return view('essentials::movementMangment.cars.index', compact('carTypes', 'Cars'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {

        $carTypes = CarType::all();
        // $insurance_companies = Contact::where('type', 'insurance')->get();
        return view('essentials::movementMangment.cars.create', compact('carTypes'));
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
            $car = Car::create([
                'plate_number' => $request->input('plate_number'),
                'color' => $request->input('color'),
                // 'user_id' => $request->input('user_id'),
                'car_model_id' => $request->input('car_model_id'),
                'number_seats' => $request->input('number_seats'),
                'plate_registration_type' => $request->input('plate_registration_type'),
                'manufacturing_year' => Carbon::createFromDate($request->input('manufacturing_year'), 1, 1),
                'serial_number' => $request->input('serial_number'),
                'structure_no' => $request->input('structure_no'),
                'vehicle_status' => $request->input('vehicle_status'),
                'expiry_date' => $request->input('expiry_date'),
                'test_end_date' => $request->input('test_end_date'),
                'examination_status' => $request->input('examination_status'),
                // 'insurance_status' => $request->input('insurance_status'),

            ]);

            if ($request->hasFile('car_image')) {
                foreach ($request->file('car_image') as $image) {
                    $destinationPath = 'car_images/';
                    $filename = $image->getClientOriginalName();
                    $image->move($destinationPath, $filename);
                    CarImage::create([
                        'car_image' => $filename,
                        'car_id' => $car->id,
                    ]);
                }
            }

            // HousingMovmentInsurance::create([
            //     'car_id' => $car->id,
            //     'insurance_company_id' => $request->input('insurance_company_id'),
            //     'insurance_start_Date' => $request->input('insurance_start_Date'),
            //     'insurance_end_date' => $request->input('insurance_end_date'),
            // ]);

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
        }
        // $insurance_companies = Contact::where('type', 'insurance')->get();
        return view('essentials::movementMangment.cars.edit', compact('car', 'carModel', 'carModels', 'carTypes'));
    }

    public function show($id)
    {
        $car = Car::find($id);
        if ($car) {
            $carModel = CarModel::find($car->car_model_id);
            $carModels = CarModel::where('car_type_id', $carModel->car_type_id)->get();
            $carTypes = CarType::all();
            $carImage = CarImage::where('car_id', $id)->get();
        }
        // $insurance_companies = Contact::where('type', 'insurance')->get();
        return view('essentials::movementMangment.cars.show', compact('car', 'carModel', 'carModels', 'carTypes', 'carImage'));
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
                // 'user_id' => $request->input('user_id'),
                'car_model_id' => $request->input('car_model_id'),
                'number_seats' => $request->input('number_seats'),
                'plate_registration_type' => $request->input('plate_registration_type'),
                'manufacturing_year' => Carbon::createFromDate($request->input('manufacturing_year'), 1, 1),
                'serial_number' => $request->input('serial_number'),
                'structure_no' => $request->input('structure_no'),
                'vehicle_status' => $request->input('vehicle_status'),
                'expiry_date' => $request->input('expiry_date'),
                'test_end_date' => $request->input('test_end_date'),
                'examination_status' => $request->input('examination_status'),
                'insurance_status' => $request->input('insurance_status'),
            ]);

            if ($car->images) {
                $car->images()->delete();
                if ($request->hasFile('car_image')) {
                    foreach ($request->file('car_image') as $image) {
                        $destinationPath = 'car_images/';
                        $filename = $image->getClientOriginalName();
                        $image->move($destinationPath, $filename);
                        CarImage::create([
                            'car_image' => $filename,
                            'car_id' => $car->id,
                        ]);
                    }
                }
            } else {
                if ($request->hasFile('car_image')) {
                    foreach ($request->file('car_image') as $image) {
                        $destinationPath = 'car_images/';
                        $filename = $image->getClientOriginalName();
                        $image->move($destinationPath, $filename);
                        CarImage::create([
                            'car_image' => $filename,
                            'car_id' => $car->id,
                        ]);
                    }
                }
            }

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
                Car::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف السيارة بنجاح',
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
