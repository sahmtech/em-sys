<?php

namespace Modules\FollowUp\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FollowUp\Entities\FollowupDocument;
use Yajra\DataTables\Facades\DataTables;

class FollowupAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_crud_attachments = auth()->user()->can('followup.crud_attachments');
        if (!($is_admin || $can_followup_crud_attachments)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $documents = FollowupDocument::where('type', 'Attached')->get();
        if (request()->ajax()) {

            $can_edit_attachment = auth()->user()->can('followup.edit_attachment');
            $can_attachments_delete = auth()->user()->can('followup.attachments.delete');
            return DataTables::of($documents)

                ->editColumn('name', function ($row) {
                    return $row->name_ar . ' - ' . $row->name_en ?? '';
                })

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_attachment, $can_attachments_delete) {

                        $html = '';
                        if (($is_admin || $can_edit_attachment)) {
                            $html .= '
                        <a href="' . route('documents-edit', ['id' => $row->id]) . '"
                        data-href="' . route('documents-edit', ['id' => $row->id]) . ' "
                         class="btn btn-xs btn-modal btn-info edit_document_button"  data-container="#edit_document_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        }
                        if (($is_admin || $can_attachments_delete)) {
                            $html .= '
                    <button data-href="' . route('documents-delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_document_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        }

                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'name'])
                ->make(true);
        }
        return view('followup::attachment.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('followup::attachment.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            FollowupDocument::create(
                [
                    'name_ar' => $request->input('name_ar'),
                    'name_en' => $request->input('name_en'),
                    'type' => 'Attached',
                ]
            );

            DB::commit();
            return redirect()->back();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
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
        $attachment = FollowupDocument::find($id);
        return view('followup::attachment.edit', compact('attachment'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $attachment = FollowupDocument::find($id);
            $attachment->update(
                [
                    'name_ar' => $request->input('name_ar'),
                    'name_en' => $request->input('name_en'),
                ]
            );

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
                FollowupDocument::find($id)->delete();
                $output = [
                    'success' => true,
                    'msg' => 'تم حذف  المرفق بنجاح',
                ];
            } catch (Exception $e) {
                return redirect()->back();
            }
            return $output;
        }
    }
}
