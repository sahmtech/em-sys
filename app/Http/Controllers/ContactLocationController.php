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
use Modules\Sales\Entities\SalesProject;

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

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_crud_contact_locations = auth()->user()->can('followup.crud_contact_locations');
        if (!($is_admin || $can_followup_crud_contact_locations)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }



        $contact_locations = ContactLocation::with(['project']);
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
                    'project_name',
                    function ($row) {
                        return $row->project->name;
                    }
                )
                ->addColumn(
                    'location_name',
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
                        if ($is_admin || auth()->user()->can('followup.editContactLocations')) {
                            $html .= '<a href="' . route('sale.editContactLocations', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                             &nbsp;';
                        }
                        if ($is_admin || auth()->user()->can('followup.deleteContactLocations')) {
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button" data-href="' . route('sale.destroyContactLocations', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                ->filterColumn('project_name', function ($query, $keyword) {

                    $query->whereHas('project', function ($qu) use ($keyword) {
                        $qu->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('location_name', function ($query, $keyword) {

                    $query->where('name', 'like', "%{$keyword}%");
                })


                ->rawColumns(['id', 'contact_location_email_in_charge', 'contact_location_phone_in_charge', 'contact_location_name_in_charge', 'contact_location_city', 'contact_location_name', 'project_name', 'location_name', 'action'])
                ->make(true);
        }
        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');
        $cities = EssentialsCity::forDropdown();
        $contacts = SalesProject::pluck('name', 'id');
        return view('sales::contact_locations.index')->with(compact('cities', 'contacts', 'name_in_charge_choices', 'cities'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            error_log($request->contact_name);

            $contactLocation['sales_project_id'] = $request->contact_name;
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $contactLocation = ContactLocation::findOrFail($id);
        $cities = EssentialsCity::forDropdown();
        $contacts = SalesProject::pluck('name', 'id');
        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');
        return view('sales::contact_locations.edit')->with(compact('cities', 'name_in_charge_choices', 'contacts', 'contactLocation'));
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $contactLocation['sales_project_id'] = $request->contact_name;
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



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