<?php

namespace Modules\HousingMovements\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\CarType;

class CarTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $carTypes = CarType::paginate(5);
        $after_serch = false;
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
            return redirect()->back();
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
