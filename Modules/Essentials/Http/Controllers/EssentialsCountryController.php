<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Http\Response;
use Modules\Essentials\Entities\EssentialsCountry;


class EssentialsCountryController extends Controller
{
    protected $moduleUtil;
   

     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
    public function index()
    {
    
       $business_id = request()->session()->get('user.business_id');


        $can_crud_countries = auth()->user()->can('essentials.crud_countries');
        if (! $can_crud_countries) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (request()->ajax()) {
            $countries = DB::table('essentials_countries')->select(['id','name', 'nationality', 'details', 'is_active'])
            ->orderby('id','desc');
                       

            return Datatables::of($countries)
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
                'action',
                function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_country_button" data-href="' . route('country.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
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
      return view('essentials::settings.partials.countries.index');
    }

    public function create()
    {   
       
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       
        return view('essentials::settings.partials.countries.create');
        
        
    }

   
    public function store(Request $request)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

    
 
        try {
            $input = $request->only(['arabic_name', 'english_name', 'nationality', 'details', 'is_active']);
            

            $input['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);
            
            $input['nationality'] = $input['nationality'];
           
            $input['details'] = $input['details'];
            
            $input['is_active'] = $input['is_active'];
            
            EssentialsCountry::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

       // return view('essentials::settings.partials.countries.index');
       return redirect()->route('countries');
    }
  
    public function show($id)
    {
        return view('essentials::show');
    }

  
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

   

        $country = EssentialsCountry::findOrFail($id);


        return view('essentials::settings.partials.countries.edit')->with(compact('country'));
    }

 
    public function update(Request $request, $id)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

   

        try {
            $input = $request->only(['arabic_name', 'english_name', 'nationality', 'details', 'is_active']);
       
            $input2['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);
            
            $input2['nationality'] = $input['nationality'];
           
            $input2['details'] = $input['details'];
            
            $input2['is_active'] = $input['is_active'];
            
            EssentialsCountry::where('id', $id)->update($input2);
            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }


        return redirect()->route('countries');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

     

        try {
            EssentialsCountry::where('id', $id)
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
