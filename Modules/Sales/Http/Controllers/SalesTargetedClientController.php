<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\Sales\Entities\salesTargetedClient;

class SalesTargetedClientController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
    
       $business_id = request()->session()->get('user.business_id');


        if (request()->ajax()) {
            $clients = DB::table('sales_targeted_clients')->select(['id','profession', 'specialization', 'nationality', 'gender'
            ,'number','Salary','food_allowance','housing_allowance','monthly_cost']);
                
            return datatables::of($clients)
            ->removeColumn('id')
            ->make(true);
        
        
            }
      return view('sales::price_offers.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
       
    }
    public function clientAdd()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('sales::targetedClient.client_add');
    }
    public function saveQuickClient(Request $request) {
       
        try {
            $business_id = $request->session()->get('user.business_id');
            $form_fields = ['profession', 'specialization', 'nationality', 'gender','monthly_cost', 'number', 'salary', 'food_allowance', 'housing_allowance'];

            $client_details = $request->only($form_fields);

            $client_details['business_id'] = $business_id;
            $client_details['created_by'] = $request->session()->get('user.id');
        
            DB::beginTransaction();

            $client = salesTargetedClient::create($client_details);
      
            DB::commit();

            $output = ['success' => 1,
                'msg' => __('sales::lang.client_added_success'),
                'client' => $client
               
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
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
