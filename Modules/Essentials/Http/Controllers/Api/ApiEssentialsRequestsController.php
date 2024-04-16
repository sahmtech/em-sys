<?php

namespace Modules\Essentials\Http\Controllers\Api;

use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\Essentials\Entities\ToDo;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use App\Request as UserRequest;
use App\RequestAttachment;
use App\RequestProcess;
use App\User;
use App\Utils\RequestUtil;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\UserLeaveBalance;

class ApiEssentialsRequestsController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;
    protected $requestUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }




    public function storeApiRequest(Request $request,)
    {
        $user = User::where('id', Auth::user()->id)->first();
        $requestType = $request->requestType ?? null;
        try {

            $attachmentPath = $request->attachment ? $request->attachment->store('/requests_attachments') : null;
            $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
            $end_date = $request->end_date ?? $request->return_date;
            $today = Carbon::today();
            if ($startDate) {
                $startDateCarbon = Carbon::parse($startDate);
                if ($startDateCarbon->lt($today)) {
                    return new CommonResource([
                        'msg' => "تاريخ البداية يجب أن يكون لاحق"
                    ]);
                }
                if ($end_date) {

                    $endDateCarbon = Carbon::parse($end_date);
                    error_log($endDateCarbon);
                    if ($startDateCarbon->gt($endDateCarbon)) {
                        return new CommonResource([
                            'msg' => "تاريخ البداية يجب أن يسبق تاريخ النهاية"
                        ]);
                    }
                }
            }


            if (!$requestType || $requestType == null) {
                $type_id = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first()->id;

                $isExists = UserRequest::where('related_to', $user->id)->where('request_type_id', $type_id)->where('status', 'pending')->first();
                if ($isExists) {
                    return new CommonResource([
                        'msg' => "يوجد طلب سابق قيد المعالجة"
                    ]);
                } else {
                    $leaveBalance = UserLeaveBalance::where([
                        'user_id' => $user->id,
                        'essentials_leave_type_id' => $request->leaveType,
                    ])->first();

                    if (!$leaveBalance || $leaveBalance->amount == 0) {

                        return new CommonResource([
                            'msg' => "ليس لديك رصيد كاف"
                        ]);
                    } else {

                        $startDate = Carbon::parse($startDate);
                        $endDate = Carbon::parse($end_date);
                        $daysRequested = $startDate->diffInDays($endDate) + 1;

                        if ($daysRequested > $leaveBalance->amount) {
                            return new CommonResource([
                                'msg' => "ليس لديك رصيد كاف"
                            ]);
                        }
                    }
                    $Request = new UserRequest;

                    $Request->request_no = $this->requestUtil->generateRequestNo($type_id);
                    $Request->related_to = $user->id;
                    $Request->request_type_id = $type_id;
                    $Request->start_date = $startDate;
                    $Request->end_date = $end_date;
                    $Request->reason = $request->reason;
                    $Request->note = $request->note;
                    $Request->attachment = $attachmentPath;
                    $Request->essentials_leave_type_id = $request->leaveType;
                    $Request->created_by = $user->id;
                    $Request->save();



                    if ($attachmentPath) {
                        RequestAttachment::create([
                            'request_id' => $Request->id,
                            'file_path' => $attachmentPath,
                        ]);
                    }
                    if ($Request) {
                        $process = null;

                        $department_id = $user->essentials_department_id;


                        if ($department_id) {
                            $process = RequestProcess::create([
                                'started_department_id' => $department_id,
                                'request_id' => $Request->id,
                                'superior_department_id' => $department_id,
                                'status' => 'pending'
                            ]);
                        } else {


                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                            return new CommonResource([
                                'msg' => "يجب أن ينتمي الموظف لاحد الأقسام"
                            ]);
                        }

                        if (!$process) {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                        }




                        return new CommonResource([
                            'msg' => "تم رفع الطلب بنجاح"
                        ]);
                    }
                    return new CommonResource([
                        'msg' => "تعذر رفع الطلب، حاول مجددا في وقت لاحق"
                    ]);
                }
            } else {
                $type = RequestsType::where('id', $requestType)->first()->type;
                if ($type == 'cancleContractRequest' && !empty($request->main_reason)) {

                    $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->firstOrFail();
                    if (is_null($contract->wish_id)) {
                        return new CommonResource([
                            'msg' => "يجب تحديد رغبة"
                        ]);
                    }
                    if (now()->diffInMonths($contract->contract_end_date) > 1) {
                        return new CommonResource([
                            'msg' => "العقد منتهي"
                        ]);
                    }
                }

                $isExists = UserRequest::where('related_to', $user->id)->where('request_type_id', $requestType)->where('status', 'pending')->first();
                if ($isExists) {
                    return new CommonResource([
                        'msg' => "يوجد طلب سابق قيد المعالجة"
                    ]);
                } else {
                    if ($type == "exitRequest") {

                        $startDate = DB::table('essentials_employees_contracts')->where('employee_id',  $user->id)->first()->contract_end_date ?? null;
                    }
                    $Request = new UserRequest;

                    $Request->request_no = $this->requestUtil->generateRequestNo($requestType);
                    $Request->related_to = $user->id;
                    $Request->request_type_id = $requestType;
                    $Request->start_date = $startDate;
                    $Request->end_date = $end_date;
                    $Request->reason = $request->reason;
                    $Request->note = $request->note;
                    $Request->attachment = $attachmentPath;
                    $Request->escape_time = $request->escape_time;
                    $Request->installmentsNumber = $request->installmentsNumber;
                    $Request->monthlyInstallment = $request->monthlyInstallment;
                    $Request->advSalaryAmount = $request->amount;
                    $Request->created_by =  $user->id;
                    $Request->insurance_classes_id = $request->ins_class;
                    $Request->baladyCardType = $request->baladyType;
                    $Request->resCardEditType = $request->resEditType;
                    $Request->workInjuriesDate = $request->workInjuriesDate;
                    $Request->contract_main_reason_id = $request->main_reason;
                    $Request->contract_sub_reason_id = $request->sub_reason;
                    $Request->visa_number = $request->visa_number;
                    $Request->atmCardType = $request->atmType;
                    $Request->save();
                    if ($attachmentPath) {
                        RequestAttachment::create([
                            'request_id' => $Request->id,
                            'file_path' => $attachmentPath,
                        ]);
                    }
                    if ($Request) {
                        $process = null;

                        $department_id = $user->id->essentials_department_id;




                        if ($department_id) {
                            $process = RequestProcess::create([
                                'started_department_id' => $department_id,
                                'request_id' => $Request->id,
                                'superior_department_id' => $department_id,
                                'status' => 'pending'
                            ]);
                        } else {


                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                            return new CommonResource([
                                'msg' => "يجب أن ينتمي الموظف لاحد الأفسام"
                            ]);
                        }

                        if (!$process) {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                        }
                        return new CommonResource([
                            'msg' => "تم رفع الطلب بنجاح"
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return $this->otherExceptions($e);
        }
    }



    public function getRequestTypes()
    {
        $allRequestTypes = RequestsType::where('for', 'employee')->pluck('type', 'id');
        return new CommonResource([
            'request_types' => $allRequestTypes
        ]);
    }



    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function makeRequest(Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->business_id;


            $attachmentPath = null;
            if (isset($request->attachment) && !empty($request->attachment)) {
                $attachmentPath = $request->attachment->store('/requests_attachments');
            }
            $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
            ////////////////////////
            // make sure the request has a procesure
            ////////////////
            $workerRequest = new FollowupWorkerRequest;

            $workerRequest->request_no = $this->generateRequestNo("leavesAndDepartures");
            $workerRequest->worker_id =  $user->id;
            $workerRequest->type = "leavesAndDepartures";
            $workerRequest->start_date = $start_date;
            $workerRequest->end_date = $end_date;
            $workerRequest->note = $request->note;
            $workerRequest->attachment = $attachmentPath;
            $workerRequest->essentials_leave_type_id = $request->leaveTypeId;
            $workerRequest->save();
            $success = 1;
            if ($workerRequest) {
                $process = FollowupWorkerRequestProcess::create([
                    'worker_request_id' => $workerRequest->id,
                    'procedure_id' => $this->getProcedureIdForType("leavesAndDepartures"),
                    'status' => 'pending',
                    'reason' => null,
                    'status_note' => null,
                ]);

                if (!$process) {

                    $workerRequest->delete();
                    // $output = [
                    //     'success' => 0,
                    //     'msg' => __('messages.something_went_wrong'),
                    // ];
                    // return redirect()->route('allRequests')->withErrors([$output['msg']]);
                    $success = 0;
                }
            } else {

                $success = 0;
                // $output = [
                //     'success' => 0,
                //     'msg' => __('messages.something_went_wrong'),
                // ];
                // return redirect()->route('allRequests')->withErrors([$output['msg']]);
            }








            $res = [
                'msg' => "تم رفع الطلب بنجاح"
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function makeLeaves(Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->business_id;


            $attachmentPath = null;
            if (isset($request->attachment) && !empty($request->attachment)) {
                $attachmentPath = $request->attachment->store('/requests_attachments');
            }
            $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
            ////////////////////////
            // make sure the request has a procesure
            ////////////////
            $workerRequest = new FollowupWorkerRequest;

            $workerRequest->request_no = $this->generateRequestNo("leavesAndDepartures");
            $workerRequest->worker_id =  $user->id;
            $workerRequest->type = "leavesAndDepartures";
            $workerRequest->start_date = $start_date;
            $workerRequest->end_date = $end_date;
            $workerRequest->note = $request->note;
            $workerRequest->attachment = $attachmentPath;
            $workerRequest->essentials_leave_type_id = $request->leaveTypeId;
            $workerRequest->save();
            $success = 1;
            if ($workerRequest) {
                $process = FollowupWorkerRequestProcess::create([
                    'worker_request_id' => $workerRequest->id,
                    'procedure_id' => $this->getProcedureIdForType("leavesAndDepartures"),
                    'status' => 'pending',
                    'reason' => null,
                    'status_note' => null,
                ]);

                if (!$process) {

                    $workerRequest->delete();
                    // $output = [
                    //     'success' => 0,
                    //     'msg' => __('messages.something_went_wrong'),
                    // ];
                    // return redirect()->route('allRequests')->withErrors([$output['msg']]);
                    $success = 0;
                }
            } else {

                $success = 0;
                // $output = [
                //     'success' => 0,
                //     'msg' => __('messages.something_went_wrong'),
                // ];
                // return redirect()->route('allRequests')->withErrors([$output['msg']]);
            }








            $res = [
                'msg' => "تم رفع الطلب بنجاح"
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
    private function getProcedureIdForType($type)
    {

        $procedure = EssentialsWkProcedure::where('type', $type)->where('start', 1)->first();

        return $procedure ? $procedure->id : null;
    }

    private function generateRequestNo($type)
    {
        $latestRecord = FollowupWorkerRequest::where('type', $type)->orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $prefix = $this->getTypePrefix($type);
            $numericPart = (int)substr($latestRefNo, strlen($prefix));
            $numericPart++;
            $input['request_no'] = $prefix . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $input['request_no'] = $this->getTypePrefix($type) . '0001';
        }

        return $input['request_no'];
    }

    private function getTypePrefix($type)
    {

        $typePrefixMap = [
            'exitRequest' => 'ex',
            'returnRequest' => 'ret',
            'leavesAndDepartures' => 'lev',
            'residenceRenewal' => 'resRe',
            'escapeRequest' => 'escRe',
            'advanceSalary' => 'advRe',
            'atmCard' => 'atm',
            'residenceCard' => 'res',
            'workerTransfer' => 'wT',
            'workInjuriesRequest' => 'wIng',
            'residenceEditRequest' => 'resEd',
            'baladyCardRequest' => 'bal',
            'insuranceUpgradeRequest' => 'insUp',
            'mofaRequest' => 'mofa',
            'chamberRequest' => 'ch',
            'cancleContractRequest' => 'con',
            'WarningRequest' => 'WR'

        ];

        return $typePrefixMap[$type];
    }
}
