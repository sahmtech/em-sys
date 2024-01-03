<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Utils\EssentialsUtil;
use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;

class EssentialsAllowanceAndDeductionController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    protected $essentialsUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, EssentialsUtil $essentialsUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');



        if (!auth()->user()->can('essentials.add_allowance_and_deduction') && !auth()->user()->can('essentials.view_allowance_and_deduction')) {
           //temp  abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $allowances = EssentialsAllowanceAndDeduction::where('business_id', $business_id)
                ->with(['employees']);

            return Datatables::of($allowances)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        if (auth()->user()->can('essentials.add_allowance_and_deduction')) {
                            $html .= '<button data-href="' . action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'edit'], [$row->id]) . '" data-container="#add_allowance_deduction_modal" class="btn-modal btn btn-primary btn-xs"><i class="fa fa-edit" aria-hidden="true"></i> ' . __('messages.edit') . '</button>';

                            $html .= '&nbsp; <button data-href="' . action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'destroy'], [$row->id]) . '" class="delete-allowance btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                ->editColumn('applicable_date', function ($row) {
                    return $this->essentialsUtil->format_date($row->applicable_date);
                })
                ->editColumn('type', '{{__("essentials::lang." . $type)}}')
                ->editColumn('amount', '<span class="display_currency" data-currency_symbol="false">{{$amount}}</span> @if($amount_type =="percent") % @endif')
                ->editColumn('employees', function ($row) {
                    $employees = [];
                    foreach ($row->employees as $employee) {
                        $employees[] = $employee->user_full_name;
                    }

                    return implode(', ', $employees);
                })
                ->rawColumns(['action', 'amount'])
                ->make(true);
        }
    }
    public function featureIndex()
    {
        $business_id = request()->session()->get('user.business_id');
    
        if (!auth()->user()->can('essentials.view_allowance_and_deduction')) {
            //temp  abort(403, 'Unauthorized action.');
        }
    
        if (request()->ajax()) {
            $userAllowances = EssentialsUserAllowancesAndDeduction::join(
                'essentials_allowances_and_deductions as allowance',
                'allowance.id',
                '=',
                'essentials_user_allowance_and_deductions.allowance_deduction_id'
            )
                ->where('allowance.type', 'allowance')
                ->join('users as u', 'u.id', '=', 'essentials_user_allowance_and_deductions.user_id')
                ->where('u.business_id', $business_id)
                ->select([
                    'allowance.id as id',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'allowance.description',
                    'essentials_user_allowance_and_deductions.amount',
                ]);
    
            return Datatables::of($userAllowances)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        $html .= '<a href="' . route('employee_allowance.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                        &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_employee_allowance_button" data-href="' . route('employee_allowance.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
    
                        return $html;
                    }
                )
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('description', function ($query, $keyword) {
                    $query->where('allowance.description', 'like', "%{$keyword}%");
                })
                ->filterColumn('amount', function ($query, $keyword) {
                    $query->where('essentials_user_allowance_and_deductions.amount', 'like', "%{$keyword}%");
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    
        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as 
         full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();
    
        return view('essentials::employee_affairs.employee_features.index')->with(compact('allowance_types', 'users'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');



        $users = User::forDropdown($business_id, false);

        return view('essentials::allowance_deduction.create')->with(compact('users'));
    }


    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['description', 'type', 'amount', 'amount_type', 'applicable_date']);
            $input['business_id'] = $business_id;
            $input['amount'] = $this->moduleUtil->num_uf($input['amount']);
            $input['applicable_date'] = !empty($input['applicable_date']) ? $this->essentialsUtil->uf_date($input['applicable_date']) : null;
            $allowance = EssentialsAllowanceAndDeduction::create($input);
            $allowance->employees()->sync($request->input('employees'));

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage(),
            ];
        }

        return $output;
    }

    public function storeUserAllowance(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['employee', 'allowance', 'amount']);
            $input['user_id'] = $input['employee'];
            $input['allowance_deduction_id'] = $input['allowance'];
            $input['amount'] = $this->moduleUtil->num_uf($input['amount']);
            EssentialsUserAllowancesAndDeduction::create($input);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage(),
            ];
        }


        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();

        return redirect()->route('featureIndex')->with(compact('allowance_types', 'users'));
    }
    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        $business_id = request()->session()->get('user.business_id');



        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');



        $allowance = EssentialsAllowanceAndDeduction::where('business_id', $business_id)
            ->with('employees')
            ->findOrFail($id);
        $users = User::forDropdown($business_id, false);

        $selected_users = [];
        foreach ($allowance->employees as $employee) {
            $selected_users[] = $employee->id;
        }

        $applicable_date = !empty($allowance->applicable_date) ? $this->essentialsUtil->format_date($allowance->applicable_date) : null;

        return view('essentials::allowance_deduction.edit')
            ->with(compact('allowance', 'users', 'selected_users', 'applicable_date'));
    }
    public function editAllowance($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        $UserAllowance = EssentialsUserAllowancesAndDeduction::findOrFail($id);

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();

        return view('essentials::employee_affairs.employee_features.editAllowance')->with(compact('UserAllowance', 'users', 'allowance_types'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['description', 'type', 'amount', 'amount_type', 'applicable_date']);
            $input['amount'] = $this->moduleUtil->num_uf($input['amount']);
            $input['applicable_date'] = !empty($input['applicable_date']) ? $this->essentialsUtil->uf_date($input['applicable_date']) : null;
            $allowance = EssentialsAllowanceAndDeduction::findOrFail($id);
            $allowance->update($input);

            $allowance->employees()->sync($request->input('employees'));

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage(),
            ];
        }

        return $output;
    }
    public function updateAllowance(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            $input = $request->only(['employee', 'allowance', 'amount']);
            $input2['user_id'] = $input['employee'];
            $input2['allowance_deduction_id'] = $input['allowance'];
            $input2['amount'] = $this->moduleUtil->num_uf($input['amount']);

            EssentialsUserAllowancesAndDeduction::where('id', $id)->update($input2);
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

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();

        return redirect()->route('featureIndex')->with(compact('allowance_types', 'users'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');



        if (request()->ajax()) {
            try {
                EssentialsAllowanceAndDeduction::where('business_id', $business_id)
                    ->where('id', $id)
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

    public function destroyAllowance($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            EssentialsUserAllowancesAndDeduction::where('id', $id)
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
