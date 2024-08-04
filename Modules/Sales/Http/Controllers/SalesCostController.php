<?php

namespace Modules\Sales\Http\Controllers;



use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use  Modules\Sales\Entities\salesCost;

class SalesCostController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $crud_delete_sale_cost = auth()->user()->can('sales.delete_sale_cost');
        $crud_edit_sale_cost = auth()->user()->can('sales.edit_sale_cost');


        $costs = salesCost::all();

        if (request()->ajax()) {


            return Datatables::of($costs)

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $crud_delete_sale_cost, $crud_edit_sale_cost) {
                        $html = '';
                        if ($is_admin || $crud_edit_sale_cost) {
                            $html .= '<a href="#" class="btn btn-xs btn-primary edit-item"
                         data-id="' . $row->id . '" data-description-value="' . $row->description . '" data-amount-value="' . $row->amount . '" data-duration_by_month-value="' . $row->duration_by_month . '" data-monthly_cost-value="' . $row->monthly_cost  . '">
                         <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>&nbsp;';
                        }
                        if ($is_admin || $crud_delete_sale_cost) {
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button"
                            data-href="' . route('sales_costs_destroy', ['id' => $row->id]) . '">
                            <i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )


                ->rawColumns(['action'])
                ->make(true);
        }
        return view('sales::sales_costs.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['description', 'amount', 'duration_by_month', 'monthly_cost']);

            salesCost::create($input);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('sales_costs');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)

    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {



            $input = $request->only(['description2', 'cost_id', 'amount2', 'duration_by_month2', 'monthly_cost2']);
            $salesCost = salesCost::find($input['cost_id']);

            if (!$salesCost) {
                abort(404, 'Sales Source not found');
            }

            $salesCost->description = $input['description2'];
            $salesCost->amount = $input['amount2'];
            $salesCost->duration_by_month = $input['duration_by_month2'];
            $salesCost->monthly_cost = $input['monthly_cost2'];

            $salesCost->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('sales_costs')->with($output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            salesCost::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
