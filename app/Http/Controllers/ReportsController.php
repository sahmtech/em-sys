<?php

namespace App\Http\Controllers;

use App\Report;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Yajra\DataTables\Facades\DataTables;

class ReportsController extends Controller
{
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function landing()
    {
        $reports = Report::all();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (!($is_admin)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        if (!$is_admin) {
            $reports = Report::whereIn('id', $this->moduleUtil->allowedReports())->get();
        }
        $cardsOfReports = [];
        foreach ($reports as $report) {
            $cardsOfReports[] = [
                'id' => $report->id,
                'name' => $report->name,
                'link' => $report->link ? route($report->link) : '',
            ];
        }
        return view('reports.reports_landing_page')->with(compact('cardsOfReports'));
    }

    public function expired_residencies()
    {
        $today = today()->format('Y-m-d');
        $residencies = EssentialsOfficialDocument::with(['employee'])
            ->where('type', 'residence_permit')
            ->where('is_active', 1)

            ->whereDate('expiration_date', '<', $today)
            ->orderBy('id', 'desc')
            ->latest('created_at')
            ->get();

        if (request()->ajax()) {
            return DataTables::of($residencies)
                ->addColumn('worker_name', function ($row) {
                    return $row->employee?->first_name .
                        ' ' .
                        $row->employee?->last_name;
                })
                ->addColumn('residency', function ($row) {
                    return $row->number;
                })
                ->addColumn('project', function ($row) {
                    return $row->employee?->assignedTo?->contact
                        ->supplier_business_name ?? null;
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->employee->assignedTo?->contact
                        ->supplier_business_name ?? null;
                })
                ->addColumn('end_date', function ($row) {
                    return $row->expiration_date;
                })
                ->addColumn('action', '')

                ->removeColumn('id')
                ->rawColumns([
                    'worker_name',
                    'residency',
                    'project',
                    'end_date',
                    'action',
                ])
                ->make(true);
        }

        return view('reports.expired_residencies');
    }
}
