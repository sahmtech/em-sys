<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Yajra\DataTables\Facades\DataTables;
use Modules\Sales\Entities\SalesSalariesRequest;

class IRsalaryRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $can_crud_sales_salary_requests = auth()->user()->can('sales.crud_sales_salary_requests');

        if (!$can_crud_sales_salary_requests) {
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();


        $salary_requests = salesSalariesRequest::all();

        if (request()->ajax()) {


            return Datatables::of($salary_requests)
                ->editColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->profession_id] ?? '';

                    return $item;
                })
                ->editColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->specialization_id] ?? '';

                    return $item;
                })
                ->addColumn('file', function ($row) {
                    if ($row->file) {
                        return '<a href="' . asset('uploads/' . $row->file) . '" target="_blank">' . __('sales::lang.View_File') . '</a>';
                    }
                    return '';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if (!$row->salary) {
                            $html .= '<button class="btn btn-primary" data-toggle="modal" data-target="#addSalaryModal" data-id="' . $row->id . '">Add Salary</button>';
                        } else {
                            $html .= __('sales::lang.answered_with_salary') . $row->salary;
                        }

                        return $html;
                    }
                )

                ->rawColumns(['file', 'action'])
                ->make(true);
        }



        return view('internationalrelations::salaryRequest.index');
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('internationalrelations::create');
    }
    public function updateSalary(Request $request)
    {
        // Validate the request
        $request->validate([
            'sales_id' => 'required|integer',
            'salary' => 'required|numeric',
        ]);
        try {
            salesSalariesRequest::where('id', $request->sales_id)
                ->update([
                    'salary' => $request->salary,
                    'updated_at' => now(),
                ]);
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
        return redirect()->back()->with('status', $output);
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
        return view('internationalrelations::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
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
