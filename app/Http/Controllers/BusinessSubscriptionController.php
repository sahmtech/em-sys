<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BusinessDocument;
use App\BusinessSubscription;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
class BusinessSubscriptionController extends Controller
{
    protected $moduleUtil;
   

     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
    public function show($business_id){
    
       
        if (! auth()->user()->can('business_documents.view') ) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $auth_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $auth_id);
        $can_delete_business_subscription= auth()->user()->can('essentials.delete_business_subscription');
       if (request()->ajax()) {  
            $business = BusinessSubscription::where('business_id', $business_id)
            ->select(['id','business_id','subscription_type','subscription_number','subscription_date','renew_date','expiration_date']);
       
            return Datatables::of($business)
            ->addColumn(
                'action',
                function ($row) use ($is_admin , $can_delete_business_subscription) {
                    $html = '';
                    if ($is_admin ||  $can_delete_business_subscription) {
                      
                        $html .= '<button class="btn btn-xs btn-danger delete_subscription_button" data-href="' . route('busSubscription.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    }
        
                    return $html;
                }
            )
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->removeColumn('id')
            ->rawColumns(['action'])
            ->make(true);
         
        }
     

    
        return view('essentials::bussines_manage.subscriptions')->with(compact('business_id'));



    }

    public function store(Request $request)
    {

        $businessDocument = new BusinessSubscription;
        $businessDocument->subscription_type = $request->subscription_type;
        $businessDocument->subscription_number = $request->subscription_number;
        $businessDocument->business_id = $request->business_id;
        $businessDocument->subscription_date = $request->subscription_date;
        $businessDocument->renew_date = $request->renew_date;
        $businessDocument->expiration_date = $request->expiration_date;
    

        $businessDocument->save();

        return redirect()->route('business_subscriptions.view', ['id' => $request->business_id])->with('success', 'Business subscription added successfully');

    }
    public function destroy($id)
    {
        if (! auth()->user()->can('business_documents.destroy') ) {
           //temp  abort(403, 'Unauthorized action.');
        }

        try {
            BusinessSubscription::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
    
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    
    return $output;
    }
}
