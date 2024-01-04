<?php

namespace Modules\Sales\Http\Controllers;
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

class SalesProjectController extends Controller
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


        $SalesProjects = SalesProject::with(['contact']);
        $cities = EssentialsCity::forDropdown();
        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');
        if (request()->ajax()) {


            return Datatables::of($SalesProjects)
                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'contact_name',
                    function ($row) {
                        return $row->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'contact_location_name',
                    function ($row) {
                        return  $row->name;
                    }
                )
                ->addColumn(
                    'assigned_to',
                    function ($row) use ($name_in_charge_choices) {
                        $names = "";
                        $userIds = json_decode($row->assigned_to, true);
                
                        if ($userIds) {
                            $lastUserId = end($userIds);
                
                            foreach ($userIds as $user_id) {
                                $names .= $name_in_charge_choices[$user_id];
                
                                if ($user_id !== $lastUserId) {
                                    $names .= ', ';
                                }
                            }
                        }
                
                        return $names;
                    }
                )
                
           

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            $html .= '<a href="' . route('sale.editSaleProject', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                             &nbsp;';
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button" data-href="' . route('sale.destroySaleProject', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
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


                ->rawColumns(['id','assigned_to', 'contact_name', 'action'])
                ->make(true);
        }
      
        $cities = EssentialsCity::forDropdown();
        $contacts = Contact::pluck('supplier_business_name', 'id');
        return view('sales::sales_projects.index')->with(compact('cities','contacts', 'name_in_charge_choices', 'cities'));
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

            $contactLocation['contact_id'] = $request->contact_name;
            $contactLocation['name'] = $request->contact_location_name;
            $contactLocation['city'] = $request->contact_location_city;
            $contactLocation['name_in_charge'] = $request->contact_location_name_in_charge;
            $contactLocation['phone_in_charge'] = $request->contact_location_phone_in_charge;
            $contactLocation['email_in_charge'] = $request->contact_location_email_in_charge;
            SalesProject::create($contactLocation);


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
            return redirect()->route('sale.saleProjects')->withErrors([$output['msg']]);
        }

        return redirect()->route('sale.saleProjects')->with('success', $output['msg']);
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


        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');
        $contactLocation = SalesProject::findOrFail($id);
        $cities = EssentialsCity::forDropdown();
        $contacts = Contact::pluck('supplier_business_name', 'id');

        return view('sales::sales_projects.edit')->with(compact('cities','name_in_charge_choices','contacts','contactLocation'));
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
            $contactLocation['contact_id'] = $request->contact_name;
            $contactLocation['name'] = $request->contact_location_name;
            $contactLocation['city'] = $request->contact_location_city;
            $contactLocation['name_in_charge'] = $request->contact_location_name_in_charge;
            $contactLocation['phone_in_charge'] = $request->contact_location_phone_in_charge;
            $contactLocation['email_in_charge'] = $request->contact_location_email_in_charge;
            SalesProject::where('id', $id)->update($contactLocation);
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
            return redirect()->route('sale.saleProjects')->withErrors([$output['msg']]);
        }


        return redirect()->route('sale.saleProjects')->with('success', $output['msg']);
    }

    public function destroy($id)
    {
       
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            SalesProject::where('id', $id)
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
