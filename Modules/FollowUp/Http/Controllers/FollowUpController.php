<?php

namespace Modules\FollowUp\Http\Controllers;


use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;
use App\Request as UserRequest;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;

use Modules\Essentials\Entities\EssentialsWorkCard;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\FollowUp\Entities\FollowupUserAccessProject;
use Yajra\DataTables\Facades\DataTables;

class FollowUpController extends Controller
{

    protected $moduleUtil;
    protected $requestUtil;

    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_dashboard = auth()->user()->can('followup.followup_dashboard');
        if (!($is_admin || $can_followup_dashboard)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $business_id = request()->session()->get('user.business_id');

        $business = Business::where('id', $business_id)->first();
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'LIKE', '%متابعة%')
                ->orWhere(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%أعمال%');
                })->orWhere(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%شركات%');
                });
        })
            ->pluck('id')->toArray();

        $new_requests = UserRequest::whereDate('created_at', Carbon::now($business->time_zone)->toDateString())->count();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }



        $on_going_requests = UserRequest::where('requests.status', 'pending')
            ->leftJoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftJoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')
            ->whereIn('requests.related_to', $userIds)
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('request_processes.superior_department_id', $departmentIds);
            })
            ->count();

        $finished_requests = UserRequest::leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')->whereIn('requests.related_to', $userIds)->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('request_processes.superior_department_id', $departmentIds);
            })->where(function ($query) {
                $query->where('requests.status', 'rejected')
                    ->orWhere('requests.status', 'approved');
            })->count();

        $total_requests = UserRequest::leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')->whereIn('requests.related_to', $userIds)->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('request_processes.superior_department_id', $departmentIds);
            })->count();


        $counts =  $this->requestUtil->getCounts('followup');
        $today_requests =   $counts->today_requests;
        $pending_requests =   $counts->pending_requests;
        $completed_requests =   $counts->completed_requests;
        $all_requests =   $counts->all_requests;
        return view(
            'followup::index',
            compact('new_requests', 'on_going_requests', 'finished_requests', 'total_requests', 'today_requests', 'pending_requests', 'completed_requests', 'all_requests')
        );
    }
    public function getFilteredRequests($filter = null)
    {
        $can_change_status = auth()->user()->can('followup.change_request_status');
        $can_return_request = auth()->user()->can('followup.return_request');
        $can_show_request = auth()->user()->can('followup.show_request');
        return $this->requestUtil->getFilteredRequests('followup', $filter, $can_change_status, $can_return_request, $can_show_request, false, null);
    }



    public function followup_department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_view_department_employees = auth()->user()->can('followup.followup_view_department_employees');


        if (!($is_admin || $can_followup_view_department_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'LIKE', '%متابعة%')
                ->orWhere(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%أعمال%');
                })->orWhere(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%شركات%');
                });
        })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
        })->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"),
            'users.id_proof_number',
        ]);
        if (request()->ajax()) {

            return Datatables::of($users)

                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'full_name',
                    function ($row) {
                        return $row->full_name;
                    }
                )
                ->addColumn(
                    'id_proof_number',
                    function ($row) {
                        return $row->id_proof_number;
                    }
                )
                ->addColumn(
                    'appointment',
                    function ($row) {
                        return $row->appointment?->profession->name ?? '';
                    }
                )


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment'])
                ->make(true);
        }

        return view('followup::followup_department_employees');
    }


    public function withinTwoMonthExpiryContracts()
    {

        $business_id = 1;
        $business = Business::where('id', $business_id)->first();

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();


        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $userIds = User::whereIn('assigned_to', $followupUserAccessProject)->pluck('id')->toArray();
        }

        $contracts = User::whereIn('id', $userIds)->where('user_type', 'worker')
            ->whereHas('contract', function ($qu) use ($business) {
                $qu->where('is_active', 1)
                    ->whereDate('contract_end_date', '>=', Carbon::now($business->time_zone))
                    ->whereDate('contract_end_date', '<=', Carbon::now($business->time_zone)->addMonths(2));
            })
            ->whereHas('OfficialDocument', function ($query) {
                $query->where('type', 'residence_permit')->where('is_active', 1);
            });



        return DataTables::of($contracts)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->first_name . ' ' . $row->mid_name . ' ' . $row->last_name;
                }
            )
            ->addColumn(
                'residency',
                function ($row) {
                    foreach ($row->OfficialDocument as $item) {
                        if ($item->type == 'residence_permit') {
                            return $item->number;
                        }
                    }
                    return null;
                }
            )
            ->addColumn(
                'project',
                function ($row) {
                    return $row->assignedTo?->name ?? null;
                }
            )
            ->addColumn(
                'sponser',
                function ($row) {
                    return $row->company?->name ?? null;
                }
            )
            ->addColumn(
                'customer_name',
                function ($row) {
                    return $row->assignedTo?->contact->supplier_business_name ?? null;
                }
            )
            ->addColumn(
                'end_date',
                function ($row) {
                    return $row->contract->contract_end_date;
                }
            )

            ->addColumn(
                'action',
                ''
                // function ($row) {
                //     $html = '';
                //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                //     return $html;
                // }
            )


            ->removeColumn('id')
            ->rawColumns(['worker_name', 'residency',  'end_date', 'project', 'action'])
            ->make(true);
    }
    public function withinTwoMonthExpiryResidency()
    {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();


        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $userIds = User::whereIn('assigned_to', $followupUserAccessProject)->pluck('id')->toArray();
        }

        $residencies = EssentialsOfficialDocument::where('is_active', 1)
            ->where('type', 'residence_permit')
            ->whereDate('expiration_date', '>=', Carbon::now()->setTimezone($business->time_zone))
            ->whereDate('expiration_date', '<=', Carbon::now()->addMonths(2)->setTimezone($business->time_zone))
            ->whereHas('employee', function ($query) use ($userIds) {
                $query->where('user_type', 'worker')->whereIn('id', $userIds);
            });

        return DataTables::of($residencies)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->employee->first_name . ' ' . $row->employee->mid_name . ' ' . $row->employee->last_name;
                }
            )
            ->addColumn(
                'residency',
                function ($row) {
                    return $row->number;
                }
            )
            ->addColumn(
                'sponser',
                function ($row) {
                    return $row->employee?->company->name ?? null;
                }
            )
            ->addColumn(
                'project',
                function ($row) {
                    return $row->employee->assignedTo?->contact->supplier_business_name ?? null;
                }
            )
            ->addColumn(
                'customer_name',
                function ($row) {
                    return $row->employee->assignedTo?->contact->supplier_business_name ?? null;
                }
            )
            ->addColumn(
                'end_date',
                function ($row) {
                    return $row->expiration_date;
                }
            )
            ->addColumn(
                'action',
                ''
                // function ($row) {
                //     $html = '';
                //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                //     return $html;
                // }
            )


            ->removeColumn('id')
            ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
            ->make(true);
    }

    public function withinTwoMonthExpiryWorkCard()
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();


        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $userIds = User::whereIn('assigned_to', $followupUserAccessProject)->pluck('id')->toArray();
        }

        $business = Business::where('id', $business_id)->first();

        $work_cards = EssentialsWorkCard::with(['user', 'user.officialDocument' => function ($query) use ($business) {
            $query->where('is_active', 1)
                ->where('type', 'residence_permit')
                ->whereDate('expiration_date', '>=', Carbon::now()->setTimezone($business->time_zone))
                ->whereDate('expiration_date', '<=', Carbon::now()->addMonths(2)->setTimezone($business->time_zone));
        }])->where('is_active', 1)
            ->whereHas('user', function ($query) use ($business) {
                $query->whereHas('officialDocument', function ($query) use ($business) {
                    $query->where('is_active', 1)
                        ->where('type', 'residence_permit')
                        ->whereDate('expiration_date', '>=', Carbon::now()->setTimezone($business->time_zone))
                        ->whereDate('expiration_date', '<=', Carbon::now()->addMonths(2)->setTimezone($business->time_zone));
                });
            });


        return DataTables::of($work_cards)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->user->first_name . ' ' . $row->user->mid_name . " " . $row->user->last_name ?? " ";
                }
            )
            ->addColumn('residency', function ($row) {
                return $row->user->id_proof_number ?? "";
            })

            ->addColumn(
                'sponser',
                function ($row) {
                    return $row->user->company?->name ?? null;
                }
            )
            ->addColumn(
                'work_card_no',
                function ($row) {
                    return $row->work_card_no ?? " ";
                }
            )
            ->addColumn(
                'project',
                function ($row) {
                    return $row->user->assignedTo?->name ?? "";
                }
            )
            ->addColumn(
                'customer_name',
                function ($row) {
                    return $row->user->assignedTo?->contact?->supplier_business_name ??
                        "";
                }
            )
            ->addColumn(
                'end_date',
                function ($row) {
                    return $row->user->officialDocument()->where('is_active', 1)->where('type', 'residence_permit')->first()->expiration_date ?? "";
                }
            )

            ->addColumn(
                'action',
                ''
                // function ($row) {
                //     $html = '';
                //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                //     return $html;
                // }
            )


            ->removeColumn('id')
            ->rawColumns(['worker_name', 'residency', 'work_card_no', 'end_date', 'project', 'action'])
            ->make(true);
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
