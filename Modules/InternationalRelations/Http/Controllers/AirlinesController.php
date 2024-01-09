<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\User;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Events\ContactCreatedOrModified;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsCity;
use DB;

class AirlinesController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;


    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function getCitiesByCountry($country_id) {
        $cities = EssentialsCity::where('country_id', $country_id)->get();
        $decodedCities = $cities->map(function ($city) {
            $city->name = json_decode($city->name, true);
            return $city;
        });
    
        return response()->json($decodedCities);
       
    }
    

    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $can_crud_airlines= auth()->user()->can('internationalrelations.crud_airlines');
        if (! $can_crud_airlines) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
     
   
        $countries = EssentialsCountry::forDropdown();

        $contacts = DB::table('contacts')
        ->leftJoin('essentials_countries', 'contacts.country', '=', 'essentials_countries.id')
        ->leftJoin('essentials_cities', 'contacts.city', '=', 'essentials_cities.id')
        ->select([
            'contacts.id',
            'contacts.supplier_business_name',
            'essentials_countries.name as country',
            'essentials_cities.name as city',
            'contacts.name',
            'contacts.mobile',
            'contacts.email',
            'contacts.evaluation',
            'contacts.landline'
    
        ])->where('business_id',$business_id)
        ->where('type','travel_agency');
    
    
 
if (request()->ajax()) {
 
         
        return Datatables::of($contacts)
          
            ->addColumn(
                'country_nameAr',
                function ($row) {
                    $name = json_decode($row->country, true);
                    return $name['ar'] ?? '';
                }
            )
            ->addColumn(
                'city_nameAr',
                function ($row) {
                    $name = json_decode($row->city, true);
                    return $name['ar'] ?? '';
                }
            )
           
            ->addColumn('action', function ($row) {
                
                    $html = '<a href="" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                    $html .= '&nbsp;<button class="btn btn-xs btn-danger delete_country_button" data-href=""><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    //$html .= '&nbsp;<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>'; // New view button
                    return $html;
                })
              

               
                ->rawColumns(['action'])
                ->make(true);
        }
     
        return view('internationalrelations::airlines.index')->with(compact('countries'));
           
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('internationalrelations::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $can_crud_airlines= auth()->user()->can('internationalrelations.crud_airlines');
        if (! $can_crud_airlines) {
           //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');

            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([
            'supplier_business_name',
            'country',
            'city',
            'name',
            'mobile',
            'email',
            'evaluation',
            'landline']);

         
            $input['type']='travel_agency';
            $input['supplier_business_name']=$request->input('Office_name');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');
           // dd($input);
        
            DB::beginTransaction();
            $output = $this->contactUtil->createNewContact($input);
            $responseData = $output['data']; 

            event(new ContactCreatedOrModified($input, 'added'));

            $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $output['data'], 'input' => $request->input()]);

            $this->contactUtil->activityLog($output['data'], 'added');

            DB::commit();

       
        } 
        
        catch (\Exception $e)
         {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }
      catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();
        return response()->json(['success' => false, 'errors' => $errors], 422);
    }

    return redirect()->route('international-Relations.Airlines');
   // return $output;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('internationalrelations::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
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
        //
    }
}
