<?php

namespace Modules\Essentials\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\ViolationPenalties;
use Modules\Essentials\Entities\Violations;
use Yajra\DataTables\Facades\DataTables;

class ViolationsPenaltiesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $can_add_Violations = auth()->user()->can('essentials.add_Violations');
        $can_edit_Violations = auth()->user()->can('essentials.edit_Violations');
        $can_delete_Violations = auth()->user()->can('essentials.delete_Violations');



        $ViolationPenalties = ViolationPenalties::all();

        if (request()->ajax()) {


            return DataTables::of($ViolationPenalties)


                ->editColumn('type', function ($row) {
                    return __('essentials::lang.' . $row->type);
                })->editColumn('occurrence', function ($row) {
                    return __('essentials::lang.' . $row->occurrence);
                })->editColumn('amount_type', function ($row) {
                    return __('essentials::lang.' . $row->amount_type);
                })->editColumn('mainViolation', function ($row) {
                    return $row->violation->description;
                })->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_edit_Violations, $can_delete_Violations) {
                        $html = '';
                        if ($is_admin || $can_edit_Violations) {
                            $html .= '<a href="' . action([\Modules\Essentials\Http\Controllers\ViolationsPenaltiesController::class, 'edit'], ['id' => $row->id]) . '"
                        data-href="' . action([\Modules\Essentials\Http\Controllers\ViolationsPenaltiesController::class, 'edit'], ['id' => $row->id]) . ' "
                         class="btn btn-xs btn-modal btn-info edit_user_button"  data-container="#edit_violations"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>';
                            '&nbsp;';
                        }
                        if ($is_admin || $can_delete_Violations) {
                            $html .= '<button class="btn btn-xs btn-danger delete_violations_button" style="margin: 0px 5px;" data-href="' . route('delete-Violations', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )



                ->removeColumn('id')
                ->rawColumns(['action', 'mainViolation'])
                ->make(true);
        }


        $Violations = Violations::all();


        return view('essentials::Violations.index', compact('Violations'));
    }

    public function indexMain()
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $can_add_Main_Violations = auth()->user()->can('essentials.add_Main_Violations');
        $can_edit_Main_Violations = auth()->user()->can('essentials.edit_Main_Violations');
        $can_delete_Main_Violations = auth()->user()->can('essentials.delete_Main_Violations');



        $Violations = Violations::all();

        if (request()->ajax()) {


            return DataTables::of($Violations)

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_edit_Main_Violations, $can_delete_Main_Violations) {
                        $html = '';
                        if ($is_admin || $can_edit_Main_Violations) {
                            $html .= '<a href="' . action([\Modules\Essentials\Http\Controllers\ViolationsPenaltiesController::class, 'editMain'], ['id' => $row->id]) . '"
                        data-href="' . action([\Modules\Essentials\Http\Controllers\ViolationsPenaltiesController::class, 'editMain'], ['id' => $row->id]) . ' "
                         class="btn btn-xs btn-modal btn-info edit_user_button"  data-container="#edit_main-violations"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>';
                            '&nbsp;';
                        }
                        if ($is_admin || $can_delete_Main_Violations) {
                            $html .= '<button class="btn btn-xs btn-danger delete_violations_button" style="margin: 0px 5px;" data-href="' . route('delete-main-Violations', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )


                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }




        return view('essentials::MainViolations.index');
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
        // return $request;
        try {
            DB::beginTransaction();
            $business_id = request()->session()->get('user.business_id');
            $company_id = request()->session()->get('user.company_id');

            ViolationPenalties::create([
                'descrption' => $request->description,
                'occurrence' => $request->occurrence,
                'type' => "violation",
                'violation_id' => $request->violation_id,
                'amount_type' => $request->amount_type,
                'amount' => $request->amount ?? 0,
                'business_id' => $business_id,
                'company_id' => $company_id,
            ]);


            DB::commit();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
            return redirect()->back()->with('status', $output);
        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->back()->with('status', $output);
        }
    }


    public function storeMain(Request $request)
    {
        // return $request;
        try {
            DB::beginTransaction();
            $business_id = request()->session()->get('user.business_id');
            $company_id = request()->session()->get('user.company_id');

            Violations::create([
                'description' => $request->description,
                'business_id' => $business_id,
                'company_id' => $company_id,
            ]);


            DB::commit();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
            return redirect()->back()->with('status', $output);
        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->back()->with('status', $output);
        }
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
        $ViolationPenalties = ViolationPenalties::find($id);
        $Violations = Violations::all();


        return view('essentials::Violations.edit', compact('ViolationPenalties', 'Violations'));
    }


    public function editMain($id)
    {
        $Violations = Violations::find($id);
        return view('essentials::MainViolations.edit', compact('Violations'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $ViolationPenalties = ViolationPenalties::find($request->id);
        $ViolationPenalties->update([
            'descrption' => $request->description,
            'occurrence' => $request->occurrence,
            'type' => $request->type,
            'amount_type' => $request->amount_type,
            'amount' => $request->amount,
        ]);

        $output = [
            'success' => true,
            'msg' => __('lang_v1.updated_succesfully'),
        ];
        return redirect()->back()->with('status', $output);
    }


    public function updateMain(Request $request)
    {
        $Violations = Violations::find($request->id);
        $Violations->update([
            'description' => $request->description,

        ]);

        $output = [
            'success' => true,
            'msg' => __('lang_v1.updated_succesfully'),
        ];
        return redirect()->back()->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try {
            ViolationPenalties::find($id)->delete();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (Exception $e) {
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }


    public function destroyMain($id)
    {
        try {
            Violations::find($id)->delete();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (Exception $e) {
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }
}