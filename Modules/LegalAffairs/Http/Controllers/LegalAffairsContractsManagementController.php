<?php

namespace Modules\LegalAffairs\Http\Controllers;

use App\Contact;
use App\Transaction;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\salesContractItem;

class LegalAffairsContractsManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('legalaffairs::index');
    }

    public function employeeContracts(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_crud_employee_contracts = auth()->user()->can('essentials.crud_employee_contracts');
        if (!$can_crud_employee_contracts) {
            //temp  abort(403, 'Unauthorized action.');
        }


        $can_crud_employee_contracts = auth()->user()->can('essentials.crud_employee_contracts');
        $can_add_employee_contracts = auth()->user()->can('essentials.add_employee_contracts');
        $can_show_employee_contracts = auth()->user()->can('essentials.show_employee_contracts');
        $can_delete_employee_contracts = auth()->user()->can('essentials.delete_employee_contracts');


        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }




        $employees_contracts = EssentialsEmployeesContract::join('users as u', 'u.id', '=', 'essentials_employees_contracts.employee_id')
            ->whereIn('u.id', $userIds)
            ->select([
                'essentials_employees_contracts.id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'essentials_employees_contracts.contract_number',
                'essentials_employees_contracts.contract_start_date',
                'essentials_employees_contracts.contract_end_date',
                'essentials_employees_contracts.contract_duration',
                'essentials_employees_contracts.contract_per_period',
                'essentials_employees_contracts.probation_period',
                'essentials_employees_contracts.contract_type_id',
                'essentials_employees_contracts.is_renewable',
                'essentials_employees_contracts.file_path',
                DB::raw("
                CASE 
                    WHEN essentials_employees_contracts.contract_end_date IS NULL THEN NULL
                    WHEN essentials_employees_contracts.contract_start_date IS NULL THEN NULL
                    WHEN DATE(essentials_employees_contracts.contract_end_date) <= CURDATE() THEN 'canceled'
                    WHEN DATE(essentials_employees_contracts.contract_end_date) > CURDATE() THEN 'valid'
                    ELSE 'Null'
                END as status
            "),
            ])->orderby('id', 'desc');


        if (!empty(request()->input('contract_type')) && request()->input('contract_type') !== 'all') {
            $employees_contracts->where('essentials_employees_contracts.contract_type_id', request()->input('contract_type'));
        }
        if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
            $employees_contracts->where('essentials_employees_contracts.status', request()->input('status'));
        }
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end = request()->end_date;
            $employees_contracts->whereDate('essentials_employees_contracts.contract_end_date', '>=', $start)
                ->whereDate('essentials_employees_contracts.contract_end_date', '<=', $end);
        }





        $contract_types = EssentialsContractType::pluck('type', 'id')->all();
        if (request()->ajax()) {

            return DataTables::of($employees_contracts)
                ->editColumn('contract_type_id', function ($row) use ($contract_types) {
                    $item = $contract_types[$row->contract_type_id] ?? '';

                    return $item;
                })

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_show_employee_contracts, $can_delete_employee_contracts) {
                        $html = '';

                        if ($is_admin || $can_show_employee_contracts) {
                            if (!empty($row->file_path)) {
                                $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->file_path . '\'"><i class="fa fa-eye"></i> ' . __('essentials::lang.contract_view') . '</button>';
                                '&nbsp;';
                            } else {
                                $html .= '<span class="text-warning">' . __('sales::lang.no_file_to_show') . '</span>';
                            }
                        }

                        if ($is_admin || $can_delete_employee_contracts) {
                            $html .= ' &nbsp; <button class="btn btn-xs btn-danger delete_employeeContract_button" data-href="' . route('employeeContract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }




                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('file_path')
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as 
         full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return view('legalaffairs::contracts_managements.employees_contracts_index')->with(compact('users', 'contract_types'));
    }

    public function salesContracts()
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_print_sales_contracts = auth()->user()->can('sales.print_sales_contracts');
        $can_view_sales_contracts_file = auth()->user()->can('sales.view_sales_contracts_file');
        $can_delete_sale_contract = auth()->user()->can('sales.delete_sale_contract');


        $contacts = Contact::all()->pluck('supplier_business_name', 'id');
        $offer_prices = Transaction::where([['transactions.type', '=', 'sell'], ['transactions.status', '=', 'approved']])
            ->leftJoin('sales_contracts', 'transactions.id', '=', 'sales_contracts.offer_price_id')
            ->whereNull('sales_contracts.offer_price_id')->pluck('transactions.ref_no', 'transactions.id');
        $items = salesContractItem::pluck('name_of_item', 'id');
        if (request()->ajax()) {


            $contracts = salesContract::join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')->select([
                'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file', 'sales_contracts.contract_duration',
                'sales_contracts.contract_per_period',
                'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
            ]);

            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $contracts->where('sales_contracts.status', request()->input('status'));
            }
            if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                $contracts->where('transactions.contract_form', request()->input('contract_form'));
            }

            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->contact_id] ?? '';

                    return $item;
                })


                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_print_sales_contracts, $can_view_sales_contracts_file, $can_delete_sale_contract) {
                        $html = '';
                        //  $html .=  '  <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\ContractsController::class, 'showOfferPrice'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __('sales::lang.offer_price_view') . '</a>';
                        if ($is_admin || $can_print_sales_contracts) {
                            $html .= '  <a href="#" data-href="' . route('download.contract', ['id' => $row->id]) . '" class="btn btn-xs btn-success btn-download">
                        <i class="fas fa-download" aria-hidden="true"></i>   ' . __('messages.print') . '   </a>';
                            $html .= '&nbsp;';
                        }
                        if ($is_admin || $can_view_sales_contracts_file) {
                            if (!empty($row->file)) {
                                $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->file . '\'"><i class="fa fa-eye"></i> ' . __('sales::lang.contract_view') . '</button>';
                            } else {
                                $html .= '<span class="text-warning">' . __('sales::lang.no_file_to_show') . '</span>';
                            }
                            $html .= '&nbsp;';
                        }
                        if ($is_admin || $can_delete_sale_contract) {
                            $html .= '<button class="btn btn-xs btn-danger delete_contract_button" data-href="' . route('contract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }
                        return $html;
                    }
                )



                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $contracts = DB::table('sales_contracts')

            ->select('sales_contracts.number_of_contract as contract_number', 'sales_contracts.id')
            ->get();


        return view('legalaffairs::contracts_managements.sales_contracts_index')->with(compact('offer_prices', 'items', 'users', 'contracts'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('legalaffairs::create');
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
        return view('legalaffairs::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('legalaffairs::edit');
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
