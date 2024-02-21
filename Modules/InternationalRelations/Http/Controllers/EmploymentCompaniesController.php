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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_employment_company_delegation_requests = auth()->user()->can('internationalrelations.view_employment_company_delegation_requests');
        $can_edit_employee_company = auth()->user()->can('internationalrelations.edit_employment_company');
        if (!($is_admin || $can_view_employment_company_delegation_requests)) {
        }

        $countries = EssentialsCountry::forDropdown();
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = DB::table('contacts')
            ->leftJoin('essentials_countries', 'contacts.country', '=', 'essentials_countries.id')
            ->leftJoin('ir_delegations', 'contacts.id', '=', 'ir_delegations.agency_id')
            ->select([
                'contacts.id',
                'contacts.supplier_business_name',
                'essentials_countries.name as country',
                'contacts.multi_nationalities',
                'contacts.name',
                'contacts.mobile',
                'contacts.email',
                'contacts.evaluation',
                'contacts.landline'

            ])

            ->where('type', 'recruitment')
            ->orderBy('id', 'desc');


        if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {
            $nationalityId = request()->input('nationality');
            $contacts->where(function ($query) use ($nationalityId) {
                $query->WhereJsonContains('contacts.multi_nationalities', $nationalityId);
            });
        }
        if (!empty(request()->input('country')) && request()->input('country') !== 'all') {
            $contacts->where('essentials_countries.id', request()->input('country'));
        }
        if (!empty($request->input('evaluation_filter')) && $request->input('evaluation_filter') !== 'all') {
            $evaluationFilter = $request->input('evaluation_filter');
            $contacts->where('contacts.evaluation', $evaluationFilter);
        }

        if (!empty($request->input('company_requests_filter'))) {

            if ($request->input('company_requests_filter') === 'has_agency_requests') {

                $contacts->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('ir_delegations')
                        ->whereRaw('ir_delegations.agency_id = contacts.id');
                });
            } elseif ($request->input('company_requests_filter') === 'has_not_agency_requests') {
                $contacts->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('ir_delegations')
                        ->whereRaw('ir_delegations.agency_id = contacts.id');
                });
            }
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

                ->addColumn('nationalities', function ($contact) {
                    $nationalities = json_decode($contact->multi_nationalities, true);

                    if ($nationalities !== null) {
                        $nationalityNames = [];

                        foreach ($nationalities as $nationalityId) {
                            $nationality = EssentialsCountry::where('id', $nationalityId)->value('nationality');
                            if ($nationality) {
                                $nationalityNames[] = $nationality;
                            }
                        }


                        $nationalityList = implode(" - ", $nationalityNames);
                        return $nationalityList;
                    } else {
                        return '';
                    }
                })



                ->addColumn('comp_evaluation', function ($contact) {

                    $translatedEvaluation = ($contact->evaluation == 'good') ? __('essentials::lang.good') : __('essentials::lang.bad');
                    if (!$translatedEvaluation) {
                        return '';
                    }
                    return $translatedEvaluation;
                })


                ->addColumn('action', function ($row) use ($can_view_employment_company_delegation_requests, $is_admin, $can_edit_employee_company) {
                    $html = '';


                    if ($is_admin || $can_view_employment_company_delegation_requests) {
                        $html .= '<a href="' . route('companyRequests', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('internationalrelations::lang.company_requests') . '</a>&nbsp;';
                    }

                    if ($is_admin || $can_edit_employee_company) {
                        $html .= '<button class="btn btn-xs btn-success  open-edition-modal" data-id="' . $row->id . '"><i class="glyphicon glyphicon-eye-edit"></i> ' . __('internationalrelations::lang.edit') . '</button>';
                    }

                    return $html;
                })


                ->rawColumns(['action', 'nationality'])
                ->make(true);
        }

        return view('internationalrelations::EmploymentCompanies.index')
            ->with(compact('countries', 'nationalities'));
    }




    public function companyRequests($id)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_company_requests = auth()->user()->can('internationalrelations.view_company_requests');
        if (!($is_admin || $can_view_company_requests)) {
        }

        $irDelegations = IrDelegation::where('agency_id', $id)->with(['transactionSellLine.service'])->get();

        return view('internationalrelations::EmploymentCompanies.companyRequests')->with(compact('irDelegations'));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_store_emoloyment_company = auth()->user()->can('internationalrelations.store_emoloyment_company');
        if (!($is_admin || $can_store_emoloyment_company)) {
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([

                'country',
                'nationalities',
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
            $input['multi_nationalities'] = json_encode($input['nationalities']);

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
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $employment_companies = Contact::where('type', 'recruitment')
            ->leftJoin('essentials_countries', 'contacts.country', '=', 'essentials_countries.id')
            ->select([
                'contacts.id',
                'contacts.supplier_business_name',
                'essentials_countries.id as country',
                'contacts.multi_nationalities',
                'contacts.name',
                'contacts.mobile',
                'contacts.email',
                'contacts.evaluation',
                'contacts.landline'

            ])
            ->find($id);

        $comp_country_name = null;
        if (!empty($employment_companies->country)) {
            $country = EssentialsCountry::findOrFail($employment_companies->country);
            $nameJson = $country->name;
            $nameArray = json_decode($nameJson, true);
            $comp_country_name = $nameArray['ar'];
        }

        $nationalities = null;

        if ($employment_companies->multi_nationalities != null) {
            $nationalityIds = json_decode($employment_companies->multi_nationalities);
            $nationalities = EssentialsCountry::whereIn('id', $nationalityIds)
                ->pluck('nationality', 'id');
        }





        return view('internationalrelations::EmploymentCompanies.employment_company_profile')
            ->with(compact('employment_companies', 'comp_country_name', 'nationalities'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($empCompanyId)
    {

        $employment_companies = Contact::where('type', 'recruitment')
            ->leftJoin('essentials_countries', 'contacts.country', '=', 'essentials_countries.id')
            ->select([
                'contacts.id',
                'contacts.supplier_business_name',
                'essentials_countries.id as country',
                'contacts.multi_nationalities',
                'contacts.name',
                'contacts.mobile',
                'contacts.email',
                'contacts.evaluation',
                'contacts.landline'

            ])
            ->find($empCompanyId);


        return response()->json(['data' => compact('employment_companies',)]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $empCompanyId)
    {
        try {
            $input = $request->only([
                'Office_name',
                'country',
                'nationalities',
                'name',
                'mobile',
                'email',
                'evaluation',
                'landline'
            ]);

            $multi_nationalitiesData = $request->input('nationalities');

            $input2['supplier_business_name'] = $input['Office_name'];
            $input2['name'] = $input['name'];
            $input2['country'] = $input['country'];
            $input2['mobile'] = $input['mobile'];
            $input2['evaluation'] = $input['evaluation'];
            $input2['landline'] = $input['landline'];
            $input2['multi_nationalities'] =  json_encode($multi_nationalitiesData);


            Contact::where('id', $empCompanyId)
                ->update($input2);

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


        return response()->json($output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
    }
}
