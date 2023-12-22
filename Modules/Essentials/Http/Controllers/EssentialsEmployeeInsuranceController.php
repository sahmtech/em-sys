<?php

namespace Modules\Essentials\Http\Controllers;

use App\Contact;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsInsuranceCompany;

class EssentialsEmployeeInsuranceController extends Controller
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


        $can_crud_employees_insurances = auth()->user()->can('essentials.crud_employees_insurances');
        if (!$can_crud_employees_insurances) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $insurance_companies = Contact::where('type', 'insurance')->pluck('supplier_business_name', 'id');
        $insurance_classes = EssentialsInsuranceClass::all()->pluck('name', 'id');

        if (request()->ajax()) {


            $insurances = EssentialsEmployeesInsurance::join('users as u', 'u.id', '=', 'essentials_employees_insurances.employee_id')->select([
                    'essentials_employees_insurances.id as id',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'essentials_employees_insurances.insurance_classes_id as insurance_classes_id',
                    'essentials_employees_insurances.insurance_company_id as insurance_company_id',
                    'essentials_employees_insurances.status as status'
                ]);



            return Datatables::of($insurances)
                ->editColumn('insurance_company_id', function ($row) use ($insurance_companies) {
                    $item = $insurance_companies[$row->insurance_company_id] ?? '';

                    return $item;
                })
                ->editColumn('insurance_classes_id', function ($row) use ($insurance_classes) {
                    $item = $insurance_classes[$row->insurance_classes_id] ?? '';

                    return $item;
                })
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        //$html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>&nbsp;';
                        //$html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>&nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_insurance_button" data-href="' . route('employee_insurance.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';


                        return $html;
                    }
                )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', "LIKE", "%{$keyword}%");
                })
                ->removeColumn('id')
                ->removeColumn('status')
                ->rawColumns(['action'])
                ->make(true);
        }
        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        return view('essentials::employee_affairs.employee_insurance.index')->with(compact('insurance_companies', 'insurance_classes', 'users'));
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


        $insurance_companies = Contact::where('type', 'insurance')->pluck('id');

        try {

            $input = $request->only(['insurance_class', 'employee']);


            $insurance_data['insurance_classes_id'] = $input['insurance_class'];
            $insurance_data['employee_id'] = $input['employee'];
            $business = User::find($input['employee'])->business_id;
            $insurance_data['insurance_company_id'] = Contact::where('type', 'insurance')->where('business_id', $business)->first()->id;
            // dd(  $insurance_data );


            EssentialsEmployeesInsurance::create($insurance_data);
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

        return redirect()->route('employee_insurance')->with('status', $output);
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function fetchClasses(Request $request)
    {

        $employee_id = $request->input('employee_id');
        $employee = User::find($employee_id)->business_id;



        $insurance_company = Contact::where('type', 'insurance')->where('business_id', $employee)->first()->id;


        $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company)->pluck('name', 'id');

        return response()->json($classes);
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



        try {
            EssentialsEmployeesInsurance
                ::where('id', $id)
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
