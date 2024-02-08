<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\RequestUtil;
use Modules\Essentials\Entities\EssentialsDepartment;


class RequestController extends Controller
{
    protected $requestUtil;


    public function __construct(RequestUtil $requestUtil)
    {
        $this->requestUtil = $requestUtil;
    }

    public function index()
    {

        $business_id = request()->session()->get('user.business_id');
     

        $can_change_status = auth()->user()->can('sales.change_request_status');
        $can_return_request = auth()->user()->can('sales.return_sale_request');
        $can_show_request = auth()->user()->can('sales.show_sale_request');

        
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%مبيعات%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_sales_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

       
        $ownerTypes=['worker'];

        return $this->requestUtil->getRequests( $departmentIds, $ownerTypes, 'sales::requests.allRequest' , $can_change_status ,$can_return_request, $can_show_request);
    
      
    }


 
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
      
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%مبيعات%')
        ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
        
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */


 
}
