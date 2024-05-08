<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsCity;
use App\User;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use Carbon\Carbon;
use App\CustomerGroup;
use App\Notifications\CustomerNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Modules\Essentials\Entities\EssentialsCountry;
use DB;
use App\Events\ContactCreatedOrModified;
use Illuminate\Support\Facades\Hash;

class ClientsController extends Controller
{

    protected $moduleUtil;
    protected $statuses;

    protected $contactUtil;

    public function __construct(ModuleUtil $moduleUtil,  ContactUtil $contactUtil)
    {

        $this->moduleUtil = $moduleUtil;
        $this->contactUtil = $contactUtil;
        $this->statuses = [
            'qualified' => [
                'name' => __('sales::lang.qualified'),
                'class' => 'bg-green',
            ],
            'unqualified' => [
                'name' => __('sales::lang.unqualified'),
                'class' => 'bg-red',
            ],

        ];
    }


    public function draft_contacts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_edit_contact = auth()->user()->can('sales.edit_draft_contact');
        $can_view_contact_info = auth()->user()->can('sales.view_contact_info');
        $can_change_contact_status = auth()->user()->can('sales.change_to_lead');


        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        $contacts = DB::table('contacts')
            ->select([
                'id', 'supplier_business_name', 'type', 'contact_id', 'created_by', 'created_at',
                'commercial_register_no', 'mobile', 'email', 'city'

            ])->where('business_id', $business_id)->where('type', 'draft');
        $cities = EssentialsCity::forDropdown();
        if (request()->ajax()) {


            return Datatables::of($contacts)

                ->addColumn('action', function ($row) use ($is_admin, $can_edit_contact, $can_view_contact_info, $can_change_contact_status) {
                    $html  = '';
                    if ($is_admin || $can_edit_contact) {
                        $html = '<a href="' . route('sale.clients.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                    }
                    if ($is_admin || $can_view_contact_info) {
                        $html .= '&nbsp;<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>'; // New view button
                    }
                    if ($is_admin || $can_change_contact_status) {
                        $html .= '<button style="height: 25px; padding: 0 12px; font-size: 1.0 rem;" class="btn btn-warning btn-sm btn-change-to-lead" data-contact-id="' . $row->id . '">' . __('sales::lang.change_to_lead') . '</button>';
                    }

                    return $html;
                })

                ->editColumn('created_by', function ($row) use ($users) {

                    return $users[$row->created_by];
                })
                ->editColumn('created_at', function ($row) use ($users) {

                    return Carbon::parse($row->created_at);
                })

                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $status = $this->statuses;
        $nationalities = EssentialsCountry::nationalityForDropdown();
        return view('sales::contacts.draft_contacts')->with(compact('users', 'status', 'cities', 'nationalities'));
    }
    public function lead_contacts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_edit_contact = auth()->user()->can('sales.edit_lead_contact');
        $can_view_contact_info = auth()->user()->can('sales.view_contact_info');
        $can_change_contact_status = auth()->user()->can('sales.change_contact_status');


        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        $contacts = DB::table('contacts')
            ->select([
                'id', 'supplier_business_name', 'type', 'contact_id', 'created_by', 'created_at',
                'commercial_register_no', 'mobile', 'email', 'city'

            ])->where('business_id', $business_id)->where('type', 'lead');
        $cities = EssentialsCity::forDropdown();
        if (request()->ajax()) {


            return Datatables::of($contacts)

                ->addColumn('action', function ($row) use ($is_admin, $can_view_contact_info, $can_change_contact_status) {

                    if ($is_admin || $can_view_contact_info) {
                        $html = '&nbsp;<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>'; // New view button
                    }
                    if ($is_admin || $can_change_contact_status) {
                        $html .= '&nbsp;<a href="' . route('changeContactStatus', ['id' => $row->id]) . '"
                        data-href="' . route('changeContactStatus', ['id' => $row->id])  . ' "  
                        data-container="#changeStatusContactModal" class="btn btn-xs btn-modal btn-warning" style="margin-top:3px;"> ' . __('sales::lang.change_contact_status') . '</a>'; // New view button
                        // $html .= '&nbsp;<button type="button" class="btn btn-warning btn-sm custom-btn" id="change-status-client">'.__('sales::lang.change_contact_status').'</button>'; // New view button
                    }
                    return $html;
                })

                ->editColumn('created_by', function ($row) use ($users) {

                    return $users[$row->created_by];
                })
                ->editColumn('created_at', function ($row) use ($users) {

                    return Carbon::parse($row->created_at);
                })

                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $status = $this->statuses;
        $nationalities = EssentialsCountry::nationalityForDropdown();
        return view('sales::contacts.lead_contacts')->with(compact('users', 'status', 'cities', 'nationalities'));
    }

    public function qualified_contacts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_contact_info = auth()->user()->can('sales.view_contact_info');
        $can_create_offer_price = auth()->user()->can('sales.add_offer_price');

        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');




        if (request()->ajax()) {
            $contacts = DB::table('contacts')
                ->select([
                    'id', 'supplier_business_name', 'type', 'contact_id', 'qualified_by', 'qualified_on',
                    'commercial_register_no', 'mobile', 'email', 'city'

                ])->where('business_id', $business_id)->where('type', 'qualified');

            return Datatables::of($contacts)

                ->addColumn('action', function ($row) use ($can_create_offer_price, $can_view_contact_info, $is_admin) {

                    // $html = '<a href="' . route('sale.clients.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                    if ($is_admin || $can_view_contact_info) {
                        $html = '<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>';
                    }
                    if ($is_admin || $can_create_offer_price) {
                        $html .= '&nbsp;<a href="' . route('create_offer_price_qualified_contacts', ['id' => $row->id, 'status' => 'quotation']) . '" class="btn btn-xs btn-warning" style="margin-top: 3px;"><i class="fa fa-plus"></i> ' . __('lang_v1.add_quotation') . '</a>'; // New view button
                    }
                    return $html;
                })
                ->editColumn('qualified_by', function ($row) use ($users) {
                    if ($row->qualified_by) {
                        return $users[$row->qualified_by];
                    } else {
                        return " ";
                    }
                })

                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }


        $nationalities = EssentialsCountry::nationalityForDropdown();

        return view('sales::contacts.qualified_contacts')->with(compact('nationalities'));
    }


