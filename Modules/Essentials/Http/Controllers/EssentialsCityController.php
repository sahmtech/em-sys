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


class EssentialsCityController extends Controller
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

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_cities = auth()->user()->can('essentials.crud_cities');
        if (! $can_crud_cities) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (request()->ajax()) {
            
            $cities = DB::table('essentials_cities')
            ->select('essentials_cities.id as id', 'essentials_cities.name', 'essentials_cities.country_id', 'essentials_cities.details', 'essentials_cities.is_active', 'essentials_countries.name as country_name')
            ->leftJoin('essentials_countries', 'essentials_cities.country_id', '=', 'essentials_countries.id')
            ;
             
            return Datatables::of($cities)
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
                'country_nameAr',
                function ($row) {
                    $name = json_decode($row->country_name, true);
                    return $name['ar'] ?? '';
                }
            )
            ->addColumn(
                'country_nameEn',
                function ($row) {
                    $name = json_decode($row->country_name, true);
                    return $name['en'] ?? '';
                }
            )
            ->addColumn(
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('city.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_city_button" data-href="' . route('city.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
        $countries = EssentialsCountry::forDropdown();
     
        return view('essentials::settings.partials.cities.index')->with(compact('countries'));
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
        
        $countries = EssentialsCountry::forDropdown();
     
        return view('essentials::settings.partials.cities.create')->with(compact('countries'));
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
            $input = $request->only(['arabic_name', 'english_name', 'country', 'details', 'is_active']);
            

            $input['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);
            
            $input['country_id'] = $input['country'];
           
            $input['details'] = $input['details'];
            
            $input['is_active'] = $input['is_active'];
            
            EssentialsCity::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        $cities = DB::table('essentials_cities')
        ->select('essentials_cities.id as id', 'essentials_cities.name', 'essentials_cities.country_id', 'essentials_cities.details', 'essentials_cities.is_active', 'essentials_countries.name as country_name')
        ->leftJoin('essentials_countries', 'essentials_cities.country_id', '=', 'essentials_countries.id')
        ;


       
        $countries = EssentialsCountry::forDropdown();
        return redirect()->route('cities')->with(compact('countries'));
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

        $city = EssentialsCity::findOrFail($id);
        $country2=EssentialsCountry::whereId($city->country_id)->first();
        
        $countries = EssentialsCountry::forDropdown();


        return view('essentials::settings.partials.cities.edit')->with(compact('city','countries','country2'));
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
            $input = $request->only(['arabic_name', 'english_name', 'country', 'details', 'is_active']);
            

            $input2['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);
            
            $input2['country_id'] = $input['country'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
           
            
            EssentialsCity::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $cities = DB::table('essentials_cities')->select(['id','name', 'nationality', 'details', 'is_active']);

        return redirect()->route('cities');
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
            EssentialsCity::where('id', $id)
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
