<?php

namespace Modules\FollowUp\Http\Controllers;

use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Charts\CommonChart;
use App\Contact;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use DB;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Yajra\DataTables\Facades\DataTables;

class FollowUpController extends Controller
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
        $business = Business::where('id', $business_id)->first();

        $new_requests = FollowupWorkerRequest::whereDate('created_at', Carbon::now($business->time_zone)->toDateString())->count();

        $on_going_requests = FollowupWorkerRequest::where('status', 'under_process')->count();
        $finished_requests = FollowupWorkerRequest::where('status', 'rejected')->orWhere('status', 'approved')->count();
        $total_requests = FollowupWorkerRequest::count();


        return view('followup::index', compact('new_requests', 'on_going_requests', 'finished_requests', 'total_requests'));
    }
    public function withinTwoMonthExpiryContracts()
    {
        // $business_id = request()->session()->get('user.business_id');
        $business_id = 1;
        $business = Business::where('id', $business_id)->first();
        $contracts = User::where('user_type', 'worker')->whereHas('contract', function ($qu) use ($business) {
            $qu->whereDate('contract_end_date', '>=', Carbon::now($business->time_zone))
                ->whereDate('contract_end_date', '<=', Carbon::now($business->time_zone)->addMonths(2));
        })
            ->whereHas('OfficialDocument', function ($query) {
                $query->where('type', 'residence_permit');
            });

       
        return DataTables::of($contracts)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
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
        $residencies = EssentialsOfficialDocument::where('type', 'residence_permit')
            ->whereDate('expiration_date', '>=', Carbon::now($business->time_zone))
            ->whereDate('expiration_date', '<=', Carbon::now($business->time_zone)->addMonths(2))
            ->whereHas('employee', function ($qu) {
                $qu->where('user_type', 'worker');
            });

        return DataTables::of($residencies)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->employee->first_name . ' ' . $row->employee->last_name;
                }
            )
            ->addColumn(
                'residency',
                function ($row) {
                    return $row->number;
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

        $business = Business::where('id', $business_id)->first();
        $contracts = User::where('user_type', 'worker')->whereHas('contract', function ($qu) use ($business) {
            $qu->whereDate('contract_end_date', '>=', Carbon::now($business->time_zone))
                ->whereDate('contract_end_date', '<=', Carbon::now($business->time_zone)->addMonths(2));
        })->whereHas('essentialsworkCard', function ($qu) {
        });



        return DataTables::of($contracts)
            ->addColumn(
                'worker_name',
                function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                }
            )
            ->addColumn(
                'residency',
                function ($row) {
                    return $row->OfficialDocument->number;
                }
            )
            ->addColumn(
                'work_card_no',
                function ($row) {
                    return $row->essentialsworkCard->work_card_no;
                }
            )
            ->addColumn(
                'project',
                function ($row) {
                    return $row->assignedTo->name;
                }
            )
            ->addColumn(
                'customer_name',
                function ($row) {
                    return $row->assignedTo->contact->supplier_business_name;
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
