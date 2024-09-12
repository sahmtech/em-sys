<?php

namespace Modules\Essentials\Http\Controllers;

use App\Company;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use App\Contact;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Essentials\Entities\EssentialsCompaniesInsurancesContract;
use Modules\Essentials\Entities\EssentialsInsuranceContract;

class EssentialsInsuranceContractController extends Controller
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


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_add_insurance_contracts = auth()->user()->can('essentials.add_insurance_contracts');
        $can_edit_insurance_contracts = auth()->user()->can('essentials.edit_insurance_contracts');
        $can_delete_insurance_contracts = auth()->user()->can('essentials.delete_insurance_contracts');
        // if (!$can_crud_insurance_companies) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }

        $insuramce_companies = Contact::where([['business_id', '=', $business_id], ['type', '=', 'insurance']])->pluck('supplier_business_name', 'id',);
        $insuranceContracts = DB::table('essentials_insurance_contracts')->select([
            'id',
            'insurance_company_id',
            'insurance_start_date',
            'insurance_end_date',
            'policy_number',
            'is_active'

        ]);
        $companies = Company::pluck('name', 'id');
        $essentialsCompaniesInsurancesContracts = EssentialsCompaniesInsurancesContract::select('company_id', 'insur_id')->get();;
        if (request()->ajax()) {



            if (!empty(request()->input('insurance_company_filter')) && request()->input('insurance_company_filter') != 'all') {
                $contract = Contact::where([['business_id', $business_id], ['supplier_business_name', request()->input('insurance_company_filter')]])->first();
                $id = 0;
                if ($contract) {
                    $id = $contract->id;
                }
                $insuranceContracts->where('insurance_company_id', $id);
            }
            if (!empty(request()->input('insurance_policy_number_filter')) && request()->input('insurance_policy_number_filter') != 'all') {
                $insuranceContracts->where('policy_number', request()->input('insurance_policy_number_filter'));
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $insuranceContracts->whereDate('insurance_end_date', '>=', $start)
                    ->whereDate('insurance_end_date', '<=', $end);
            }

            return Datatables::of($insuranceContracts)
                ->editColumn('insurance_company_id', function ($row) use ($insuramce_companies) {

                    $item = $insuramce_companies[$row->insurance_company_id] ?? '';
                    return $item;
                })
                ->addColumn('company', function ($row) use ($essentialsCompaniesInsurancesContracts,  $companies) {
                    $htlm = '<ul>';
                    foreach ($essentialsCompaniesInsurancesContracts as $essentialsCompaniesInsurancesContract) {
                        if ($essentialsCompaniesInsurancesContract->insur_id == $row->insurance_company_id) {

                            $htlm .= '<li>' .  $companies[$essentialsCompaniesInsurancesContract->company_id] . '</li>';
                        }
                    }

                    $htlm .= '</ul>';
                    return  $htlm;
                })

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_insurance_contracts,  $can_delete_insurance_contracts, $essentialsCompaniesInsurancesContracts) {
                        $related_companies = [];
                        foreach ($essentialsCompaniesInsurancesContracts as $essentialsCompaniesInsurancesContract) {
                            if ($essentialsCompaniesInsurancesContract->insur_id == $row->insurance_company_id) {
                                $related_companies[] = $essentialsCompaniesInsurancesContract->company_id;
                            }
                        }
                        $companies_json = json_encode($related_companies);
                        $html = '';
                        //$html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>&nbsp;';
                        if ($is_admin || $can_edit_insurance_contracts) {
                            $html .= '<button data-toggle="modal" data-target="#editInsuranceContractModal" 
                                    class="btn btn-xs btn-primary edit_button" 
                                    data-id="' . $row->id . '" 
                                    data-insurance_company="' . $row->insurance_company_id . '" 
                                    data-policy_number="' . $row->policy_number . '" 
                                    data-start_date="' . $row->insurance_start_date . '" 
                                    data-end_date="' . $row->insurance_end_date . '" 
                                    data-companies=\'' . $companies_json . '\'>' .
                                '<i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>&nbsp;';
                        }

                        if ($is_admin || $can_delete_insurance_contracts) {
                            $html .= '<button class="btn btn-xs btn-danger delete_insurance_contract_button" data-href="' . route('insurance_contracts.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                // ->filterColumn('supplier_business_name', function ($query, $keyword) {
                //     $query->where('supplier_business_name',"LIKE", "%{$keyword}%");
                // })
                ->removeColumn('id')
                ->rawColumns(['action', 'company'])
                ->make(true);
        }

        return view('essentials::insurance_contracts.index')
            ->with(compact('insuramce_companies', 'companies'));
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['insurance_company', 'policy_number', 'insurance_start_date', 'insurance_end_date']);

            $company_ids = $request->company_ids;

            foreach ($company_ids as   $company_id) {
                EssentialsCompaniesInsurancesContract::create([
                    'company_id' => $company_id,
                    'insur_id' => $input['insurance_company'],
                    'insurance_start_date' => $input['insurance_start_date'],
                    'insurance_end_date' => $input['insurance_end_date'],
                    'created_by' => auth()->user()->id,
                ]);
            }

            $insurance_contract_data['insurance_start_date'] = $input['insurance_start_date'];
            $insurance_contract_data['insurance_end_date'] =  $input['insurance_end_date'];
            $insurance_contract_data['insurance_company_id'] = $input['insurance_company'];
            $insurance_contract_data['policy_number'] =  $input['policy_number'];

            EssentialsInsuranceContract::create($insurance_contract_data);
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
        }

        //   return redirect()->route('insurance_contracts')->with('status', $output);
        $insuramce_companies = Contact::where([['business_id', '=', $business_id], ['type', '=', 'insurance']])->pluck('supplier_business_name', 'id',);
        return redirect()->route('insurance_contracts');
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

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $contract = EssentialsInsuranceContract::findOrFail($id);
        $insuramce_companies = Contact::where([['business_id', '=', $business_id], ['type', '=', 'insurance']])->pluck('supplier_business_name', 'id',);

        return view('essentials::insurance_contracts.edit')->with(compact('insuramce_companies', 'contract'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        error_log("asdasdas");
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        try {
            $input = $request->only(['insurance_company', 'policy_number', 'insurance_start_date', 'insurance_end_date']);


            $insurance_contract_data['insurance_start_date'] = $input['insurance_start_date'];
            $insurance_contract_data['insurance_end_date'] =  $input['insurance_end_date'];
            $insurance_contract_data['insurance_company_id'] = $input['insurance_company'];
            $insurance_contract_data['policy_number'] =  $input['policy_number'];

            EssentialsCompaniesInsurancesContract::where('insur_id', $input['insurance_company'])->delete();
            $company_ids = $request->company_ids;

            foreach ($company_ids as   $company_id) {
                EssentialsCompaniesInsurancesContract::create([
                    'company_id' => $company_id,
                    'insur_id' => $input['insurance_company'],
                    'insurance_start_date' => $input['insurance_start_date'],
                    'insurance_end_date' => $input['insurance_end_date'],
                    'created_by' => auth()->user()->id,
                ]);
            }

            EssentialsInsuranceContract::where('id', $id)->update($insurance_contract_data);
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
        $insuramce_companies = Contact::where([['business_id', '=', $business_id], ['type', '=', 'insurance']])->pluck('supplier_business_name', 'id',);
        return redirect()->route('insurance_contracts')->with(compact('insuramce_companies'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsInsuranceContract::where('id', $id)
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
