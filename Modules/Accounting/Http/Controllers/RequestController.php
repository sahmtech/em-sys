<?php

namespace Modules\Accounting\Http\Controllers;

use App\Utils\RequestUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

        $can_change_status = auth()->user()->can('accounting.change_status');
        $can_return_request = auth()->user()->can('accounting.return_the_request');
        $can_show_request = auth()->user()->can('accounting.show_request');


        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
        ->where(function ($query) {
            $query->where('name', 'like', '%حاسب%')
                ->orWhere('name', 'like', '%مالي%');
        })
        ->pluck('id')->toArray();
   
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_HR_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes=['employee','manager'];

        return $this->requestUtil->getRequests( $departmentIds, $ownerTypes, 'accounting::requests.allRequest' , $can_change_status, $can_return_request, $can_show_request);

    }

 
   
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
      
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
        ->where(function ($query) {
            $query->where('name', 'like', '%حاسب%')
                ->orWhere('name', 'like', '%مالي%');
        })
        ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
    }

    

   
}
