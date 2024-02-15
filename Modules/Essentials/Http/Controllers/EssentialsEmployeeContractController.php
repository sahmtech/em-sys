<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleCompany;
use App\AccessRoleProject;
use App\Business;
use App\Company;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Sales\Entities\SalesProject;

class EssentialsEmployeeContractController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index(Request $request)
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
            ->whereIn('u.id', $userIds)->where('u.status', '!=', 'inactive')
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
                'essentials_employees_contracts.is_active',
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
            ])->where( 'essentials_employees_contracts.is_active', 1)
            ->orderby('id', 'desc');

       // dd( $employees_contracts->where('employee_id',5385)->get());
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

            return Datatables::of($employees_contracts)
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


        return view('essentials::employee_affairs.employees_contracts.index')->with(compact('users', 'contract_types'));
    }


    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only([
                'employee',
                'contract_type',
                'contract_start_date',
                'contract_end_date',
                'contract_duration',
                'contract_duration_unit',
                'probation_period',
                'status',
                'is_renewable',
                'file'
            ]);


            $input2['employee_id'] = $input['employee'];



            $input2['contract_start_date'] = $input['contract_start_date'];


            $input2['contract_end_date'] = $input['contract_end_date'];

            $start_date = Carbon::parse($input['contract_start_date']);
            $end_date = Carbon::parse($input['contract_end_date']);


            // $contract_duration = $start_date->diffInDays($end_date);

            $input2['contract_duration'] = $input['contract_duration'];
            $input2['contract_per_period'] = $input['contract_duration_unit'];


            $input2['probation_period'] = $input['probation_period'];


            $input2['contract_type_id'] = $input['contract_type'];



            $input2['is_renewable'] = $input['is_renewable'];

            $latestRecord = EssentialsEmployeesContract::orderBy('contract_number', 'desc')->first();
            if ($latestRecord) {
                $latestRefNo = $latestRecord->contract_number;
                $numericPart = (int)substr($latestRefNo, 3);
                $numericPart++;
                $input2['contract_number'] = 'EC' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            } else {

                $input2['contract_number'] = 'EC0001';
            }
            if (request()->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('/employee_contracts');


                $input2['file_path'] = $filePath;
            }

            EssentialsEmployeesContract::where('employee_id', $input['employee'])->update(['is_active' => 0]);
            $contract = EssentialsEmployeesContract::create($input2);
            // dd( $contract );

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

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        // return  $output ;
        return redirect()->route('employeeContracts')->with(compact('users'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsEmployeesContract::where('id', $id)
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
