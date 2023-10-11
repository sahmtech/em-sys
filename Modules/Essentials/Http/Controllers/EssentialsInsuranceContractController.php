<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use App\Contact;
use Modules\Essentials\Entities\EssentialsCity;
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

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_insurance_companies = auth()->user()->can('essentials.crud_insurance_companies');
        if (!$can_crud_insurance_companies) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $insuranceContracts = DB::table('essentials_insurance_contracts')->select([
                'id',
                'insurance_company_id',
                'employees_count',
                'dependents_count',
                'insurance_start_date',
                'insurance_end_date',
                'policy_number',
                'policy_value',
                'attachments',
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

            return Datatables::of($insuranceContracts)
           
            ->addColumn(
                'attachments2',
                function ($row) {
                    $html = '';
                    $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/'.$row->attachments.'\'"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>';

                    return $html;
                }
            )
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        //$html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>&nbsp;';
                        //$html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>&nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_insurance_contract_button" data-href="' . route('insurance_contracts.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';                    
                        return $html;
                    }
                )
                // ->filterColumn('supplier_business_name', function ($query, $keyword) {
                //     $query->where('supplier_business_name',"LIKE", "%{$keyword}%");
                // })
                ->removeColumn('id')
                ->removeColumn('attachments')
                ->rawColumns(['attachments2','action'])
                ->make(true);
        }
        $business_id = request()->session()->get('user.business_id');
        $insuramce_companies = Contact::where('business_id', $business_id)->pluck('supplier_business_name', 'id',);
        return view('essentials::insurance_contracts.index')->with(compact('insuramce_companies'));
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

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['insurance_company', 'policy_number','policy_value','insurance_employees_count','insurance_dependents_count','insurance_start_date','insurance_end_date','insurance_attachments']);
            $file = request()->file('insurance_attachments');
            $filePath = $file->store('/insuranceContracts');
            $insurance_contract_data['attachments'] = $filePath;
            $insurance_contract_data['employees_count'] =  $input['insurance_employees_count'];
            $insurance_contract_data['dependents_count'] = $input['insurance_dependents_count'];
            $insurance_contract_data['insurance_start_date'] = $input['insurance_start_date'];
            $insurance_contract_data['insurance_end_date'] =  $input['insurance_end_date'];
            $insurance_contract_data['insurance_company_id'] = $input['insurance_company'];
            $insurance_contract_data['policy_number'] =  $input['policy_number'];
            $insurance_contract_data['policy_value'] = $input['policy_value'];
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

        return redirect()->route('insurance_contracts')->with('status', $output);
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

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsInsuranceContract::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       
        return $output;
    }
}
