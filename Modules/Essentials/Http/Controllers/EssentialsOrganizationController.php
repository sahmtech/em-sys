<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsOrganization;

class EssentialsOrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
  
        $business_id = request()->session()->get('user.business_id');
    
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
   
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
       
        if (request()->ajax()) {
        
            $organizations = DB::table('essentials_organizations')
                ->select(['essentials_organizations.id', 'essentials_organizations.name', 'essentials_organizations.code', 'essentials_organizations.level_type', 'essentials_organizations.parent_level',
                    'essentials_organizations.account_number', 'essentials_organizations.details', 'essentials_organizations.is_active', 'essentials_bank_accounts.name as bank_name'])
                ->leftJoin('essentials_bank_accounts', 'essentials_organizations.bank_id', '=', 'essentials_bank_accounts.id');
    
          
            return Datatables::of($organizations)
                ->addColumn('action', function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                      
                        $html .= '<a href="'. route('organization.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_organization_button" data-href="' . route('organization.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    }
    
                    return $html;
                })
                ->filterColumn('name', function ($query, $keyword) {
                    
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
    
        $banks = EssentialsBankAccounts::forDropdown();
        
        return view('essentials::settings.partials.organizations.index')->with(compact('banks'));
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
       
        
        $banks = EssentialsBankAccounts::forDropdown();
        return view('essentials::settings.partials.organizations.create')->with(compact('banks'));
    
        
        
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
            $input = $request->only(['name', 'code', 'level_type', 'parent_level', 'account_number', 'details', 'is_active','bank']);
            

            $input2['name'] = $input['name'];
            
            $input2['code'] = $input['code'];

            $input2['level_type'] = $input['level_type'];

            $input2['parent_level'] = $input['parent_level'];

            $input2['account_number'] = $input['account_number'];

            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];

            $input2['bank_id'] = $input['bank'];

            
            EssentialsOrganization::create($input2);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        $banks = EssentialsBankAccounts::forDropdown();
        return redirect()->route('organizations')->with(compact('banks'));
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

        $organization = EssentialsOrganization::findOrFail($id);
        $bank=EssentialsBankAccounts::whereId($organization->bank_id)->first();

       $banks = EssentialsBankAccounts::forDropdown();

        return view('essentials::settings.partials.organizations.edit')->with(compact('organization','banks','bank'));
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
            $input = $request->only(['name', 'code', 'level_type', 'parent_level', 'account_number', 'details', 'is_active','bank']);
            

            $input2['name'] = $input['name'];
            
            $input2['code'] = $input['code'];

            $input2['level_type'] = $input['level_type'];

            $input2['parent_level'] = $input['parent_level'];

            $input2['account_number'] = $input['account_number'];

            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];

            $input2['bank_id'] = $input['bank'];

            
            
            EssentialsOrganization::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

     

        return redirect()->route('organizations');
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
            EssentialsOrganization::where('id', $id)
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
