<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EssentialsEmployeeFamilyController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');


        $crud_employee_families = auth()->user()->can('essentials.crud_employee_families');
        if (!$crud_employee_families) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $EssentialsEmployeesFamilies = EssentialsEmployeesFamily::join('users as u', 'u.id', '=', 'essentials_employees_families.employee_id')->where('u.business_id', $business_id)

                ->select([
                    'essentials_employees_families.id',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    DB::raw("CONCAT(COALESCE(essentials_employees_families.first_name, ''), ' ', COALESCE(essentials_employees_families.last_name, '')) as family"),
                    'essentials_employees_families.age',
                    'essentials_employees_families.gender',
                    'essentials_employees_families.address',
                    'essentials_employees_families.relative_relation',
                    'essentials_employees_families.eqama_number',

                ]);


            return Datatables::of($EssentialsEmployeesFamilies)

                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';

                        $html .= '<a  href="' . route('employee_families.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                        '&nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_employee_families_button" data-href="' . route('employee_families.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })

                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as 
         full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_families.index')->with(compact('users'));
    }


    public function create()
    {
        return view('essentials::create');
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            $input = $request->only(['first_name', 'last_name', 'address', 'age', 'gender', 'relative_relation', 'eqama_number', 'employee']);

            $input2['employee_id'] = $input['employee'];

            $input2['first_name'] = $input['first_name'];

            $input2['last_name'] = $input['last_name'];

            $input2['address'] = $input['address'];;

            $input2['relative_relation'] = $input['relative_relation'];

            $input2['age'] = $input['age'];

            $input2['gender'] = $input['gender'];

            $input2['eqama_number'] = $input['eqama_number'];

            EssentialsEmployeesFamily::create($input2);


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
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return redirect()->route('employee_families')->with(compact('users'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            EssentialsEmployeesFamily::where('id', $id)
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        $family = EssentialsEmployeesFamily::findOrFail($id);

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw(" CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_families.edit')->with(compact('users', 'family'));
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);



        try {
            $input = $request->only(['first_name', 'last_name', 'address', 'age', 'gender', 'relative_relation', 'eqama_number', 'employee']);

            $input2['employee_id'] = $input['employee'];

            $input2['first_name'] = $input['first_name'];

            $input2['last_name'] = $input['last_name'];

            $input2['address'] = $input['address'];;

            $input2['relative_relation'] = $input['relative_relation'];

            $input2['age'] = $input['age'];

            $input2['gender'] = $input['gender'];

            $input2['eqama_number'] = $input['eqama_number'];

            EssentialsEmployeesFamily::where('id', $id)->update($input2);
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

        $all_users = $query->select('id', DB::raw(" CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return redirect()->route('employee_families')->with(compact('users'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
}