<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsJobTitle;

class EssentialsJobTitleController extends Controller
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
        
            $job_titles = DB::table('essentials_job_titles')
                ->select('job_title', 'job_code', 'responsibilities', 'supervision_scope', 'authorization_and_permissions',
                    'details', 'is_active');
    
          
            return Datatables::of($job_titles)
                ->addColumn('action', function ($row) use ($is_admin) {
                   
                        $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('job_title.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_job_title_button" data-href="' . route('job_title.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
    

        return view('essentials::settings.partials.job_titles.index');
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
       
        return view('essentials::settings.partials.job_titles.create');
        
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
            $input = $request->only(['job_title', 'job_code', 'responsibilities', 'supervision_scope', 'authorization_and_permissions', 'details', 'is_active']);
            

            $input2['job_title'] =  $input['job_title'];
            
            $input2['job_code'] = $input['job_code'];

            $input2['responsibilities'] = $input['responsibilities'];

            $input2['supervision_scope'] = $input['supervision_scope'];

            $input2['authorization_and_permissions'] = $input['authorization_and_permissions'];

            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsJobTitle::create($input2);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
      

        return view('essentials::settings.partials.job_titles.index');
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

        $job_title = EssentialsJobTitle::findOrFail($id);


        return view('essentials::settings.partials.job_titles.edit')->with(compact('job_title'));
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
            $input = $request->only(['job_title', 'job_code', 'responsibilities', 'supervision_scope', 'authorization_and_permissions', 'details', 'is_active']);
            

            $input2['job_title'] =  $input['job_title'];
            
            $input2['job_code'] = $input['job_code'];

            $input2['responsibilities'] = $input['responsibilities'];

            $input2['supervision_scope'] = $input['supervision_scope'];

            $input2['authorization_and_permissions'] = $input['authorization_and_permissions'];

            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsJobTitle::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return view('essentials::settings.partials.job_titles.index');
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
            EssentialsJobTitle::where('id', $id)
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
