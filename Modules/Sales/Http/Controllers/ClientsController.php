<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\Notifications\CustomerNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use App\Events\ContactCreatedOrModified;
use Illuminate\Support\Facades\Hash;

class ClientsController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
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
     

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $can_crud_customers = auth()->user()->can('sales.crud_customers');
        if (! $can_crud_customers) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
       
        if (request()->ajax()) {
            $contacts = DB::table('contacts')
            ->select([
                'id',
                'supplier_business_name',
                'contact_id',
              
                'commercial_register_no',
                'mobile',
                'email',
                'city'
            ])->where('business_id',$business_id);
            //dd($contacts);
            return Datatables::of($contacts)
                // ->addColumn('nameAr', function ($row) {
                //     $name = json_decode($row->name, true);
                //     return $name['ar'] ?? '';
                // })
                // ->addColumn('nameEn', function ($row) {
                //     $name = json_decode($row->name, true);
                //     return $name['en'] ?? '';
                // })
                ->addColumn('action', function ($row) {
                
                    $html = '<a href="' . route('sale.clients.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                   // $html .= '&nbsp;<button class="btn btn-xs btn-danger delete_country_button" data-href="' . route('sale.deleteCustomer', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    $html .= '&nbsp;<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>'; // New view button
                    return $html;
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
              
                ->rawColumns(['action'])
                ->make(true);
        }
        $types = [];
       
        if (auth()->user()->can('customer.create') || auth()->user()->can('customer.view_own')) {
            $types['lead'] = __('report.customer');
        }
       
     
        return view('sales::contacts.index')->with(compact('types','users'));
           
    }

  

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
      
        if (! auth()->user()->can('supplier.create') && ! auth()->user()->can('customer.create') && ! auth()->user()->can('customer.view_own') && ! auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

      
        return view('sales::contacts.create')->with(compact( 'types' ));
           
 

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if ( ! auth()->user()->can('customer.create') && ! auth()->user()->can('customer.view_own') ) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');

            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only(['type','contact_id','name_en','first_name','last_name', 
            'supplier_business_name','commercial_register_no','mobile'
             ,'alternate_number','email','assigned_to_users','user_id','selected_user_id',
             'last_name_cs','first_name_cs','english_name_cs','capacity_cs','nationality_cs',
                'email_cs','identityNO_cs','mobile_cs','allow_login','username_cs','password_cs',
            'first_name_cf','last_name_cf','english_name_cf','email_cf','mobile_cf','allow_login_cf','username_cf','password_cf']);

     
           // dd($input['user_id']);
            $name_array = [];

           
            if (! empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
           
            if (! empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }

            $input['name'] = trim(implode(' ', $name_array));

            $input['english_name']=$request->input('name_en');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');
            $input['responsible_user_id']= $input['selected_user_id'];
      //  dd( $input);
            DB::beginTransaction();
            $output = $this->contactUtil->createNewContact($input);
            $responseData = $output['data']; // Accessing the 'data' array from the response
            $contactId = $responseData->id;
          

            event(new ContactCreatedOrModified($input, 'added'));

            $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $output['data'], 'input' => $request->input()]);

            $this->contactUtil->activityLog($output['data'], 'added');

            DB::commit();

            
            $contract_signer_input['crm_contact_id']=$contactId;
            $contract_signer_input['first_name']=$request->input('first_name_cs');
            $contract_signer_input['last_name']=$request->input('last_name_cs');
            $contract_signer_input['english_name']=$request->input('english_name_cs');
            $contract_signer_input['capacity_cs']=$request->input('capacity_cs');
            $contract_signer_input['nationality_cs']=$request->input('nationality_cs');
            $contract_signer_input['email']=$request->input('email_cs');
            $contract_signer_input['identity_number']=$request->input('identityNO_cs');
            $contract_signer_input['contact_number']=$request->input('mobile_cs');
            $contract_signer_input['business_id']=$request->session()->get('user.id');
         
            if($request->input('allow_login')==true)
            {
                $contract_signer_input['type']='customer_user'; 
                $contract_signer_input['username']=$request->input('username_cs'); 
                if (!empty($input['password_cs'])) {
                    $contract_signer_input['password'] = Hash::make($request->input('password_cs'));
                } 
            }
            else
            {
                $contract_signer_input['type']='user'; 
            }
            $contract_signer_input['contact_user_type']='contact_signer';
            $contract_signer = User::create($contract_signer_input);


            $contract_follower_input['crm_contact_id']=$contactId;
            $contract_follower_input['first_name']=$input['first_name_cf'];
            $contract_follower_input['last_name']=$input['last_name_cf'];
            $contract_follower_input['english_name']=$input['english_name_cf'];
            $contract_follower_input['email']=$input['email_cf'];
            $contract_follower_input['contact_number']=$input['mobile_cf'];
            $contract_follower_input['business_id']=$request->session()->get('user.id');

            if($request->input('allow_login_cf')==true)
            {
                $contract_follower_input['type']="customer_user"; 
                $contract_follower_input['username']=$input['username_cs']; 
                if (!empty($input['password_cs'])) {
                    $contract_follower_input['password'] = Hash::make($input['password_cs']);
                } 
            }
            else
            {$contract_follower_input['type']="user"; }
            $contract_follower_input['contact_user_type']='contract_follower';
            $contract_follower = User::create($contract_follower_input);

  
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

    return redirect()->route('sale.clients');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if ( ! auth()->user()->can('customer.view') && ! auth()->user()->can('customer.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = $this->contactUtil->getContactInfo($business_id, $id);
      
        $is_selected_contacts = User::isSelectedContacts(auth()->user()->id);
        $user_contacts = [];
        if ($is_selected_contacts) {
            $user_contacts = auth()->user()->contactAccess->pluck('id')->toArray();
        }

    
        if (! auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            if ($contact->created_by != auth()->user()->id & ! in_array($contact->id, $user_contacts)) {
                abort(403, 'Unauthorized action.');
            }
        }

        //$reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($contact->type, ['customer', 'both'])) ? true : false;

        $contact_dropdown = Contact::contactDropdown($business_id, false, false);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        //get contact view type : ledger, notes etc.
        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'ledger';
        }

        $contact_view_tabs = $this->moduleUtil->getModuleData('get_contact_view_tabs');

        $activities = Activity::forSubject($contact)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

           $contactSigners = DB::table('users')
           ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
           ->where('users.contact_user_type', 'contact_signer')
           ->select('users.*')
           ->get();

           $contactFollower = DB::table('users')
           ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
           ->where('users.contact_user_type', 'contract_follower')
           ->select('users.*')
           ->get();
       
        return view('sales::contacts.show')
             ->with(compact('contact',
             'contactSigners',
             'contactFollower',
              'contact_dropdown',
               'business_locations', 'view_type', 'contact_view_tabs', 'activities'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
       
        $business_id = request()->session()->get('user.business_id');
        $contact = Contact::where('business_id', $business_id)->find($id);

        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $is_selected_contacts = User::isSelectedContacts(auth()->user()->id);
        $user_contacts = [];
        if ($is_selected_contacts) {
            $user_contacts = auth()->user()->contactAccess->pluck('id')->toArray();
        }

    
        $types = [];
      
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
      

      
           $contact_dropdown = Contact::contactDropdown($business_id, false, false);

       
           $contactSigners = DB::table('users')
           ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
           ->where('contacts.id', $id)
           ->where('users.contact_user_type', 'contact_signer')
           ->select('users.*')
           ->get();
          //dd( $contactSigners[0]->allow_login);
           $contactFollower = DB::table('users')
           ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
           ->where('users.contact_user_type', 'contract_follower')
           ->where('contacts.id', $id)
           ->select('users.*')
           ->get();
       
        return view('sales::contacts.edit')
             ->with(compact('types','contact','contactSigners','contactFollower', 'contact_dropdown'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if ( ! auth()->user()->can('customer.create') && ! auth()->user()->can('customer.view_own') ) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');

            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only(['type','contact_id','name_en','first_name','last_name', 'supplier_business_name','commercial_register_no','mobile'
        ,'alternate_number','email','assigned_to_users',
            ]);

      
            $name_array = [];

           
            if (! empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
           
            if (! empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }

            $input['name'] = trim(implode(' ', $name_array));

            $input['english_name']=$request->input('name_en');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

        
            DB::beginTransaction();
            $contact = Contact::findOrFail($id);
       // dd( $input);
            $contact->update($input);
         
            $contactId = $contact->id;
            DB::commit();

            
           // $contract_signer_input['crm_contact_id']=$contactId;
            $contract_signer_input['first_name']=$request->input('first_name_cs');
            $contract_signer_input['last_name']=$request->input('last_name_cs');
            $contract_signer_input['english_name']=$request->input('english_name_cs');
            $contract_signer_input['capacity_cs']=$request->input('capacity_cs');
            $contract_signer_input['nationality_cs']=$request->input('nationality_cs');
            $contract_signer_input['email']=$request->input('email_cs');
            $contract_signer_input['identity_number']=$request->input('identityNO_cs');
            $contract_signer_input['contact_number']=$request->input('mobile_cs');
            $contract_signer_input['business_id']=$request->session()->get('user.id');
         
            if($request->input('allow_login')==true)
            {
                $contract_signer_input['type']='customer_user'; 
                $contract_signer_input['username']=$request->input('username_cs'); 
                if (!empty($input['password_cs'])) {
                    $contract_signer_input['password'] = Hash::make($request->input('password_cs'));
                } 
            }
            else
            {$contract_signer_input['type']='user'; 
            }
            $contract_signer_input['contact_user_type']='contact_signer';

            $contract_signer_user = User::where('crm_contact_id',$contactId);
            $contract_signer = $contract_signer_user->update($contract_signer_input);


           // $contract_follower_input['crm_contact_id']=$contactId;
            $contract_follower_input['first_name']=$input['first_name_cf'];
            $contract_follower_input['last_name']=$input['last_name_cf'];
            $contract_follower_input['english_name']=$input['english_name_cf'];
            $contract_follower_input['email']=$input['email_cf'];
            $contract_follower_input['contact_number']=$input['mobile_cf'];
            $contract_follower_input['business_id']=$request->session()->get('user.id');

            if($request->input('allow_login_cf')==true)
            {
                $contract_follower_input['type']="customer_user"; 
                $contract_follower_input['username']=$input['username_cs']; 
                if (!empty($input['password_cs'])) {
                    $contract_follower_input['password'] = Hash::make($input['password_cs']);
                } 
            }
            else
            {$contract_follower_input['type']="user"; }
            $contract_follower_input['contact_user_type']='contract_follower';
            $contract_follower_user = User::where('crm_contact_id',$contactId);
            $contract_follower = $contract_follower_user->update($contract_follower_input);

            $output = ['success' => true,
            'msg' => __('lang_v1.added_success'),
        
        ];
        } 
        
        catch (\Exception $e)
         {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('sale.clients');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request,$id)
    {
       
        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $contact = Contact::where('business_id', $business_id)->findOrFail($id);
       
       if ($request->ajax())
       {
        try {
            User::where('crm_contact_id', $contact->id)
            ->update(['allow_login' => 0]);

            Contact::where('id', $id)->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       }
      
     
       
       return $output;
           
  
        
     
    }


    public function deleteContact($id)
{
    try {
        $contact = Contact::findOrFail($id);
        User::where('crm_contact_id', $contact->id)->delete();
       // dd($contact);
        $contact->delete();

        return response()->json([
            'success' => true,
            'msg' => __('lang_v1.deleted_success'),
        ]);
    } catch (\Exception $e) {
        \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

        return response()->json([
            'success' => false,
            'msg' => __('messages.something_went_wrong'),
        ]);
    }
    return redirect()->route('sale.clients');
}

}
