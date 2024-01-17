<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsTravelCategorie;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;


class EssentialsTravelCategorieController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');


        $can_crud_travel_categories = auth()->user()->can('essentials.crud_travel_categories');
        $can_delete_travel_categories = auth()->user()->can('essentials.delete_travel_categories');
        $can_edit_travel_categories = auth()->user()->can('essentials.edit_travel_categories');
        $can_add_travel_categories = auth()->user()->can('essentials.add_travel_categories');

        if (!$can_crud_travel_categories) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (request()->ajax()) {
            $travel_categories = DB::table('essentials_travel_ticket_categories')->select(['id', 'name', 'employee_ticket_value', 'wife_ticket_value', 'children_ticket_value', 'details', 'is_active']);


            return Datatables::of($travel_categories)

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_delete_travel_categories, $can_edit_travel_categories) {
                        $html = '';
                        if ($is_admin || $can_edit_travel_categories) {
                            $html .= '<a href="' . route('travel_categorie.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                       &nbsp;';
                        }
                        if ($is_admin || $can_delete_travel_categories) {
                            $html .= '<button class="btn btn-xs btn-danger delete_travel_categorie_button" data-href="' . route('travel_categorie.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('essentials::settings.partials.travel_categories.index');
    }
    public function userTravelCat()
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!auth()->user()->can('essentials.view_user_travel_categorie')) {
            //temp  abort(403, 'Unauthorized action.');
        }


        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $userTravelCat = EssentialsEmployeeTravelCategorie::join('users as u', 'u.id', '=', 'essentials_employee_travel_categories.employee_id')
            ->whereIn('u.id', $userIds)
            ->select([
                'essentials_employee_travel_categories.id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'essentials_employee_travel_categories.categorie_id'

            ]);

        if (request()->ajax()) {


            return Datatables::of($userTravelCat)

                ->editColumn('categorie_id', function ($row) use ($travelCategories) {
                    $item = $travelCategories[$row->categorie_id] ?? '';

                    return $item;
                })
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        //         $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                        //     $html .= '<a  href="'. route('cancleActivition', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.cancleActivition').'</a>';
                        $html .= '<button class="btn btn-xs btn-danger delete_employee_travel_categorie_button" data-href="' . route('userTravelCat.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

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
        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                    ' - ',COALESCE(id_proof_number,'')) as 
             full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_features.travelCategorie')->with(compact('travelCategories', 'users'));
    }
    public function storeUserTravelCat(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');



        try {

            $input = $request->only(['employee', 'travel_categoire']);

            $input['employee_id'] = $input['employee'];
            $input['categorie_id'] = $input['travel_categoire'];
            EssentialsEmployeeTravelCategorie::create($input);


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


        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');

        return redirect()->route('userTravelCat')->with(compact('travelCategories', 'users'));
    }
    public function create()
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        return view('essentials::settings.partials.travel_categories.create');
    }


    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['name', 'employee_ticket_value', 'wife_ticket_value', 'children_ticket_value', 'details', 'is_active']);


            $input2['name'] =  $input['name'];

            $input2['employee_ticket_value'] = $input['employee_ticket_value'];

            $input2['wife_ticket_value'] = $input['wife_ticket_value'];

            $input2['children_ticket_value'] = $input['children_ticket_value'];

            $input2['details'] = $input['details'];

            $input2['is_active'] = $input['is_active'];


            EssentialsTravelTicketCategorie::create($input2);

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

        return redirect()->route('travel_categories');
    }

    public function show($id)
    {
        return view('essentials::show');
    }


    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $travel_categorie = EssentialsTravelTicketCategorie::findOrFail($id);



        return view('essentials::settings.partials.travel_categories.edit')->with(compact('travel_categorie'));
    }


    public function update(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['name', 'employee_ticket_value', 'wife_ticket_value', 'children_ticket_value', 'details', 'is_active']);


            $input2['name'] =  $input['name'];

            $input2['employee_ticket_value'] = $input['employee_ticket_value'];

            $input2['wife_ticket_value'] = $input['wife_ticket_value'];

            $input2['children_ticket_value'] = $input['children_ticket_value'];

            $input2['details'] = $input['details'];

            $input2['is_active'] = $input['is_active'];

            EssentialsTravelTicketCategorie::where('id', $id)->update($input2);
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


        return redirect()->route('travel_categories');
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsTravelTicketCategorie::where('id', $id)
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
    public function destroyUserTravelCat($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsEmployeeTravelCategorie::where('id', $id)
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
