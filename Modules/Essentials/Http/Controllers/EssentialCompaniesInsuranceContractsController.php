<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Company;
use App\Contact;
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
        $can_edit_insurance_companies_contracts =auth()->user()->can('essentials.edit_companies_insurance_contracts');
        $can_delete_insurance_companies_contracts =auth()->user()->can('essentials.delete_insurance_companies_contracts');
        
        $companies = Company::where('business_id',$business_id)->pluck('name', 'id');
        $insurance_companies = Contact::where('type','=','insurance')
        ->pluck('supplier_business_name', 'id');

       

        $insuranceCompaniesContracts = EssentialsCompaniesInsurancesContract::with(['company', 'insurance'])
        ->select([
             'id',
            'company_id',
            'insur_id',
            'insurance_start_date',
            'insurance_end_date',
        ]);
    
       // Filter by insurance company
if (!empty(request()->input('insurance_company_filter')) && request()->input('insurance_company_filter') != 'all') {
    $insuranceCompaniesContracts = $insuranceCompaniesContracts->where('insur_id', request()->input('insurance_company_filter'));
}

// Filter by date range
if (!empty(request()->start_date) && !empty(request()->end_date)) {
    $start = request()->start_date;
    $end = request()->end_date;
    $insuranceCompaniesContracts->whereDate('insurance_end_date', '>=', $start)
        ->whereDate('insurance_end_date', '<=', $end);
}

        if (request()->ajax()) {
           

            return Datatables::of($insuranceCompaniesContracts)
            ->editColumn('insur_id', function ($row) use ($insurance_companies) {
                $item = $insurance_companies[$row->insur_id] ?? '';
                return $item;
            })
          
              
                ->addColumn(
                    'action',
                    function ($row) use( $is_admin ,$can_edit_insurance_companies_contracts ,$can_delete_insurance_companies_contracts) {
                        $html = '';
                        
                        if ($is_admin || $can_edit_insurance_companies_contracts) {
                            
                            $editUrl = url('medicalInsurance/insurance_companies_contracts/edit/' . $row->id . '/' . $row->company_id);
                
                            $html .= '<a href="' . $editUrl . '"
                                        data-href="' . $editUrl . '"
                                        class="btn btn-xs btn-modal btn-info"  
                                        data-container="#editinsuranceCompaniesContracts"
                                        data-company-id="' . $row->company_id . '">
                                        <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '
                                    </a>&nbsp;';
                        }
                       if($is_admin  || $can_delete_insurance_companies_contracts){
                         $html .= '<button class="btn btn-xs btn-danger delete_companies_insurance_contract_button" data-href="' . route('insurance_companies_contracts.delete', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }
                      
                        return $html;
                    }
                )
              
               
                ->rawColumns([ 'action'])
                ->make(true);
        }
        return view('essentials::companies_insurances_contracts.index')
        ->with(compact('insurance_companies','companies'));
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
    public function edit($id ,$comp_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
         
        $companies = Company::where('business_id',$business_id)->pluck('name', 'id');
        $insurance_companies = Contact::where('type','=','insurance')
        ->pluck('supplier_business_name', 'id');

        $company_insurance = EssentialsCompaniesInsurancesContract::findOrFail($id);
       
        return view('essentials::companies_insurances_contracts.edit')
        ->with(compact('insurance_companies','companies','company_insurance' ,'comp_id'));
     
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {  $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

     
        try {
            $input = $request->only(['company_id',
              'insurance_company',
              'insurance_start_date',
               'insurance_end_date']);
          
        
           // $insurance_companies_contract_data['company_id'] =  $input['company_id'];

            $insurance_companies_contract_data['insurance_start_date'] = $input['insurance_start_date'];
            $insurance_companies_contract_data['insurance_end_date'] =  $input['insurance_end_date'];
            $insurance_companies_contract_data['insur_id'] = $input['insurance_company'];
          
            if(!$input['company_id'])
            {

                $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            }
            EssentialsCompaniesInsurancesContract::where('id', $id)
            ->where('company_id',$input['company_id'])
            ->update($insurance_companies_contract_data);


            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
      
        $companies = Company::where('business_id',$business_id)->pluck('name', 'id');
        $insurance_companies = Contact::where('type','=','insurance')
        ->pluck('supplier_business_name', 'id');


       
        return redirect()->route('get_companies_insurance_contracts')
        ->with(compact('insurance_companies','companies'));
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
            $companyInsuranceContract = EssentialsCompaniesInsurancesContract::find($id);
    
            if (!$companyInsuranceContract) {
                return response()->json(['success' => false, 'msg' => __('messages.something_went_wrong')]);
            }
    
            // Update multiple columns
            $companyInsuranceContract->update([
                'insur_id' => null,
                'insurance_start_date' => null,
                'insurance_end_date' => null,
            ]);
    
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
