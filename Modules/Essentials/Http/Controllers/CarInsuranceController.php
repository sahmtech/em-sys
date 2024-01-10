<?php

namespace Modules\Essentials\Http\Controllers;

use App\Contact;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HousingMovements\Entities\HousingMovmentInsurance;
use Yajra\DataTables\Facades\DataTables;

class CarInsuranceController extends Controller
{
    public function index(Request $request)
    {





        if (request()->ajax()) {
            $insurance = HousingMovmentInsurance::where('car_id', request()->input('id'));


            return DataTables::of($insurance)


                ->editColumn('insurance_company_id', function ($row) {
                    return $row->contact->supplier_business_name ?? '';
                })

                ->editColumn('insurance_start_Date', function ($row) {
                    return $row->insurance_start_Date ?? '';
                })
                ->editColumn('insurance_end_date', function ($row) {
                    return $row->insurance_end_date ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('essentials.car.insurance.edit', ['id' => $row->id])  . '"
                        data-href="' . route('essentials.car.insurance.edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button"  data-container="#edit_insurance_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        $html .= '
                    <button data-href="' .  route('essentials.carinsurance.delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_car_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';


                        return $html;
                    }
                )



                ->rawColumns(['action', 'insurance_company_id', 'insurance_start_Date', 'insurance_end_date'])
                ->make(true);
        }
        $car_id=request()->input('id');
        return view('essentials::movementMangment.carsInsurance.index',compact('car_id'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($id)
    {
        $insurance_companies = Contact::where('type', 'insurance')->get();
        return view('essentials::movementMangment.carsInsurance.create', compact('insurance_companies','id'));
    }

    // 	

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();


            HousingMovmentInsurance::create([
                'car_id' => $request->input('car_id'),
                'insurance_company_id' => $request->input('insurance_company_id'),
                'insurance_start_Date' => $request->input('insurance_start_Date'),
                'insurance_end_date' => $request->input('insurance_end_date'),
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

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $insurance = HousingMovmentInsurance::find($id);

        $insurance_companies = Contact::where('type', 'insurance')->get();
        return view('essentials::movementMangment.carsInsurance.edit', compact('insurance', 'insurance_companies'));
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
            $insurance = HousingMovmentInsurance::find($id);


            $insurance->update([
                'insurance_company_id' => $request->input('insurance_company_id'),
                'insurance_start_Date' => $request->input('insurance_start_Date'),
                'insurance_end_date' => $request->input('insurance_end_date'),
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
                HousingMovmentInsurance::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم الحذف  بنجاح',
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