<?php

namespace Modules\Essentials\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Essentials\Entities\EssentialsRegion;

class EssentialsRegionController extends Controller
{
    protected $moduleUtil;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');


        $can_crud_regoins = auth()->user()->can('essentials.crud_regions');
        if (! $can_crud_regoins) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

        if (request()->ajax()) {
            
            $regions = DB::table('essentials_regions')
            ->select('essentials_regions.id as id', 'essentials_regions.name', 'essentials_regions.city_id', 'essentials_regions.details', 'essentials_regions.is_active', 'essentials_regions.name as city_name')
            ->leftJoin('essentials_cities', 'essentials_regions.city_id', '=', 'essentials_cities.id');
             
            return Datatables::of($regions)
            ->addColumn(
                'nameAr',
                function ($row) {
                    $name = json_decode($row->name, true);
                    return $name['ar'] ?? '';
                }
            )
            ->addColumn(
                'nameEn',
                function ($row) {
                    $name = json_decode($row->name, true);
                    return $name['en'] ?? '';
                }
            )
            ->addColumn(
                'city_nameAr',
                function ($row) {
                    $name = json_decode($row->city_name, true);
                    return $name['ar'] ?? '';
                }
            )
            ->addColumn(
                'city_nameEn',
                function ($row) {
                    $name = json_decode($row->city_name, true);
                    return $name['en'] ?? '';
                }
            )
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('region.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_region_button" data-href="' . route('region.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
        $cities = EssentialsCity::forDropdown();
     
        return view('essentials::settings.partials.regions.index')->with(compact('cities'));
    }

    public function store(Request $request)
    {
     
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

 
 
        try {
            $input = $request->only(['arabic_name', 'english_name', 'city', 'details', 'is_active']);
            

            $input['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);
            
            $input['city_id'] = $input['city'];
           
            $input['details'] = $input['details'];
            
            $input['is_active'] = $input['is_active'];
            
            EssentialsRegion::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
      
        return redirect()->route('regions');
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

     

        $region = EssentialsRegion::findOrFail($id);
        $city2=EssentialsCity::whereId($region->city_id)->first();
        
        $cities = EssentialsCity::forDropdown();


        return view('essentials::settings.partials.regions.edit')->with(compact('region','cities','city2'));
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

     

        try {
            $input = $request->only(['arabic_name', 'english_name', 'city', 'details', 'is_active']);
            

            $input2['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);
            
            $input2['city_id'] = $input['city'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
           
            
            EssentialsRegion::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

      
        return redirect()->route('regions');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

    

        try {
            EssentialsRegion::where('id', $id)
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
