<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use  Modules\Sales\Entities\SalesSource;

class SaleSourcesController extends Controller
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
        $can_delete_sale_sources = auth()->user()->can('sales.delete_sale_sources');
        $can_edit_sale_sources = auth()->user()->can('sales.edit_sale_sources');

        $countries = SalesSource::select(['id', 'source as source'])
            ->orderby('id', 'desc');
        //dd( $countries->get() );

        if (request()->ajax()) {


            return Datatables::of($countries)

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_delete_sale_sources, $can_edit_sale_sources) {
                        $html = '';
                        if ($is_admin || $can_edit_sale_sources) {
                            $html .= '<a href="#" class="btn btn-xs btn-primary edit-item"
                         data-id="' . $row->id . '" data-orig-value="' . $row->source . '">
                         <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>&nbsp;';
                        }
                        if ($is_admin || $can_delete_sale_sources) {
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button"
                            data-href="' . route('sale_source_destroy', ['id' => $row->id]) . '">
                            <i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )


                ->rawColumns(['action'])
                ->make(true);
        }
        return view('sales::salesSources.index_sales_sources');
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
            $input = $request->only(['source']);



            $input['source'] = $input['source'];


            SalesSource::create($input);

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


        return redirect()->route('sales_sources');
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
            $input = $request->only(['source2', 'source_id']);

            $salesSource = salesSource::find($input['source_id']);

            if (!$salesSource) {
                abort(404, 'Sales Source not found');
            }

            // Update the source field
            $salesSource->source = $input['source2'];
            $salesSource->save();

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

        return redirect()->route('sales_sources')->with($output);
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
            SalesSource::where('id', $id)
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
