<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsBankAccounts;

class EssentialsBankAccountController extends Controller
{
    protected $moduleUtil;
    
     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
     public function index()
     {
        $business_id = request()->session()->get('user.business_id');
 
         if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
             abort(403, 'Unauthorized action.');
         }
 
         $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
 
         if (request()->ajax()) {
        
            
             $banks = DB::table('essentials_bank_accounts')->select(['id','name', 'phone_number', 'mobile_number',
             'address','details', 'is_active']);
                        
 
             return Datatables::of($banks)
             ->addColumn(
                 'action',
                 function ($row) use ($is_admin) {
                     $html = '';
                     if ($is_admin) {
                         $html .= '<a href="'. route('bank_account.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                         &nbsp;';
                         $html .= '<button class="btn btn-xs btn-danger delete_bank_account_button" data-href="' . route('bank_account.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
       return view('essentials::settings.partials.bank_accounts.index');
     }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {   
       
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
                     abort(403, 'Unauthorized action.');}
       
        return view('essentials::settings.partials.bank_accounts.create');
        
        
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['name', 'phone_number', 'mobile_number',
            'address','details', 'is_active']);
            

            $input2['name'] = $input['name'];
            
            $input2['phone_number'] = $input['phone_number'];

            $input2['mobile_number'] = $input['mobile_number'];

            $input2['address'] = $input['address'];

           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsBankAccounts::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('bank_accounts');
       
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $bank = EssentialsBankAccounts::findOrFail($id);


        return view('essentials::settings.partials.bank_accounts.edit')->with(compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'phone_number', 'mobile_number',
            'address','details', 'is_active']);
            

            $input2['name'] = $input['name'];
            
            $input2['phone_number'] = $input['phone_number'];

            $input2['mobile_number'] = $input['mobile_number'];

            $input2['address'] = $input['address'];

           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            
            EssentialsBankAccounts::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('bank_accounts');
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsBankAccounts::where('id', $id)
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