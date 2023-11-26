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
use App\Transaction;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsCity;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\InternationalRelations\Entities\IrDelegation;
use Modules\InternationalRelations\Entities\IrProposedLabor;

class EmploymentCompaniesController extends Controller
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
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'internationalRelations_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_airlines = auth()->user()->can('internationalrelations.crud_airlines');
        if (!$can_crud_airlines) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);


        $countries = EssentialsCountry::forDropdown();
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = DB::table('contacts')
            ->leftJoin('essentials_countries', 'contacts.country', '=', 'essentials_countries.id')
            ->select([
                'contacts.id',
                'contacts.supplier_business_name',
                'essentials_countries.name as country',
                'essentials_countries.nationality as nationality',
                'contacts.name',
                'contacts.mobile',
                'contacts.email',
                'contacts.evaluation',
                'contacts.landline'

            ])->where('business_id', $business_id)
            ->where('type', 'recruitment');
        //  dd($contacts);

        if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {
            $contacts->where('essentials_countries.id', request()->input('nationality'));
        }
        if (!empty(request()->input('country')) && request()->input('country') !== 'all') {
            $contacts->where('essentials_countries.id', request()->input('country'));
        }
        if (request()->ajax()) {


            return Datatables::of($contacts)

                ->addColumn(
                    'country_nameAr',
                    function ($row) {
                        $name = json_decode($row->country, true);
                        return $name['ar'] ?? '';
                    }
                )


                ->addColumn('action', function ($row) {

                    $html = '<a href="" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                    $html .= '&nbsp;<button class="btn btn-xs btn-danger delete_country_button" data-href=""><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    $html .= '&nbsp;<a href="' . route('companyRequests', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('internationalrelations::lang.company_requests') . '</a>';
                    return $html;
                })



                ->rawColumns(['action'])
                ->make(true);
        }

        return view('internationalrelations::EmploymentCompanies.index')->with(compact('countries', 'nationalities'));
    }
    public function proposed_laborIndex(Request $request)
    { 
            $business_id = request()->session()->get('user.business_id');
    
          
            if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create')) {
                abort(403, 'Unauthorized action.');
            }
    
            $nationalities = EssentialsCountry::nationalityForDropdown();
            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');
            $business_id = request()->session()->get('user.business_id');
    
            $workers = IrProposedLabor::select([
                    'id',
                    DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
                    'age',
                    'gender',	
                    'email',
                    'profile_image',	
                    'dob',
                    'marital_status',
                    'blood_group',
                    'contact_number',
                    'permanent_address',
                    'current_address',
                    'profession_id',
                    'specialization_id',
                    'nationality_id']);
    
                       
            if (request()->ajax()) 
            {
             
                return Datatables::of($workers)
                   
                       
                        ->editColumn('profession_id',function($row)use($professions){
                            $item = $professions[$row->profession_id]??'';
            
                            return $item;
                        })
                        ->editColumn('specialization_id',function($row)use($specializations){
                            $item = $specializations[$row->specialization_id]??'';
            
                            return $item;
                        })
                      
                        ->editColumn('nationality_id',function($row)use($nationalities){
                            $item = $nationalities[$row->nationality_id]??'';
            
                            return $item;
                        })
                      
                        ->addColumn('action', function ($row) {
                
                            $html = '<a href="' . route('showEmployee', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';
                            
                            return $html;
                        })
                       
    
                    ->filterColumn('full_name', function ($query, $keyword) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                    })
    
                   
                    ->rawColumns(['action','profession','specialization'])
                    ->make(true);
    
            }
            
        
        return view('internationalrelations::EmploymentCompanies.proposed_laborIndex');
    }

    public function createProposed_labor()
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } 
        elseif (! $this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }

      
        $nationalities=EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $contacts=Contact::where('type','customer')->pluck('supplier_business_name','id');
        
        $blood_types = ['A+' => 'A positive (A+).',
        'A-' => 'A negative (A-).',
        'B+' => 'B positive (B+)',
        'B-' => 'B negative (B-).',
          'AB+'=>'AB positive (AB+).',
          'AB-'=>'AB negative (AB-).',
          'O+'=>'O positive (O+).',
          'O-'=>'O positive (O-).',];
     
       
       

         $resident_doc=null;
         $user = null;
        return view('internationalrelations::EmploymentCompanies.proposed_laborCreate')
                ->with(compact('nationalities','blood_types','contacts',"specializations",'professions',
                 'resident_doc','user'));
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function storeProposed_labor(Request $request)
    {
      
        try {
            $input = $request->only([
                'first_name','mid_name','last_name',
                'email','dob', 'gender',
                'marital_status','blood_group','age',
                'contact_number','alt_number', 'family_number','permanent_address',
                'current_address','profession', 'specialization',
                'nationality','profile_picture'

            ]);
            $input['first_name'] =  $request->input('first_name');
            $input['mid_name'] =  $request->input('mid_name');
            $input['last_name'] =  $request->input('last_name');
            $input['email'] =  $request->input('email');
            $input['dob'] =  $request->input('dob');
            $input['age'] =  $request->input('age');
            $input['gender'] = $request->input('gender');
            $input['contact_number'] = $request->input('contact_number');
            $input['marital_status'] = $request->input('marital_status');
            $input['alt_number'] = $request->input('alt_number');
            $input['family_number'] = $request->input('family_number');
            $input['permanent_address'] = $request->input('permanent_address');
            $input['current_address'] = $request->input('current_address');
            $input['blood_group'] = $request->input('blood_group');
            $input['profession_id'] = $request->input('profession');
            $input['specialization_id'] = $request->input('specialization');
            $input['nationality_id'] = $request->input('nationality');
            if ($request->hasFile('profile_picture')) {
                $file = request()->file('profile_picture');
                $filePath = $file->store('/proposedLaborPicture');
                
                $input['profile_image'] = $filePath;
            }


          
        $proposedLabor = IrProposedLabor::create($input);
        $output = [
            'success' => true,
            'msg' => __('lang_v1.added_success'),
        ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log(print_r('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage()));
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('proposed_laborIndex')->with('status', $output);
    }
    

    public function companyRequests($id)
    {
    
        $irDelegations = IrDelegation::where('agency_id',$id)->with(['transactionSellLine.service'])->get();

        
        return view('internationalrelations::EmploymentCompanies.requests')->with(compact('irDelegations'));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $can_crud_airlines = auth()->user()->can('internationalrelations.crud_airlines');
        if (!$can_crud_airlines) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([
                'supplier_business_name',
                'country',
                'nationality',
                'name',
                'mobile',
                'email',
                'evaluation',
                'landline'
            ]);


            $input['type'] = 'recruitment';
            $input['supplier_business_name'] = $request->input('Office_name');
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
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
        return redirect()->route('international-Relations.EmploymentCompanies');
        //return $output;
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
