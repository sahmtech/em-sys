<?php

namespace Modules\FollowUp\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\Company;
use App\User;
use App\Utils\ModuleUtil;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\FollowUp\Entities\FollowupDocument;
use Yajra\DataTables\Facades\DataTables;

class FollowupDeliveryAttachmentController extends Controller
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
    public function index(Request $request)
    {

        //
        // $user = Company::whereHas('owner', function ($query) {
        //     $query->where('id', auth()->user()->company->id);
        // })->get();

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_followup_crud_attachment_delivery = auth()->user()->can('followup.crud_attachment_delivery');
        if (!($is_admin || $can_followup_crud_attachment_delivery)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $can_edit_attachment_delivery = auth()->user()->can('followup.edit_attachment_delivery');
        $can_delete_attachment_deliver = auth()->user()->can('followup.delete_attachment_delivery');
        $can_view_attachment_deliver = auth()->user()->can('followup.view_attachment_delivery');
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = $this->moduleUtil->applyAccessRole();
            $userIds = User::where('user_type', '!=', 'admin')
                ->where('company_id', auth()->user()->company->id)
                ->pluck('id')
                ->toArray();
        }

        $workersIds = User::whereIn('user_type', ['worker', 'employee'])->pluck('id')->toArray();

        // dd($workersIds);

        if (!$is_admin) {

            $workersIds = $this->moduleUtil->applyAccessRole();
            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
            $workersIds = User::whereIn('user_type', ['worker', 'employee'])
                ->whereIn('company_id', $companies_ids)
                ->pluck('id')
                ->toArray();

        }

        $delivery_documents = FollowupDeliveryDocument::whereIn('user_id', $workersIds)
            ->whereHas('attachment', function ($query) {
                $query->where('type', 'Attached');
            })
            ->get();

        $workers_have_docs_Ids = FollowupDeliveryDocument::whereIn('user_id', $workersIds)
            ->whereHas('attachment', function ($query) {
                $query->where('type', 'Attached');
            })
            ->pluck('user_id')
            ->toArray();

        $workers = User::whereIn('user_type', ['worker', 'employee'])
            ->whereIn('id', $workers_have_docs_Ids)
            ->get();

        if (request()->ajax()) {

            if (!empty(request()->input('worker_id')) && request()->input('worker_id') !== 'all') {

                $delivery_documents = $delivery_documents->where('user_id', request()->input('worker_id'));
            }

            if (!empty(request()->input('document_id')) && request()->input('document_id') !== 'all') {

                $delivery_documents = $delivery_documents->where('document_id', request()->input('document_id'));
            }

            return DataTables::of($delivery_documents)

                ->editColumn('worker', function ($row) {
                    return $row->user->id_proof_number . ' - ' . $row->user->first_name . ' ' . $row->user->mid_name . ' ' . $row->user->last_name ?? '';
                })

                ->editColumn('attach_name', function ($row) {
                    return $row->attachment->name_ar ?? '';
                })
                ->editColumn('nots', function ($row) {
                    return $row->nots ?? '';
                })
                ->editColumn('title', function ($row) {
                    return $row->title ?? '';
                })

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_attachment_delivery, $can_delete_attachment_deliver, $can_view_attachment_deliver) {

                        $html = '';

                        if (($is_admin || $can_edit_attachment_delivery)) {
                            $html .= '
                        <a href="' . route('attachments-delivery-edit', ['id' => $row->id]) . '"
                        data-href="' . route('attachments-delivery-edit', ['id' => $row->id]) . ' "
                         class="btn btn-xs btn-modal btn-info edit_document_delivery_button"  data-container="#edit_document_delivery_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        }
                        if (($is_admin || $can_delete_attachment_deliver)) {
                            $html .= '
                    <button data-href="' . route('attachments-delivery-delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_document_delivery_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        }
                        if (($is_admin || $can_view_attachment_deliver)) {
                            $html .= '<a href="' . asset('/uploads/' . $row->file_path) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> ' . __("messages.view") . '</a>
                &nbsp;';
                        }
                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'worker', 'attach_name'])
                ->make(true);
        }

        $documents = FollowupDocument::where('type', 'Attached')->get();

        return view('followup::deliveryAttachment.index', compact('workers', 'documents'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $workers = User::whereIn('user_type', ['worker', 'employee'])->get();
        $documents = FollowupDocument::where('type', 'Attached')->get();

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin)) {
            $workers = [];
            $workers = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
            $workers = User::whereIn('user_type', ['worker', 'employee'])->whereIn('company_id', $companies_ids)->get();

        }
        return view('followup::deliveryAttachment.creat', compact('workers', 'documents'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {

            $input = $request->only(
                [
                    'document',
                    'user_id',
                    'document_id',
                    'file_path',
                    'title',
                    'nots',

                ]
            );

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filePath = $file->store('/documents');
                $input2['file_path'] = $filePath;
            }
            $input2['user_id'] = $input['user_id'];
            $input2['document_id'] = $input['document_id'];
            $input2['title'] = $request->input('title');
            $input2['nots'] = $input['nots'];

            FollowupDeliveryDocument::create($input2);

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

        return redirect()->route('attachments-delivery')->with($output);
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
        $workers = User::whereIn('user_type', ['worker', 'employee'])->get();
        $documents = FollowupDocument::where('type', 'Attached')->get();

        $document_delivery = FollowupDeliveryDocument::find($id);

        return view('followup::deliveryAttachment.edit', compact('workers', 'documents', 'document_delivery'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $update_data = [];
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $update_data['file_path'] = $file->store('/documents');
            }

            $update_data['user_id'] = $request->input('user_id');
            $update_data['document_id'] = $request->input('document_id');
            $update_data['nots'] = $request->input('nots');

            if ($update_data['document_id'] != 11) {
                $update_data['title'] = null;
            } else {
                $update_data['title'] = $request->input('title');
            }

            $document_delivery = FollowupDeliveryDocument::find($id);
            $document_delivery->update($update_data);

            DB::commit();
            return redirect()->back();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            try {
                FollowupDeliveryDocument::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف السند بنجاح',
                ];
            } catch (Exception $e) {
                return redirect()->back();
            }
            return $output;
        }
    }
}
