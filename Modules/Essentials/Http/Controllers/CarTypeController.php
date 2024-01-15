<?php

namespace Modules\Essentials\Http\Controllers;

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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (request()->ajax()) {



            return DataTables::of($carTypes)

                ->editColumn('name_ar', function ($row) {
                    return $row->name_ar  ?? '';
                })

                ->editColumn('name_en', function ($row) {
                    return $row->name_en ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {

                        $html = '';
                        if ($is_admin  || auth()->user()->can('cartype.edit')) {
                        $html .= '
                        <a href="' . route('essentials.cartype.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.cartype.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_carType_button"  data-container="#edit_car_type_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        }
                        if ($is_admin  || auth()->user()->can('cartype.delete')) {
                        $html .= '
                    <button data-href="' .  route('essentials.cartype.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_carType_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
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

                ->rawColumns(['action', 'name_en', 'name_ar'])
                ->make(true);
        }
        return view('essentials::movementMangment.carType.index', compact('carTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::movementMangment.carType.create');
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
        $carType = CarType::find($id);
        return view('essentials::movementMangment.carType.edit', compact('carType'));
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
                CarType::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف نوع السيارة بنجاح',
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

    public function search(Request $request)
    {
        $carTypes = CarType::Where('name_ar', 'like', "%{$request->search}%")
            ->orWhere('name_en', 'like', "%{$request->search}%")->paginate(5);
        $after_serch = true;
        return view('essentials::movementMangment.carType.index', compact('carTypes', 'after_serch'));
    }
}