<?php

namespace Modules\Essentials\Http\Controllers;

use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use App\Contact;
use Modules\Essentials\Entities\EssentialsCity;


class EssentialsInsuranceCompanyController extends Controller
{
    protected $moduleUtil;
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_insurance_companies = auth()->user()->can('essentials.crud_insurance_companies');
        if (!$can_crud_insurance_companies) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $insuranceCompanies = Contact::join('business', 'business.id', '=', 'contacts.business_id')
                ->where('contacts.type', 'insurance');

            if (!(auth()->user()->can('superadmin'))) {
                $insuranceCompanies->where('business.id', '=', $business_id);
            }
            $insuranceCompanies->select([
                'contacts.id',
                'business.name',
                'contacts.supplier_business_name',
                'contacts.city',
                'contacts.state',
                'contacts.country',
                'contacts.tax_number',
                'contacts.address_line_1',
                'contacts.mobile',
                'contacts.landline',
            ]);


            // if (!empty(request()->input('user_id'))) {
            //     $official_documents->where('essentials_official_documents.employee_id', request()->input('user_id'));
            // }

            // if (!empty(request()->input('status'))) {
            //     $official_documents->where('essentials_official_documents.status', request()->input('status'));
            // }

            // if (!empty(request()->input('doc_type'))) {
            //     $official_documents->where('essentials_official_documents.type', request()->input('doc_type'));
            // }

            // if (!empty(request()->start_date) && !empty(request()->end_date)) {
            //     $start = request()->start_date;
            //     $end = request()->end_date;
            //     $official_documents->whereDate('essentials_official_documents.expiration_date', '>=', $start)
            //         ->whereDate('essentials_official_documents.expiration_date', '<=', $end);
            // }

            return Datatables::of($insuranceCompanies)
                //' . route('doc.view', ['id' => $row->id]) . '
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        //$html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>&nbsp;';
                        //$html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>&nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_insurance_company_button" data-href="' . route('insurance_companies.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        return $html;
                    }
                )
                ->filterColumn('supplier_business_name', function ($query, $keyword) {
                    $query->where('supplier_business_name', "LIKE", "%{$keyword}%");
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        $countries = EssentialsCountry::forDropdown();
        $cities = EssentialsCity::forDropdown();
        $businesses = null;
        if (!(auth()->user()->can('superadmin'))) {
            $businesses = Business::forDropdown($business_id);
        } else {
            $businesses = Business::forDropdown();
        }
        return view('essentials::insurance_companies.index')->with(compact('countries', 'cities', 'businesses'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['business_name', 'insurance_company', 'city', 'state', 'country', 'address', 'tax_number', 'phone_number', 'mobile_number']);
            $Contact_data['business_id'] = $business_id;
            $Contact_data['created_by'] = $user_id;
            $Contact_data['supplier_business_name'] = $input['insurance_company'];
            $Contact_data['name'] = $input['business_name'];
            $Contact_data['tax_number'] = $input['tax_number'];
            $Contact_data['city'] = $input['city'];
            $Contact_data['state'] = $input['state'];
            $Contact_data['country'] = $input['country'];
            $Contact_data['address_line_1'] = $input['address'];
            $Contact_data['landline'] = $input['phone_number'];
            $Contact_data['mobile'] = $input['mobile_number'];
            $Contact_data['created_by '] = $input['mobile_number'];
            $Contact_data['type'] = 'insurance';
            Contact::create($Contact_data);
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

        return redirect()->route('insurance_companies')->with('status', $output);
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
        return view('essentials::edit');
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            Contact::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
