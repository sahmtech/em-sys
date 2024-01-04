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
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');

        $can_crud_employment_companies = auth()->user()->can('internationalrelations.crud_employment_companies');
        if (!($isSuperAdmin || $can_crud_employment_companies)) {
           //temp  abort(403, 'Unauthorized action.');
        }
      
        $is_admin = $this->moduleUtil->is_admin(auth()->user());


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

                  $html =  '<a href="' . route('companyRequests', ['id' => $row->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-eye-open"></i> ' . __('internationalrelations::lang.company_requests') . '</a>';
                    return $html;
                })



                ->rawColumns(['action'])
                ->make(true);
        }

        return view('internationalrelations::EmploymentCompanies.index')->with(compact('countries', 'nationalities'));
    }
   

    

    public function companyRequests($id)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');

        $can_view_company_requests = auth()->user()->can('internationalrelations.view_company_requests');
        if (!($isSuperAdmin || $can_view_company_requests)) {
           //temp  abort(403, 'Unauthorized action.');
        }
    
        $irDelegations = IrDelegation::where('agency_id',$id)->with(['transactionSellLine.service'])->get();
     

        
        return view('internationalrelations::EmploymentCompanies.companyRequests')->with(compact('irDelegations'));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $isSuperAdmin = User::where('id', auth()->user()->id)->first()->user_type == 'superadmin';

        $business_id = request()->session()->get('user.business_id');

        $can_store_emoloyment_company = auth()->user()->can('internationalrelations.store_emoloyment_company');
        if (!($isSuperAdmin || $can_store_emoloyment_company)) {
           //temp  abort(403, 'Unauthorized action.');
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