    public function unqualified_contacts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_contact_info = auth()->user()->can('sales.view_contact_info');


        if (request()->ajax()) {
            $contacts = DB::table('contacts')
                ->select([
                    'id', 'supplier_business_name', 'type', 'contact_id',
                    'commercial_register_no', 'mobile', 'email', 'city'

                ])->where('business_id', $business_id)->where('type', 'unqualified');


            return Datatables::of($contacts)

                ->addColumn('action', function ($row) use ($is_admin, $can_view_contact_info) {


                    if ($is_admin || $can_view_contact_info) {
                        $html = '<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>'; // New view button
                    }
                    return $html;
                })

                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }


        $nationalities = EssentialsCountry::nationalityForDropdown();
        return view('sales::contacts.unqualified_contacts')->with(compact('nationalities'));
    }

    public function converted_contacts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_contact_info = auth()->user()->can('sales.view_contact_info');

        if (request()->ajax()) {
            $contacts = DB::table('contacts')
                ->select([
                    'id', 'supplier_business_name', 'type', 'contact_id',
                    'commercial_register_no', 'mobile', 'email', 'city'

                ])->where('business_id', $business_id)->where('type', 'converted');
                

            return Datatables::of($contacts)

                ->addColumn('action', function ($row) use ($is_admin, $can_view_contact_info) {

                    // $html = '<a href="' . route('sale.clients.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                    if ($is_admin || $can_view_contact_info) {
                        $html = '<a href="' . route('sale.clients.view', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a>'; // New view button
                    }
                    return $html;
                })

                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $nationalities = EssentialsCountry::nationalityForDropdown();
        return view('sales::contacts.converted_contacts')->with(compact('nationalities'));
    }
    public function getEnglishNameForCity(Request $request)
    {

        $selectedCity = $request->input('city');
        $city = EssentialsCity::find($selectedCity);


        $decodedName = json_decode($city->name, true);

        $englishName = isset($decodedName['en']) ? $decodedName['en'] : null;

        return response()->json(['relatedData' => $englishName]);
    }


    public function store(Request $request)
    {

        if (!auth()->user()->can('user.create') && !auth()->user()->can('customer.create') && !auth()->user()->can('customer.view_own')) {
            //temp  abort(403, 'Unauthorized action.');
        }
        try {

            $business_id = session()->get('user.business_id');

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([
                'contact_name', 'name_en', 'city', 'commercial_register_no', 'mobile', 'alternate_number', 'email',

                'last_name_cs', 'first_name_cs', 'english_name_cs', 'nationality_cs', 'email_cs', 'identityNO_cs', 'mobile_cs', 'username_cs', 'password_cs',

                'first_name_cf', 'last_name_cf', 'english_name_cf', 'email_cf', 'mobile_cf', 'username_cf', 'password_cf'
            ]);

            // $input['allow_login_cs'] = $request->filled('allow_login_cs');
            // $input['allow_login_cf'] = $request->filled('allow_login_cf');
            $latestRecord = Contact::whereIn('type', ['draft', 'lead', 'qualified', 'unqualified', 'converted'])->orderBy('ref_no', 'desc')->first();


            if ($latestRecord) {
                $latestRefNo = $latestRecord->ref_no;
                $numericPart = (int)substr($latestRefNo, 5);
                $numericPart++;
                $contact_input['ref_no'] = 'L' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);
            } else {

                $contact_input['ref_no'] = 'L0005000';
            }


            //store contact
            // $contact_input['name'] =  $request->input('contact_name');
            $contact_input['supplier_business_name'] = $request->input('contact_name');
            $contact_input['english_name'] = $request->input('name_en');
            $contact_input['commercial_register_no'] = $request->input('commercial_register_no');
            $contact_input['mobile'] = $request->input('mobile');
            $contact_input['alternate_number'] = $request->input('alternate_number');
            $contact_input['email'] = $request->input('email');
            $contact_input['business_id'] = $business_id;
            $contact_input['created_by'] = $request->session()->get('user.id');
            $contact_input['type'] = "draft";


            $output = $this->contactUtil->createNewContact($contact_input);
            $responseData = $output['data'];
            $contactId = $responseData->id;
            if ($contactId) {

                //add contact as user can't log in
                $userInfo['user_type'] = 'customer';
                $userInfo['first_name'] = $request->supplier_business_name;
                $userInfo['allow_login'] = 0;
                $userInfo['business_id'] =  $business_id;
                $userInfo['crm_contact_id'] =  $contactId;
                User::create($userInfo);
            }



            event(new ContactCreatedOrModified($contact_input, 'added'));

            $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $output['data'], 'input' => $request->input()]);

            $this->contactUtil->activityLog($output['data'], 'added');


            //store contact signer
            $contact_signer_input['crm_contact_id'] = $contactId;
            $contact_signer_input['first_name'] = $request->input('first_name_cs');
            $contact_signer_input['last_name'] = $request->input('last_name_cs');
            $contact_signer_input['english_name'] = $request->input('english_name_cs');
            $contact_signer_input['nationality_id'] = $request->input('nationality_cs');
            $contact_signer_input['email'] = $request->input('email_cs');
            $contact_signer_input['id_proof_number'] = $request->input('identityNO_cs');
            $contact_signer_input['contact_number'] = $request->input('mobile_cs');
            $contact_signer_input['business_id'] = $business_id;


            $contact_signer_input['user_type'] = 'customer_user';
            $contact_signer_input['username'] = null;
            $contact_signer_input['password'] = null;


            // $contact_signer_input['allow_login'] = $input['allow_login_cs'];
            $contact_signer_input['contact_user_type'] = 'contact_signer';
            if ($request->input('first_name_cs')) {
                User::create($contact_signer_input);
            }


            $contact_follower_input['crm_contact_id'] = $contactId;
            $contact_follower_input['first_name'] = $input['first_name_cf'];
            $contact_follower_input['last_name'] = $input['last_name_cf'];
            $contact_follower_input['english_name'] = $input['english_name_cf'];
            $contact_follower_input['email'] = $input['email_cf'];
            $contact_follower_input['contact_number'] = $input['mobile_cf'];
            $contact_follower_input['business_id'] = $business_id;


            $contact_follower_input['user_type'] = "customer_user";
            $contact_follower_input['username'] = null;
            $contact_follower_input['password'] = null;


            // $contact_follower_input['allow_login'] = $input['allow_login_cf'];
            $contact_follower_input['contact_user_type'] = 'contract_follower';

            User::create($contact_follower_input);
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }


        return redirect()->route('draft_contacts');
    }

    public function changeStatus(Request $request)
    {
        $user = auth()->user();

        $businessId = $request->session()->get('user.business_id');

        $todayDate = Carbon::now();


        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));
            if ($request->hasFile('file_lead')) {
                $file = $request->file('file_lead');
                $filePath = $file->store('/lead_files');
            }
            foreach ($selectedRowsData as $row) {
                $contact = Contact::find($row->id);

                if (!$contact) {

                    continue;
                }

                $contact->type = $request->status;
                $contact->file_lead = $filePath;
                $contact->note_lead = $request->note_lead;
                $contact->qualified_by = $businessId;
                $contact->qualified_on = $todayDate;


                $contact->save();
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function changeContact_Status_dialog()
    {
        $contact_id = request()->get('id', '');
        $status = $this->statuses;
        return view('sales::contacts.change_status', compact('contact_id', 'status'));
    }

    public function changeStatusContact(Request $request, $id)
    {
        $user = auth()->user();

        $businessId = $request->session()->get('user.business_id');

        $todayDate = Carbon::now();


        try {
            $filePath = null;
            // $selectedRowsData = json_decode($request->input('selectedRowsData'));
            if ($request->hasFile('file_lead')) {
                $file = $request->file('file_lead');
                $filePath = $file->store('/lead_files');
            }

            $contact = Contact::find($id);


            $contact->type = $request->status;
            $contact->file_lead = $filePath;
            $contact->note_lead = $request->note_lead;
            $contact->qualified_by = $businessId;
            $contact->qualified_on = $todayDate;


            $contact->save();


            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }

        return redirect()->back();
    }
    public function show($id)
    {
        if (!auth()->user()->can('customer.view') && !auth()->user()->can('customer.view_own')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = $this->contactUtil->getContactInfo($business_id, $id);

        $is_selected_contacts = User::isSelectedContacts(auth()->user()->id);
        $user_contacts = [];
        if ($is_selected_contacts) {
            $user_contacts = auth()->user()->contactAccess->pluck('id')->toArray();
        }


        if (!auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            if ($contact->created_by != auth()->user()->id & !in_array($contact->id, $user_contacts)) {
                //temp  abort(403, 'Unauthorized action.');
            }
        }



        $contact_dropdown = Contact::contactDropdown($business_id, false, false);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'ledger';
        }

        $contact_view_tabs = $this->moduleUtil->getModuleData('get_contact_view_tabs');

        $activities = Activity::forSubject($contact)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $contactSigners = user::with('country')
            ->where('users.crm_contact_id', $contact->id)

            ->where('users.contact_user_type', 'contact_signer')
            ->select('users.*')
            ->first();

        $contactFollower = DB::table('users')
            ->where('users.crm_contact_id', $contact->id)
            ->where('users.contact_user_type', 'contract_follower')
            ->select('users.*')
            ->first();

        return view('sales::contacts.show')
            ->with(compact(
                'contact',
                'contactSigners',
                'contactFollower',
                'contact_dropdown',
                'business_locations',
                'view_type',
                'contact_view_tabs',
                'activities'
            ));
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

        if (!$this->moduleUtil->isSubscribed($business_id)) {
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
        $contactSigners = user::with('country')
            ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
            ->where('contacts.id', $id)
            ->where('users.contact_user_type', 'contact_signer')
            ->select('users.*')
            ->first();

        $contactFollower = DB::table('users')
            ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
            ->where('users.contact_user_type', 'contract_follower')
            ->where('contacts.id', $id)
            ->select('users.*')
            ->first();




        $nationalities = EssentialsCountry::nationalityForDropdown();
        return view('sales::contacts.edit')
            ->with(compact('types', 'contact', 'contactSigners', 'contactFollower', 'contact_dropdown', 'nationalities'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('customer.create') && !auth()->user()->can('customer.view_own')) {
            //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([
                'type', 'contact_id',
                'supplier_business_name', 'commercial_register_no', 'mobile', 'alternate_number', 'email', 'user_id', 'selected_user_id',

                'first_name_cf', 'last_name_cf', 'english_name_cf', 'email_cf', 'mobile_cf',
            ]);

            // $input['allow_login_cs'] = $request->filled('allow_login_cs');
            // $input['allow_login_cf'] = $request->filled('allow_login_cf');

            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');


            DB::beginTransaction();
            $contact = Contact::findOrFail($id);


            $contact->update($input);


            $contactSigners = User::with('country')
                ->join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
                ->where('contacts.id', $contact->id)
                ->where('users.contact_user_type', 'contact_signer')
                ->select('users.*')
                ->first();



            $contract_signer_input = [
                'crm_contact_id' => $contact->id,
                'first_name' => $request->input('first_name_cs'),
                'last_name' => $request->input('last_name_cs'),
                'english_name' => $request->input('english_name_cs'),
                'capacity_cs' => $request->input('capacity_cs'),
                'nationality_id' => $request->input('nationality_cs'),
                'email' => $request->input('email_cs'),
                'id_proof_number' => $request->input('identityNO_cs'),
                'contact_number' => $request->input('mobile_cs'),
                'business_id' => $request->session()->get('user.id'),
                // 'allow_login' => $input['allow_login_cs'],
                'contact_user_type' => 'contact_signer',
            ];

            // if ($input['allow_login_cs'] == true) {
            //     $contract_signer_input['user_type'] = 'customer_user';
            //     $contract_signer_input['username'] = $request->input('username_cs');

            //     if (!empty($input['password_cs'])) {
            //         $contract_signer_input['password'] = Hash::make($request->input('password_cs'));
            //     }
            // } else {
            $contract_signer_input['user_type'] = 'customer_user';
            $contract_signer_input['username'] = null;
            $contract_signer_input['password'] = null;
            // }
            if ($contactSigners != null) {
                $contactSigners->update($contract_signer_input);
            } else {
                if ($request->input('first_name_cs') != null) {
                    $contract_signer = User::create($contract_signer_input);
                }
            }



            $contactFollower = User::join('contacts', 'users.crm_contact_id', '=', 'contacts.id')
                ->where('users.contact_user_type', 'contract_follower')
                ->where('contacts.id',   $contact->id)
                ->select('users.*')
                ->first();

            $contract_follower_input = [
                'crm_contact_id' => $contact->id,
                'first_name' => $request->input('first_name_cf'),
                'last_name' => $request->input('last_name_cf'),
                'english_name' => $request->input('english_name_cf'),

                'email' => $request->input('email_cf'),

                'contact_number' => $request->input('mobile_cf'),
                'business_id' => $request->session()->get('user.id'),
                // 'allow_login' => $input['allow_login_cf'],
                'contact_user_type' => 'contract_follower',
            ];



            $contract_follower_input['user_type'] = 'customer_user';
            $contract_follower_input['username'] = null;
            $contract_follower_input['password'] = null;


            if ($contactFollower != null) {
                $contactFollower->update($contract_follower_input);
            } else {
                if ($request->input('first_name_cf') != null) {
                    $contract_follower = User::create($contract_follower_input);
                }
            }

            DB::commit();




            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),

            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('draft _contacts');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request, $id)
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $contact = Contact::where('business_id', $business_id)->findOrFail($id);

        if ($request->ajax()) {
            try {
                User::where('crm_contact_id', $contact->id)
                    ->update(['allow_login' => 0]);

                Contact::where('id', $id)->delete();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
        }



        return $output;
    }

    public function changeDraftStatus($id)
    {

        try {
            $draft = Contact::find($id);
            if (!$draft) {
                return ['success' => false, 'msg' => __('messages.not_found')];
            }

            $draft->update([
                'type' => 'lead',

            ]);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    public function deleteContact($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            User::where('crm_contact_id', $contact->id)->delete();

            $contact->delete();

            return response()->json([
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
        return redirect()->route('sale.clients');
    }


    public function change_to_converted_client(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');


        try {
            $selectedRows = $request->input('selectedRows');

            Contact::whereIn('id', $selectedRows)->update(['type' => 'converted']);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.send_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }


    public function  change_contact_status_api()
    {


        $contacts = Contact::all();


        foreach ($contacts as $contact) {
            $contact->type = 'converted';
            $contact->save();
        }




        return 'success';
    }
}