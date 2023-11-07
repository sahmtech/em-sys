<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;


class EssentialsProfessionController extends Controller
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
            $professions = EssentialsProfession::with('specializations')
            ->orderby('id','desc');
                       

            return Datatables::of($professions)
             ->addColumn('specializations', function ($row) {
            $specializations = $row->specializations->map(function ($spec) {
                return $spec->name . ' (' . $spec->en_name . ')';
            });
            return $specializations->implode(', ');
        })
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        // $html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        // &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_profession_button" data-href="' . route('profession.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    }
        
                    return $html;
                }
            )
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
           
            ->rawColumns(['action'])
            ->make(true);
        
        
            }
            return view('essentials::settings.partials.professions_and_specializations.index');

    }
    

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        ;
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|string',
        'en_name' => 'nullable|string',
        'specializations' => 'required|array',
        'specializations.*' => 'string',
        'en_specializations' => 'array',
        'en_specializations.*' => 'string|nullable', 
            ]);

        $specializations = $request->input('specializations');
        $en_specializations = $request->input('en_specializations');
        $profession = EssentialsProfession::create([
            'name' => $request->input('name'),
            'en_name' => $request->input('en_name'),
        ]);
  
        foreach ($specializations as $index => $specName) {
            $specEnName = isset($en_specializations[$index]) ? $en_specializations[$index] : null;
            
            EssentialsSpecialization::create([
                'name' => $specName,
                'en_name' => $specEnName,
                'profession_id' => $profession->id,
            ]);
        }

    
      return redirect()->route('professions');
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
        return view('essentials::edit');
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $profession = EssentialsProfession::find($id);

            if (!$profession) {
                return response()->json(['message' => 'Profession not found'], 404);
            }
    
            $profession->specializations()->delete();

            $profession->delete();

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
