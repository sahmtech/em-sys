<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Entities\CostCenter;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
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

        if (!(auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            !(auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }
        $mainCenters = CostCenter::query()->whereNull('deleted_at')->whereNull('parent_id')->get();
        $allCenters = CostCenter::query()->whereNull('deleted_at')->get();
        $businessLocations = BusinessLocation::query()->get();
        if (request()->ajax()) {
            $costCenters = CostCenter::query()->orderBy('id');
            return Datatables::of($costCenters)
                ->addColumn(
                    'action', function ($row) {
                    $editUrl = action('\Modules\Accounting\Http\Controllers\CostCenterController@edit', [$row->id]);
                    $deleteUrl = action('\Modules\Accounting\Http\Controllers\CostCenterController@destroy', [$row->id]);

                    return '<button data-businesslocationid="'.$row->business_location_id.'" data-parent="' . $row->parent_id . '" data-accountcenternumber="' . $row->account_center_number . '" data-namear="' . $row->ar_name . '" data-nameen="' . $row->en_name . '" data-id="' . $row->id . '" class="btn btn-xs btn-primary btn-modal edit_cost_center" data-toggle="modal" data-target="#edit_cost_center_modal"><i class="glyphicon glyphicon-edit"></i>' . __("messages.edit") . '</button>

                        <button data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_cost_center_button"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</button>
                    ';
                })
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

        return view('accounting::cost_center.index', compact('mainCenters', 'allCenters', 'businessLocations'));
    }

    protected function store(Request $request)
    {
        $rules = [
            'ar_name' => 'required|String|min:3|max:191|unique:cost_centers,ar_name',
            'en_name' => 'required|String|min:3|max:191|unique:cost_centers,en_name',
            'account_center_number' => 'required|Numeric|unique:cost_centers,account_center_number',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'business_location_id' => 'nullable|exists:business_locations,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $failedRules = $validator->failed();
//            if (isset($failedRules['ar_name']['min']) || isset($failedRules['ar_name']['max'])) {
//                return response()->json(['fail' => __("messages.something_went_wrong")]);
//            }
            return response()->json(['success' => false,
                'msg' => __("messages.something_went_wrong")
            ]);
        }
        $validated = $validator->validated();
        $validated['business_id'] = $request->session()->get('user.business_id');
        CostCenter::query()->create($validated);
        return response()->json(['success' => true,
            'msg' => __("lang_v1.added_success")
        ]);
    }

    protected function update(Request $request, $id)
    {
        $costCenter = CostCenter::query()->find($id);
        $rules = [
            'ar_name' => 'required|String|min:3|max:191|unique:cost_centers,ar_name,' . $id,
            'en_name' => 'required|String|min:3|max:191|unique:cost_centers,en_name,' . $id,
            'account_center_number' => 'required|Numeric|unique:cost_centers,account_center_number,' . $id,
            'parent_id' => 'nullable|exists:cost_centers,id',
            'business_location_id' => 'nullable|exists:business_locations,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $failedRules = $validator->failed();
            if (isset($failedRules['ar_name']['min']) || isset($failedRules['ar_name']['max'])) {
                return response()->json(['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ]);
            }
            return response()->json(['success' => false,
                'msg' => __("messages.something_went_wrong")
            ]);
        }
        $costCenter->update([
            'ar_name' => $request->ar_name,
            'en_name' => $request->en_name,
            'account_center_number' => $request->account_center_number,
            'parent_id' => $request->parent_id,
            'business_location_id' => $request->business_location_id,
        ]);
        return response()->json(['success' => true,
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
