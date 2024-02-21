<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\WkProcedure;
use Yajra\DataTables\Facades\DataTables;

class RequestTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_edit_requests_type = auth()->user()->can('ceomanagment.edit_requests_type');
        $can_delete_requests_type = auth()->user()->can('ceomanagment.delete_requests_type');

        $allRequestsTypes = [
            'exitRequest',
            'returnRequest',
            'escapeRequest',
            'advanceSalary',
            'leavesAndDepartures',
            'atmCard',
            'residenceRenewal',
            'residenceCard',
            'workerTransfer',
            'workInjuriesRequest',
            'residenceEditRequest',
            'baladyCardRequest',
            'insuranceUpgradeRequest',
            'mofaRequest',
            'chamberRequest',
            'cancleContractRequest',
            'WarningRequest',
            'assetRequest',
            'passportRenewal',
            'AjirAsked',
        ];

        $typesWithBoth = RequestsType::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('requests_types as r2')
                ->whereColumn('r2.type', 'requests_types.type')
                ->where('r2.for', 'worker');
        })
            ->where('for', 'employee')
            ->distinct()
            ->pluck('type')->toArray();

        $missingTypes = array_diff($allRequestsTypes, $typesWithBoth);

        $requestsTypes = RequestsType::all();

        if (request()->ajax()) {


            return datatables::of($requestsTypes)

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_requests_type, $can_delete_requests_type) {
                        $html = '';
                        // if ($is_admin || $can_edit_requests_type) {
                        //     $html .= '<a href="#" class="btn btn-xs btn-primary edit-item"
                        //  data-id="' . $row->id . '" data-type-value="' . $row->type . '" data-prefix-value="' . $row->prefix . '" data-for-value="' . $row->for . '">
                        //  <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>&nbsp;';
                        // }
                        if ($is_admin || $can_delete_requests_type) {
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button"
                            data-href="' . route('deleteRequestType', ['id' => $row->id]) . '">
                            <i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )


                ->rawColumns(['action'])
                ->make(true);
        }
        return view('ceomanagment::requests_types.index')->with(compact('missingTypes'));
    }


    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $exist = RequestsType::where([['type', $request->type], ['for', $request->for]])->first();
        if ($exist) {
            $output = [
                'success' => false,
                'msg' => __('ceomanagment::lang.this_type_is_already_exists'),
            ];
            return redirect()->back()->with(['status' => $output]);
        }


        try {
            $input = $request->only(['type', 'for']);
            $input['prefix'] = $this->getTypePrefix($input['type']);
            if ($input['for'] != 'both') {
                RequestsType::create($input);
            }
            if ($input['for'] === 'both') {
                $existingFor = RequestsType::where('type', $input['type'])->value('for');

                $forValues = ['worker', 'employee'];

                if ($existingFor) {
                    $forValues = array_diff($forValues, [$existingFor]);
                }

                foreach ($forValues as $forValue) {
                    $input['for'] = $forValue;
                    RequestsType::create($input);
                }
            }

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


        return redirect()->back()->with(['status' => $output]);
    }




    public function update(Request $request)

    {

        try {

            $exist = RequestsType::where('id', '!=', $request->request_type_id)->where([['type2', $request->type], ['for2', $request->for]])->first();
            if ($exist) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.this_type_is_already_exists'),
                ];
                return redirect()->back()->with($output);
            }
            $input = $request->only(['type2', 'request_type_id', 'for2']);
            $requestType = RequestsType::findOrFail($input['request_type_id']);
            $requestType->type = $input['type2'];

            if ($input['for2'] !== 'both') {
                $requestType->for = $input['for2'];
            } elseif ($input['for2'] === 'both') {
                $newFor = $requestType->for === 'worker' ? 'employee' : 'worker';
                $requestType = new RequestsType();
                $requestType->type = $input['type2'];
                $requestType->prefix = $this->getTypePrefix($input['type2']);
                $requestType->for = $newFor;
            }

            $requestType->save();

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

        return redirect()->back()->with($output);
    }

    private function getTypePrefix($type)
    {

        $typePrefixMap = [

            'exitRequest' => 'ExReq_',
            'returnRequest' => 'RtnReq_',
            'leavesAndDepartures' => 'LvDepReq_',
            'residenceRenewal' => 'ResRenew_',
            'escapeRequest' => 'EscReq_',
            'advanceSalary' => 'AdvSal_',
            'atmCard' => 'ATMReq_',
            'residenceCard' => 'ResCard_',
            'workerTransfer' => 'WrkTrans_',
            'workInjuriesRequest' => 'InjReq_',
            'residenceEditRequest' => 'ResEdit_',
            'baladyCardRequest' => 'BalCard_',
            'insuranceUpgradeRequest' => 'InsUpg_',
            'mofaRequest' => 'MOFAReq_',
            'chamberRequest' => 'ChamReq_',
            'cancleContractRequest' => 'ConReq_',
            'WarningRequest' => 'WrReq_',
            'assetRequest' => 'AssetReq_',
            'passportRenewal' => 'PasRenew_',
            'AjirAsked' => 'Asked_'
        ];

        return $typePrefixMap[$type];
    }
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $procedures = WkProcedure::Where('request_type_id', $id)->first();

            if ($procedures) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.cant_delete,delete_the_procedure_first'),
                ];
                return $output;
            }
            RequestsType::where('id', $id)
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