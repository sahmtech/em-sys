<?php

namespace Modules\Essentials\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\HousingMovementsMaintenance;
use Yajra\DataTables\Facades\DataTables;

class CarsMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

        
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_carMaintenances = auth()->user()->can('essentials.carMaintenances');
        if (!($is_admin || $can_carMaintenances)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $carsMaintenance = HousingMovementsMaintenance::all();
        $can_maintenances_edit = auth()->user()->can('maintenances.edit');
        $can_maintenances_delete = auth()->user()->can('maintenances.delete');
        if (request()->ajax()) {
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
                    function ($row) use ($is_admin, $can_maintenances_edit, $can_maintenances_delete) {

                        $html = '';
                        if ($is_admin  || $can_maintenances_edit) {
                            $html .= '
                        <a href="' . route('essentials.cars-maintenances.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.cars-maintenances.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_carMaintenances_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        }
                        if ($is_admin  || $can_maintenances_delete) {
                            $html .= '
                    <button data-href="' .  route('essentials.cars-maintenances.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_carMaintenances_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        }
                        if (!empty($row->attachment)) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->attachment . '\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                            '&nbsp;';
                        } else {
                            $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                        }
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
        return view('essentials::movementMangment.maintenances.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $cars = Car::all();
        return view('essentials::movementMangment.maintenances.create', compact('cars'));
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
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $attachment_name = $attachment->store('/carsMaintenance');
            }
            HousingMovementsMaintenance::create([
                'car_id' => $request->input('car_id'),
                'current_speedometer' => $request->input('current_speedometer'),
                'maintenance_type' => $request->input('maintenance_type'),
                'maintenance_description' => $request->input('maintenance_description'),
                'invoice_no' => $request->input('invoice_no'),
                'date' => $request->input('date'),
                'attachment' => $attachment_name,

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
        $carMaintenances = HousingMovementsMaintenance::find($id);
        return view('essentials::movementMangment.maintenances.edit', compact('carMaintenances', 'cars'));
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
            $update_data = [];
            if ($request->hasFile('attachment')) {
                $image = $request->file('attachment');
                $update_data['attachment'] = $image->store('/carsMaintenance');
            }

            $update_data['car_id'] = $request->input('car_id');
            $update_data['current_speedometer'] = $request->input('current_speedometer');
            $update_data['maintenance_type'] = $request->input('maintenance_type');
            $update_data['maintenance_description'] =  $request->input('maintenance_description');
            $update_data['invoice_no'] =  $request->input('invoice_no');
            $update_data['date'] =  $request->input('date');
            $carMaintenances = HousingMovementsMaintenance::find($id);
            $carMaintenances->update($update_data);

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
                HousingMovementsMaintenance::find($id)->delete();
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