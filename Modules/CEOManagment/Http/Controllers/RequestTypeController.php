<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\Task;
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
        $can_edit_requests_type = auth()->user()->can('generalmanagement.edit_requests_type');
        $can_delete_requests_type = auth()->user()->can('generalmanagement.delete_requests_type');

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
            'AlternativeWorker',
            // 'TransferringGuaranteeFromExternalClient',
            'Permit',
            'FamilyInsurace',
            'Ajir_link',
            'ticketReservationRequest',
            'authorizationRequest',
            'interviewsRequest',
            'salaryInquiryRequest',
            'moqimPrint',
            'salaryIntroLetter',
            'QiwaContract',
            'ExitWithoutReturnReport',
            'residenceIssue',
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
                ->addColumn('tasks', function ($requestType) {

                    $tasksList = $requestType->tasks->map(function ($task) {
                        return $task->description;
                    })->implode('<br>');

                    return $tasksList;
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_requests_type, $can_delete_requests_type) {
                        $html = '';
                        if ($is_admin || $can_edit_requests_type) {

                            $html .= '<a href="#"
                            class="btn btn-xs btn-primary edit-request-type"
                            data-id="' . $row->id . '"
                            data-url="' . route('getRequestType', ['request_type_id' => $row->id]) . '">
                            ' . __('ceomanagment::lang.edit_request_tasks') . '
                        </a>&nbsp;';
                            $html .= '<a href="#" class="btn btn-xs btn-primary edit-request-type-btn" data-id="' . $row->id . '" data-url="' . route('getRequestType', ['request_type_id' => $row->id]) . '">' . __('ceomanagment::lang.edit_requests_type') . '</a>&nbsp;';
                        }
                        if ($is_admin || $can_delete_requests_type) {
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button"
                            data-href="' . route('deleteRequestType', ['id' => $row->id]) . '">
                            <i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )

                ->rawColumns(['action', 'tasks'])
                ->make(true);
        }
        return view('ceomanagment::requests_types.index')->with(compact('missingTypes'));
    }

    public function updateSelfishService($id)
    {
        $requestType = RequestsType::find($id);
        error_log($requestType);
        if ($requestType) {
            $requestType->selfish_service = 1;
            $requestType->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 400);
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
            $input = $request->only(['type', 'for', 'selfish_service', 'user_type']);
            $input['prefix'] = $this->getTypePrefix($input['type']);
            $input['selfish_service'] = $input['selfish_service'] ?? 0;
            $requestTypeIds = [];
            if ($input['for'] != 'both') {

                $requestType = RequestsType::create($input);
                $requestTypeIds[] = $requestType->id;
            }
            if ($input['for'] === 'both') {
                $existingFor = RequestsType::where('type', $input['type'])->value('for');

                $forValues = ['worker', 'employee'];

                if ($existingFor) {
                    $forValues = array_diff($forValues, [$existingFor]);
                }

                foreach ($forValues as $forValue) {
                    $input['for'] = $forValue;
                    $requestType = RequestsType::create($input);
                    $requestTypeIds[] = $requestType->id;
                }
            }
            $tasks = $request->tasks ?? [];
            $links = $request->task_links ?? [];

            foreach ($requestTypeIds as $requestTypeId) {
                foreach ($tasks as $index => $task) {
                    if (!empty($task)) {
                        $taskInput = [
                            'description' => $task,
                            'link' => $links[$index] ?? null,
                            'request_type_id' => $requestTypeId,
                        ];

                        Task::create($taskInput);
                    }
                }
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log($e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }
    public function updateRequestType(Request $request, $id)
    {
        try {
            $requestType = RequestsType::findOrFail($id);

            // Check if there are any procedures linked to this request type
            $procedures = WkProcedure::where('request_type_id', $id)->get();
            if ($procedures->count() != 0) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.cant_edit_type_it_have_procedures'),
                ];
                return response()->json($output);
            }

            // Update request type details
            $requestType->update($request->only(['type', 'for', 'selfish_service', 'user_type']));

            // Update tasks
            Task::where('request_type_id', $id)->delete();
            if (isset($request->tasks)) {
                $descriptions = $request->tasks['description'] ?? [];
                $links = $request->tasks['link'] ?? [];

                foreach ($descriptions as $index => $description) {
                    if (!empty($description)) {
                        $taskInput = [
                            'description' => $description,
                            'link' => $links[$index] ?? null,
                            'request_type_id' => $id,
                        ];
                        Task::create($taskInput);
                    }
                }
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            return $e;
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }

    // public function update(Request $request)

    // {

    //     try {
    //         $requests = WkProcedure::where('request_type_id', $request->request_type_id)->get();

    //         if ($requests->count() != 0) {

    //             $output = [
    //                 'success' => false,
    //                 'msg' => __('ceomanagment::lang.cant_edit_type_it_have_procedures'),
    //             ];
    //             return $output;
    //         }
    //         Task::where('request_type_id', $request->request_type_id)->delete();
    //         if (isset($request->tasks)) {

    //             $descriptions = $request->tasks['description'] ?? [];
    //             $links = $request->tasks['link'] ?? [];

    //             foreach ($descriptions as $index => $description) {

    //                 if (!empty($description)) {

    //                     $taskInput = [
    //                         'description' => $description,
    //                         'link' => $links[$index] ?? null,
    //                         'request_type_id' => $request->request_type_id,
    //                     ];

    //                     Task::create($taskInput);
    //                 }
    //             }
    //         }

    //         $output = [
    //             'success' => true,
    //             'msg' => __('lang_v1.updated_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => false,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }

    //     return $output;
    // }
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $tasks = $request->tasks ?? [];
            $deletedTasks = json_decode($request->deleted_tasks, true) ?? [];

            // Handle deleted tasks
            if (!empty($deletedTasks)) {
                $linkedTasks = DB::table('procedure_tasks')
                    ->whereIn('task_id', $deletedTasks)
                    ->pluck('task_id')
                    ->toArray();

                if (!empty($linkedTasks)) {
                    return response()->json([
                        'success' => false,
                        'msg' => __('ceomanagment::lang.task_linked_procedure_error'),
                    ]);
                }

                Task::whereIn('id', $deletedTasks)->delete();
            }

            // Handle updated and new tasks
            if (!empty($tasks)) {
                foreach ($tasks['description'] as $type => $taskDescriptions) {
                    foreach ($taskDescriptions as $index => $description) {
                        if (!empty($description)) {
                            $link = $tasks['link'][$type][$index] ?? null;

                            if ($type === 'old') {
                                $task = Task::find($tasks['id'][$type][$index]);
                                $task?->update([
                                    'description' => $description,
                                    'link' => $link !== 'null' ? $link : null,
                                ]);
                            } elseif ($type === 'new') {
                                Task::create([
                                    'description' => $description,
                                    'link' => $link !== 'null' ? $link : null,
                                    'request_type_id' => $request->request_type_id,
                                ]);
                            }
                        }
                    }
                }
            }

            // Update the request type details
            $requestType = RequestsType::findOrFail($request->requestTypeId);
            $data = $request->only(['type', 'for', 'selfish_service', 'user_type']);
            $data['selfish_service'] = !empty($data['selfish_service']) ? 1 : 0;

            $requestType->update($data);

            // Commit the transaction and return success response
            DB::commit();

            return back()->with([
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
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
            'AjirAsked' => 'Asked_',
            'AlternativeWorker' => 'AlterWorker_',
            'TransferringGuaranteeFromExternalClient' => 'TFEC_',
            'Permit' => 'Permit_',
            'FamilyInsurace' => 'FamInsu',
            'Ajir_link' => 'AjirLink_',
            'ticketReservationRequest' => 'TicketReq_',
            'authorizationRequest' => 'AuthReq_',
            'salaryInquiryRequest' => 'salInquiryReq_',
            'interviewsRequest' => 'interviewReq_',
            'moqimPrint' => 'moqPrint_',
            'salaryIntroLetter' => 'salIntroLetter_',
            'QiwaContract' => 'QiwaCont_',
            'ExitWithoutReturnReport' => 'exitReport_',
            'residenceIssue' => 'ResIsu_',
        ];

        return $typePrefixMap[$type];
    }
    public function destroy($id)
    {

        try {
            $procedures = WkProcedure::Where('request_type_id', $id)->first();

            if ($procedures) {
                $output = [
                    'success' => false,
                    'msg' => __('ceomanagment::lang.cant_delete,delete_the_procedure_first'),
                ];
                return $output;
            }
            Task::where('request_type_id', $id)->delete();
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

    public function getTasksForType(Request $request)
    {
        $typeId = $request->typeId;

        // Log the typeId and the tasks retrieved
        \Log::info('Received type ID: ' . $typeId);

        // Retrieve tasks associated with the typeId
        $tasks = Task::where('request_type_id', $typeId)->pluck('description', 'id');

        \Log::info('Tasks retrieved: ' . json_encode($tasks));

        return response()->json($tasks);
    }

    public function getRequestType($id)
    {
        $requestType = RequestsType::with('tasks')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'requestType' => $requestType,
        ]);
    }
}
