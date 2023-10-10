<?php

namespace Modules\Essentials\Http\Controllers;


use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;


class EssentialsInsuranceCategoryController extends Controller
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
    
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_insurance_companies = auth()->user()->can('essentials.crud_insurance_companies');
        if (! $can_crud_insurance_companies) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $insuranceCompanies = DB::table('essentials_insurance_classes')->select([
                'id',
                'name',
            ]);

    
            // if (!empty(request()->input('user_id'))) {
            //     $official_documents->where('essentials_official_documents.employee_id', request()->input('user_id'));
            // }
    
            // if (!empty(request()->input('status'))) {
            //     $official_documents->where('essentials_official_documents.status', request()->input('status'));
            // }
    
            // if (!empty(request()->input('doc_type'))) {
            //     $official_documents->where('essentials_official_documents.type', request()->input('doc_type'));
            // }
    
            // if (!empty(request()->start_date) && !empty(request()->end_date)) {
            //     $start = request()->start_date;
            //     $end = request()->end_date;
            //     $official_documents->whereDate('essentials_official_documents.expiration_date', '>=', $start)
            //         ->whereDate('essentials_official_documents.expiration_date', '<=', $end);
            // }
    
            return Datatables::of($insuranceCompanies)
            //' . route('doc.view', ['id' => $row->id]) . '
            ->addColumn(
                'action',
                function ($row) {
                    $html = '';
                    $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>';
            
                    return $html;
                }
            )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name',"LIKE", "%{$keyword}%");
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('essentials::settings.partials.insurance_categories.index');
     }

   

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::create');
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
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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
