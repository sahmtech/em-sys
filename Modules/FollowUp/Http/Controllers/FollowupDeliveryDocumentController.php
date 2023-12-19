<?php

namespace Modules\FollowUp\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\FollowUp\Entities\FollowupDocument;
use Yajra\DataTables\Facades\DataTables;

class FollowupDeliveryDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $delivery_documents = FollowupDeliveryDocument::all();
        if (request()->ajax()) {

            if (!empty(request()->input('worker_id')) && request()->input('worker_id') !== 'all') {


                $delivery_documents = $delivery_documents->where('user_id', request()->input('worker_id'));
            }

            if (!empty(request()->input('document_id')) && request()->input('document_id') !== 'all') {


                $delivery_documents = $delivery_documents->where('document_id', request()->input('document_id'));
            }



            return DataTables::of($delivery_documents)


                ->editColumn('worker', function ($row) {
                    return $row->user->id_proof_number . ' - ' . $row->user->first_name . ' ' . $row->user->last_name  ?? '';
                })

                ->editColumn('doc_name', function ($row) {
                    return $row->document->name_ar ?? '';
                })
                ->editColumn('nots', function ($row) {
                    return $row->nots ?? '';
                })

                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('documents-delivery-edit', ['id' => $row->id])  . '"
                        data-href="' . route('documents-delivery-edit', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_document_delivery_button"  data-container="#edit_document_delivery_model"><i class="fas fa-edit cursor-pointer"></i>' . __("messages.edit") . '</a>
                    ';
                        $html .= '
                    <button data-href="' .  route('documents-delivery-delete', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_document_delivery_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>
                ';
                        $html .= '<a href="' . env('APP_URL') . '/uploads/' . $row->file_path . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> ' . __("messages.view") . '</a>
                &nbsp;';


                        return $html;
                    }
                )

                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'worker', 'doc_name'])
                ->make(true);
        }
        $workers = User::where('user_type', 'worker')->get();
        $documents = FollowupDocument::all();
        return view('followup::deliveryDocument.index',compact('workers','documents'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $workers = User::where('user_type', 'worker')->get();
        $documents = FollowupDocument::all();
        return view('followup::deliveryDocument.creat', compact('workers', 'documents'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // DB::beginTransaction();
        // try {
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filePath = $file->store('/documents');
        }
        FollowupDeliveryDocument::create([
            'user_id' => $request->input('user_id'),
            'document_id' => $request->input('document_id'),
            'file_path' => $filePath,
            'nots' => $request->input('nots'),
        ]);
        // DB::commit();
        return redirect()->back();
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     return redirect()->back();
        // }
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
        $documents = FollowupDocument::all();
        $document_delivery = FollowupDeliveryDocument::find($id);

        return view('followup::deliveryDocument.edit', compact('workers', 'documents', 'document_delivery'));
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
                $update_data['file_path']= $file->store('/documents');
            }
    
            $update_data['user_id'] = $request->input('user_id');
            $update_data['document_id'] = $request->input('document_id');
            $update_data['nots'] = $request->input('nots');
            
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