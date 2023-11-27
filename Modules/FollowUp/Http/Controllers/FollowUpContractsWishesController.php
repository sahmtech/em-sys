<?php

namespace Modules\FollowUp\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Contact;
use App\Transaction;
use App\User;
use DataTables;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentailsReasonWish;
class FollowUpContractsWishesController extends Controller
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
     
         if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
             abort(403, 'Unauthorized action.');
         }
     
         $can_crud_projects = auth()->user()->can('followup.crud_projects');
         if (!$can_crud_projects) {
             abort(403, 'Unauthorized action.');
         }
     
         $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
         $contacts = Contact::where('type', 'customer')->pluck('supplier_business_name', 'id');
     
         $workers = User::join('contacts', 'users.assigned_to', '=', 'contacts.id')
             ->leftjoin('essentials_employees_contracts','essentials_employees_contracts.employee_id','users.id')
             ->where('users.user_type', 'worker')
             ->select(
                 'users.id',
                 'users.emp_number as emp_number',
                 'users.first_name',
                 'users.mid_name',
                 'users.last_name',
                 'users.id_proof_number as residency',
                 'contacts.supplier_business_name as project_name',
                 'essentials_employees_contracts.contract_start_date as contract_start_date',
                 'essentials_employees_contracts.contract_end_date as contract_end_date',
                 'essentials_employees_contracts.wish_id as wish',
             );
             
      
         if (request()->ajax()) {
             return DataTables::of($workers)
                 ->addColumn('name', function ($row) {
                     return $row->first_name . ' ' . $row->mid_name . ' ' . $row->last_name;
                 })
                
                 ->editColumn('action', function ($row) {
                    $button = '<a href="#" class="btn btn-xs btn-success change-status-btn" data-toggle="modal"
                                   data-target="#change_status_modal" data-employee-id="'.$row->id.'"  data-wish="'.$row->wish.'">
                                   ' . __('followup::lang.change_wish') . '
                               </a>';
                    return $button;
                })
                
                 ->filterColumn('name', function ($query, $keyword) {
                     $query->where('name', 'like', "%{$keyword}%");
                 })
                 ->rawColumns(['action'])
                 ->make(true);
         }

         $wishes=EssentailsReasonWish::where('type','wish')->pluck('reason', 'id');
      
         return view('followup::contracts_wishes.index', compact('contacts', 'wishes'));
     }
     

     public function changeWish(Request $request)
     {
         try {
             // Use $request->input('employee_id') to get the value of the 'employee_id' field
             $employeeId = $request->input('employee_id');
             $wish = $request->input('wish');
     
             // Assuming you have a model for EssentialsEmployeesContract
             $empContract = EssentialsEmployeesContract::find($employeeId);
     
             if ($empContract) {
                 $empContract->update(['wish_id' => $wish]);
     
                 $output = [
                     'success' => true,
                     'msg' => 'Wish updated successfully',
                 ];
             } else {
                 $output = [
                     'success' => false,
                     'msg' => 'Contract not found for the specified employee ID',
                 ];
             }
         } catch (\Exception $e) {
             \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
     
             $output = [
                 'success' => false,
                 'msg' => $e->getMessage(),
             ];
         }
     
         return response()->json($output);
     }
     
    
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('followup::create');
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
        return view('followup::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('followup::edit');
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
