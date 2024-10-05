<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\Penalties;
use Modules\Essentials\Entities\ViolationPenalties;
use Modules\Essentials\Utils\EssentialsUtil;
use Yajra\DataTables\Facades\DataTables;

class PenaltiesController extends Controller
{
    protected $moduleUtil;

    protected $essentialsUtil;

    protected $commonUtil;

    protected $transactionUtil;

    protected $businessUtil;

    protected $requestUtil;


    protected $newArrivalUtil;


    public function __construct(ModuleUtil $moduleUtil,  EssentialsUtil $essentialsUtil, Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
        $this->commonUtil = $commonUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $can_add_penalties = auth()->user()->can('essentials.add_penalties');
        $can_edit_penalties = auth()->user()->can('essentials.edit_penalties');
        $can_delete_penalties = auth()->user()->can('essentials.delete_penalties');



        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $penalties = Penalties::where('business_id', $business_id)->get();

        if (request()->ajax()) {


            return DataTables::of($penalties)


                ->editColumn('user', function ($row) {
                    return $row->user->first_name  . ' ' . $row->user->last_name . ' - ' . $row->user->id_proof_number;
                })->editColumn('added_by', function ($row) {
                    return $row->addedBy->first_name  . ' ' . $row->addedBy->last_name;
                })->editColumn('penalties', function ($row) {
                    return $row->violationPenalties->descrption . ' - ' . $row->violationPenalties?->violation?->description . ' - ' . __('essentials::lang.' . $row->violationPenalties->occurrence) . '-' . __('essentials::lang.' . $row->violationPenalties->amount_type) . '' . ($row->violationPenalties->amount > 0 ? ' - ' . $row->violationPenalties->amount : '');
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1 ? __('essentials::lang.Implemented') : __('essentials::lang.Not implemented');
                })
                ->editColumn('application_date', function ($row) {
                    return Carbon::parse($row->application_date)->format('m/Y');
                })

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_edit_penalties, $can_delete_penalties) {
                        $html = '';
                        if ($is_admin || $can_edit_penalties) {
                            if ($row->status != 1) {
                                $html .= '<a href="' . action([\Modules\Essentials\Http\Controllers\PenaltiesController::class, 'edit'], ['id' => $row->id]) . '"
                                data-href="' . action([\Modules\Essentials\Http\Controllers\PenaltiesController::class, 'edit'], ['id' => $row->id]) . ' "
                                 class="btn btn-xs btn-modal btn-info edit_user_button"  data-container="#edit_violations"><i class="fas fa-edit cursor-pointer"></i></a>';
                                '';
                            }
                        }
                        if ($is_admin || $can_delete_penalties) {
                            $html .= '<button class="btn btn-xs btn-danger delete_violations_button" style="margin: 0px 5px;" data-href="' . route('delete-penalties', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> </button>';
                        }
                        if (!empty($row->file_path)) {
                            $html .= '<button class="btn btn-xs btn-info " style=" "  onclick="window.location.href = \'/uploads/' . $row->file_path . '\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                            '&nbsp;';
                        }

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->rawColumns(['user', 'added_by', 'penalties', 'action', 'status', 'application_date'])
                ->make(true);
        }


        // 
        $users = User::whereIn('id', $userIds)->whereIn('user_type', ['employee', 'worker'])->get();
        $ViolationPenalties = ViolationPenalties::where('business_id', $business_id)->get();
        return view('essentials::Penalties.index', compact('users', 'ViolationPenalties'));
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
        try {
            $business_id = request()->session()->get('user.business_id');
            $company_id = request()->session()->get('user.company_id');
            $path_file = null;
            if ($request->hasFile('violation_file')) {
                $attachment = $request->file('violation_file');
                $attachment_name = $attachment->store('/penalties');
                $path_file = $attachment_name;
            }

            DB::beginTransaction();
            Penalties::create([
                'user_id' => $request->user_id,
                'added_by' => Auth::user()->id,
                'violation_penalties_id' => $request->violation_penalties_id,
                'business_id' => $business_id,
                'company_id' => $company_id,
                'application_date' => $request->application_date,
                'file_path' => $path_file,
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

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $users = User::whereIn('id', $userIds)->whereIn('user_type', ['employee', 'worker'])->get();
        $ViolationPenalties = ViolationPenalties::where('business_id', $business_id)->get();
        $Penalties = Penalties::find($id);

        return view('essentials::Penalties.edit', compact('users', 'ViolationPenalties', 'Penalties'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        // return $request;
        try {


            $Penalties = Penalties::find($request->id);
            $path_file = null;
            if ($request->hasFile('violation_file')) {
                $attachment = $request->file('violation_file');
                $attachment_name = $attachment->store('/penalties');
                $path_file = $attachment_name;
            }

            DB::beginTransaction();
            $Penalties->update([
                'user_id' => $request->user_id,
                'violation_penalties_id' => $request->violation_penalties_id,
                'file_path' => $path_file,
                'application_date' => $request->application_date,
            ]);

            DB::commit();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_succesfully'),
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
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try {
            Penalties::find($id)->delete();
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