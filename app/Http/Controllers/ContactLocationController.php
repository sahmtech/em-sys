<?php

namespace App\Http\Controllers;

use App\Contact;
use App\ContactLocation;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Http\Response;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Sales\Entities\salesContractItem;

class ContactLocationController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!($is_admin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $contact_locations = ContactLocation::with(['contact']);
        // return $contact_locations->get();
        $cities = EssentialsCity::forDropdown();
        if (request()->ajax()) {


            return Datatables::of($contact_locations)
                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'contact_name',
                    function ($row) {
                        return $row->contact->supplier_business_name;
                    }
                )
                ->addColumn(
                    'contact_location_name',
                    function ($row) {
                        return  $row->name;
                    }
                )
                ->addColumn(
                    'contact_location_city',
                    function ($row) use ($cities) {
                        if ($row->city) {
                            return  $cities[$row->city];
                        } else return null;
                    }
                )
                ->addColumn(
                    'contact_location_name_in_charge',
                    function ($row) {
                        return   $row->name_in_charge;
                    }
                )
                ->addColumn(
                    'contact_location_phone_in_charge',
                    function ($row) {
                        return  $row->phone_in_charge;
                    }
                )
                ->addColumn(
                    'contact_location_email_in_charge',
                    function ($row) {
                        return $row->email_in_charge;
                    }
                )

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            $html .= '<a href="' . route('sale.editContactLocations', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                             &nbsp;';
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button" data-href="' . route('sale.destroyContactLocations', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                ->filterColumn('contact_name', function ($query, $keyword) {

                    $query->whereHas('contact', function ($qu) use ($keyword) {
                        $qu->where('supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contact_location_name', function ($query, $keyword) {

                    $query->where('name', 'like', "%{$keyword}%");
                })


                ->rawColumns(['id', 'contact_location_email_in_charge', 'contact_location_phone_in_charge', 'contact_location_name_in_charge', 'contact_location_city', 'contact_location_name', 'contact_name', 'contact_id', 'action'])
                ->make(true);
        }
        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');

        $contacts = Contact::pluck('supplier_business_name', 'id',);
        return view('sales::contact_locations.index')->with(compact('contacts', 'name_in_charge_choices', 'cities'));
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

        if (!($is_admin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            error_log($request->contact_name);

            $contactLocation['contact_id'] = $request->contact_name;
            $contactLocation['name'] = $request->contact_location_name;
            $contactLocation['city'] = $request->contact_location_city;
            $contactLocation['name_in_charge'] = $request->contact_location_name_in_charge;
            $contactLocation['phone_in_charge'] = $request->contact_location_phone_in_charge;
            $contactLocation['email_in_charge'] = $request->contact_location_email_in_charge;
            ContactLocation::create($contactLocation);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->route('sale.contactLocations')->withErrors([$output['msg']]);
        }

        return redirect()->route('sale.contactLocations')->with('success', $output['msg']);
    }
    // /**
    //  * Show the specified resource.
    //  * @param int $id
    //  * @return Renderable
    //  */
    // public function show($id)
    // {
    //     return view('sales::show');
    // }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!($is_admin
            || auth()->user()->can('superadmin')
            || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $contactLocation = ContactLocation::findOrFail($id);


        return view('sales::contact_locations.edit')->with(compact('contactLocation'));
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

        if (!($is_admin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $contactLocation['contact_id'] = $request->contact_name;
            $contactLocation['name'] = $request->contact_location_name;
            $contactLocation['city'] = $request->contact_location_city;
            $contactLocation['name_in_charge'] = $request->contact_location_name_in_charge;
            $contactLocation['phone_in_charge'] = $request->contact_location_phone_in_charge;
            $contactLocation['email_in_charge'] = $request->contact_location_email_in_charge;
            ContactLocation::where('id', $id)->update($contactLocation);
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
            return redirect()->route('sale.contactLocations')->withErrors([$output['msg']]);
        }


        return redirect()->route('sale.contactLocations')->with('success', $output['msg']);
    }

    public function destroy($id)
    {
        error_log("Asdas");
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!($is_admin || auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            ContactLocation::where('id', $id)
                ->delete();

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

        return $output;
    }
}
