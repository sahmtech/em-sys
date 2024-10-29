<?php

namespace Modules\FollowUp\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\FollowUp\Entities\FollowupDocument;
use Modules\FollowUp\Entities\FollowupUserAccessProject;
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_followup_crud_attachment_delivery = auth()->user()->can('followup.crud_attachment_delivery');
        if (!($is_admin || $can_followup_crud_attachment_delivery)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $can_edit_attachment_delivery = auth()->user()->can('followup.edit_attachment_delivery');
        $can_delete_attachment_deliver = auth()->user()->can('followup.delete_attachment_deliver');
        $can_view_attachment_deliver = auth()->user()->can('followup.view_attachment_deliver');
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';

        $workers = User::where('user_type', 'worker')->get();
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id', auth()->user()->id)->pluck('sales_project_id');
            $workers = User::where('user_type', 'worker')->whereIn('assigned_to', $followupUserAccessProject)->get();
        }

        $delivery_documents = FollowupDeliveryDocument::whereIn('user_id', $userIds)
            ->whereHas('attachment', function ($query) {
                $query->where('type', 'Attached');
            })
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
                    return $row->user->id_proof_number . ' - ' . $row->user->first_name . ' ' . $row->user->last_name ?? '';
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
        $workers = User::where('user_type', 'worker')->get();
        $documents = FollowupDocument::where('type', 'Attached')->get();

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id', auth()->user()->id)->pluck('sales_project_id');
            $workers = User::where('user_type', 'worker')->whereIn('assigned_to', $followupUserAccessProject)->get();
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
        $workers = User::where('user_type', 'worker')->get();
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
