<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Company;
use App\Contact;
use Illuminate\Support\Facades\Auth;
use Modules\Essentials\Entities\EssentialsCompaniesInsurancesContract;

class EssentialCompaniesInsuranceContractsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_add_companies_insurance_contracts = auth()->user()->can('essentials.add_companies_insurance_contracts');
        $can_edit_insurance_companies_contracts = auth()->user()->can('essentials.edit_companies_insurance_contracts');
        $can_delete_insurance_companies_contracts = auth()->user()->can('essentials.delete_insurance_companies_contracts');

        $companies = Company::where('business_id', $business_id)->pluck('name', 'id');
        $insurance_companies = Contact::where('type', '=', 'insurance')
            ->pluck('supplier_business_name', 'id');


        $insuranceCompaniesContracts = Company::where('business_id', $business_id)
            ->with(['essentialsCompaniesInsurancesContract'])
            ->select('companies.*');

        if (!empty(request()->input('insurance_company_filter')) && request()->input('insurance_company_filter') != 'all') {
            $insuranceCompaniesContracts = $insuranceCompaniesContracts
                ->whereHas('essentialsCompaniesInsurancesContract', function ($query) {
                    $query->where('insur_id', request()->input('insurance_company_filter'));
                });
        }

        // if (!empty(request()->start_date) && !empty(request()->end_date)) {
        //     $start = request()->start_date;
        //     $end = request()->end_date;
        //     $insuranceCompaniesContracts->whereDate('insurance_end_date', '>=', $start)
        //         ->whereDate('insurance_end_date', '<=', $end);
        // }

        if (request()->ajax()) {


            return Datatables::of($insuranceCompaniesContracts)
                ->addColumn('company_id', function ($row) use ($companies) {
                    $companyId = $row->id;
                    $companyName = $companies[$companyId] ?? '';
                    return $companyName;
                })

                ->addColumn('insur_id', function ($row) use ($insurance_companies) {
                    if ($row->essentialsCompaniesInsurancesContract->first() != null) {
                        $item = $insurance_companies[$row->essentialsCompaniesInsurancesContract->first()->insur_id] ?? '';
                    } else {
                        $item = '';
                    }

                    return $item;
                })

                ->addColumn('insurance_start_date', function ($row) {
                    if ($row->essentialsCompaniesInsurancesContract != null) {
                        $item = $row->essentialsCompaniesInsurancesContract?->first()->insurance_start_date ?? '';
                    } else {
                        $item = '';
                    }
                    return $item;
                })

                ->addColumn('insurance_end_date', function ($row) {
                    if ($row->essentialsCompaniesInsurancesContract != null) {
                        $item = $row->essentialsCompaniesInsurancesContract?->first()->insurance_end_date ?? '';
                    } else {
                        $item = '';
                    }
                    return $item;
                })


                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_insurance_companies_contracts, $can_delete_insurance_companies_contracts) {
                        $html = '';

                        if ($is_admin || $can_edit_insurance_companies_contracts) {

                            // $editUrl = url('medicalInsurance/insurance_companies_contracts/edit/' . $row->first()->essentialsCompaniesInsurancesContract->first()->id . '/' . $row->id);

                            $editUrl = url('medicalInsurance/insurance_companies_contracts/edit/' . $row->id);

                            $html .= '<a href="' . $editUrl . '"
                                        data-href="' . $editUrl . '"
                                        class="btn btn-xs btn-modal btn-info"  
                                        data-container="#editinsuranceCompaniesContracts"
                                        >
                                        <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '
                                    </a>&nbsp;';
                        }
                        if ($is_admin  || $can_delete_insurance_companies_contracts) {
                            $html .= '<button class="btn btn-xs btn-danger delete_companies_insurance_contract_button" data-href="' . route('insurance_companies_contracts.delete', ['id' =>  $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )


                ->filterColumn('insurance_start_date', function ($query, $keyword) {
                    $query->whereHas('essentialsCompaniesInsurancesContract', function ($q) use ($keyword) {
                        $q->whereDate('insurance_start_date', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('insurance_end_date', function ($query, $keyword) {
                    $query->whereHas('essentialsCompaniesInsurancesContract', function ($q) use ($keyword) {
                        $q->whereDate('insurance_end_date', 'like', '%' . $keyword . '%');
                    });
                })

                ->rawColumns(['action'])
                ->make(true);
        }
        return view('essentials::companies_insurances_contracts.index')
            ->with(compact('insurance_companies', 'companies'));
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
        //
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
        $company_insurance = null;
        $companies = Company::where('business_id', $business_id)->pluck('name', 'id');
        $insurance_companies = Contact::where('type', '=', 'insurance')
            ->pluck('supplier_business_name', 'id');

        $prev_comp_id = EssentialsCompaniesInsurancesContract::where('company_id', $id)->first();
        if ($prev_comp_id == null) {
            $new_company_insurance = EssentialsCompaniesInsurancesContract::create(['company_id' => $id]);
            $company_insurance = $new_company_insurance;
        } else {
            $company_insurance = $prev_comp_id;
        }

        return view('essentials::companies_insurances_contracts.edit')
            ->with(compact('insurance_companies', 'companies', 'company_insurance'));
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
            $input = $request->only([
                'company_id',
                'insurance_company',
                'insurance_start_date',
                'insurance_end_date'
            ]);


            // $insurance_companies_contract_data['company_id'] =  $input['company_id'];

            $insurance_companies_contract_data['insurance_start_date'] = $input['insurance_start_date'];
            $insurance_companies_contract_data['insurance_end_date'] =  $input['insurance_end_date'];
            $insurance_companies_contract_data['insur_id'] = $input['insurance_company'];
            $insurance_companies_contract_data['updated_by'] =  Auth::user()->id;


            if (!$input['company_id']) {

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
            EssentialsCompaniesInsurancesContract::where('company_id', $input['company_id'])
                ->update($insurance_companies_contract_data);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $companies = Company::where('business_id', $business_id)->pluck('name', 'id');
        $insurance_companies = Contact::where('type', '=', 'insurance')
            ->pluck('supplier_business_name', 'id');



        return redirect()->route('get_companies_insurance_contracts')
            ->with(compact('insurance_companies', 'companies'));
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
            $companyInsuranceContract = EssentialsCompaniesInsurancesContract::where('company_id', $id)->first();

            if (!$companyInsuranceContract) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.does_not_add_insurance_company'),
                ];;
            } else {
                $companyInsuranceContract->update([
                    'insur_id' => null,
                    'deleted_by' => Auth::user()->id,
                    'deleted_at' => \Carbon::now(),
                    'insurance_start_date' => null,
                    'insurance_end_date' => null,
                ]);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            }
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