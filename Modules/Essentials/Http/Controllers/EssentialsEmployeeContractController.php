<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesContract;

class EssentialsEmployeeContractController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    
    public function index()
    {
       
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            $employees_contracts = EssentialsEmployeesContract::
                join('users as u', 'u.id', '=', 'essentials_employees_contracts.employee_id')
                ->select([
                    'essentials_employees_contracts.id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'essentials_employees_contracts.contract_number',
                    'essentials_employees_contracts.contract_start_date',
                    'essentials_employees_contracts.contract_end_date',
                    'essentials_employees_contracts.contract_duration',
                    'essentials_employees_contracts.probation_period',
                    'essentials_employees_contracts.status',
                    'essentials_employees_contracts.is_renewable',
                    'essentials_employees_contracts.file_path',


                ]);


            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $employees_contracts->where('essentials_employees_contracts.status', request()->input('status'));
            }
            
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $employees_contracts->whereDate('essentials_employees_contracts.contract_end_date', '>=', $start)
                    ->whereDate('essentials_employees_contracts.contract_end_date', '<=', $end);
            }
           
            return Datatables::of($employees_contracts)
            ->addColumn(
                'action',
                 function ($row) {
                    $html = ''; 
                //    $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/'.$row->file_path.'\'"><i class="fa fa-eye"></i> ' . __('essentials::lang.contract_view') . '</button>';
                    '&nbsp;';
                 

                //    $html .= '<a  href="'. route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                    $html .= '<button class="btn btn-xs btn-danger delete_employeeContract_button" data-href="' . route('employeeContract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    
                    return $html;
                 }
                )
            
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('file_path')
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
                $query = User::where('business_id', $business_id)->user();
                $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
                $users = $all_users->pluck('full_name', 'id');
               
        
        return view('essentials::employee_affairs.employees_contracts.index')->with(compact('users'));
    }
   

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }
 
        try {
            $input = $request->only(['employee', 'contract_number', 'contract_start_date', 'contract_end_date', 'contract_duration', 'probation_period','status','is_renewable','file']);
          
            $input2['employee_id'] = $input['employee'];
            $input2['contract_number'] = $input['contract_number'];
            $input2['contract_start_date'] = $input['contract_start_date'];
            $input2['contract_end_date'] = $input['contract_end_date'];
            $input2['contract_duration'] = $input['contract_duration'];
            $input2['probation_period'] = $input['probation_period'];
            $input2['status'] = $input['status'];
            $input2['is_renewable'] = $input['is_renewable'];
        
            $file = request()->file('file');
            $filePath = $file->store('/employeeContracts');
            
            $input2['file_path'] = $filePath;
       
            EssentialsEmployeesContract::create($input2);
            
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $query = User::where('business_id', $business_id)
        ->user();
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        
    
       return redirect()->route('employeeContracts')->with(compact('users'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsEmployeesContract::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       
       return $output;

    }
}
