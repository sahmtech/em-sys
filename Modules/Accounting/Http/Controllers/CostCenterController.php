<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use App\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Entities\CostCenter;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;

use Illuminate\Support\Facades\Session as FacadesSession;

class CostCenterController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    protected function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = FacadesSession::get('selectedCompanyId');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_cost_center = auth()->user()->can('accounting.cost_center');
        if (!($is_admin || $can_cost_center)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $can_costCenter_edit = auth()->user()->can('accounting.costCenter.edit');
        $can_costCenter_delete = auth()->user()->can('accounting.costCenter.delete');
        $mainCenters = CostCenter::query()->whereNull('deleted_at')->whereNull('parent_id')->get();
        $allCenters = CostCenter::query()->whereNull('deleted_at')->get();
        $businessLocations = BusinessLocation::where('business_id', $business_id)->where('company_id', $company_id)->get();
        // $businessLocations = BusinessLocation::query()->get();
        if (request()->ajax()) {
            $costCenters = CostCenter::query()->orderBy('id');
            return Datatables::of($costCenters)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_costCenter_edit, $can_costCenter_delete) {
                        $editUrl = action('\Modules\Accounting\Http\Controllers\CostCenterController@edit', [$row->id]);
                        $deleteUrl = action('\Modules\Accounting\Http\Controllers\CostCenterController@destroy', [$row->id]);
                        $html = '';

                        if ($is_admin  || $can_costCenter_edit) {
                            $html .=  '<button data-businesslocationid="' . $row->business_location_id . '" data-parent="' . $row->parent_id . '" data-accountcenternumber="' . $row->account_center_number . '" data-namear="' . $row->ar_name . '" data-nameen="' . $row->en_name . '" data-id="' . $row->id . '" class="btn btn-xs btn-primary btn-modal edit_cost_center" data-toggle="modal" data-target="#edit_cost_center_modal"><i class="glyphicon glyphicon-edit"></i>' . __("messages.edit") . '</button>';
                        }
                        if ($is_admin  || $can_costCenter_delete) {
                            $html .=  '<button data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_cost_center_button"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</button>
                    ';
                        }
                        return $html;
                    }
                )
                ->editColumn('name', function ($row) {
                    $any = null;
                    if ($row->parent_id != null) {
                        $name = app()->getLocale() . '_name';
                        $any = CostCenter::query()->find($row->parent_id)->$name;
                    }
                    return $any;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $company_name = Company::where('id', $company_id)->first()->name;
        $breadcrumbs = [
            ['title' => __('accounting::lang.companies'), 'url' => route('accountingLanding')],
            ['title' => $company_name, 'url' => route('accounting.dashboard')],
            ['title' => __('accounting::lang.cost_center'), 'url' =>  action([\Modules\Accounting\Http\Controllers\CostCenterController::class, 'index'])],
        ];
        return view('accounting::cost_center.index', compact('mainCenters', 'allCenters', 'businessLocations', 'breadcrumbs'));
    }

    protected function store(Request $request)
    {
        $company_id = FacadesSession::get('selectedCompanyId');


        $rules = [
            'ar_name' => 'required|String|min:3|max:191|unique:accounting_cost_centers,ar_name',
            'en_name' => 'required|String|min:3|max:191|unique:accounting_cost_centers,en_name',
            'account_center_number' => 'required|Numeric|unique:accounting_cost_centers,account_center_number',
            'parent_id' => 'nullable|exists:accounting_cost_centers,id',
            'business_location_id' => 'nullable|exists:business_locations,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $failedRules = $validator->failed();
            //            if (isset($failedRules['ar_name']['min']) || isset($failedRules['ar_name']['max'])) {
            //                return response()->json(['fail' => __("messages.something_went_wrong")]);
            //            }
            return redirect()->back()->with([
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ]);
            // return response()->json([
            //     'success' => false,
            //     'msg' => __("messages.something_went_wrong")
            // ]);
        }
        $validated = $validator->validated();
        $validated['business_id'] = $request->session()->get('user.business_id');
        $validated['company_id'] = $company_id;

        CostCenter::query()->create($validated);
        //   return response()->json([
        //     'success' => true,
        //     'msg' => __("lang_v1.added_success")
        // ]);
        return redirect()->back()->with([
            'success' => true,
            'msg' => __("lang_v1.added_success")
        ]);
    }

    public function update(Request $request,)
    {
        $id = $request->id;
        $costCenter = CostCenter::query()->find($id);
        $rules = [
            'ar_name' => 'required|String|min:3|max:191|unique:accounting_cost_centers,ar_name,' . $id,
            'en_name' => 'required|String|min:3|max:191|unique:accounting_cost_centers,en_name,' . $id,
            'account_center_number' => 'required|Numeric|unique:accounting_cost_centers,account_center_number,' . $id,
            'parent_id' => 'nullable|exists:accounting_cost_centers,id',
            'business_location_id' => 'nullable|exists:business_locations,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $failedRules = $validator->failed();
            if (isset($failedRules['ar_name']['min']) || isset($failedRules['ar_name']['max'])) {
                return redirect()->back()->with([
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ]);
                // return response()->json([
                //     'success' => false,
                //     'msg' => __("messages.something_went_wrong")
                // ]);
            }
            return redirect()->back()->with([
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ]);
            // return response()->json([
            //     'success' => false,
            //     'msg' => __("messages.something_went_wrong")
            // ]);
        }


        $costCenter->update([
            'ar_name' => $request->ar_name,
            'en_name' => $request->en_name,
            'account_center_number' => $request->account_center_number,
            'parent_id' => $request->parent_id,
            'business_location_id' => $request->business_location_id,
        ]);

        return redirect()->back()->with([
            'success' => true,
            'msg' => __("lang_v1.updated_success")
        ]);



        return response()->json([
            'success' => true,
            'msg' => __("lang_v1.updated_success")
        ]);
    }

    protected function destroy($id)
    {
        if (\request()->ajax()) {
            CostCenter::query()->find($id)->delete();
            CostCenter::query()->where('parent_id', $id)->delete();
            return [
                'success' => true,
                'msg' => __("lang_v1.deleted_success")
            ];
        }
    }
}
