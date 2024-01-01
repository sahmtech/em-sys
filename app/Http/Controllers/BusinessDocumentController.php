<?php

namespace App\Http\Controllers;

use App\BusinessDocument;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BusinessDocumentController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function show($business_id)
    {


        if (!auth()->user()->can('business_documents.view')) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $auth_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $auth_id);
        if (request()->ajax()) {
            $business = BusinessDocument::where('business_id', $business_id)
                ->select(['id', 'business_id', 'licence_type', 'licence_number', 'licence_date', 'renew_date', 'expiration_date', 'issuing_location', 'path_file', 'details', 'unified_number']);


            return Datatables::of($business)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            // if (!empty($row->path_file)) { 
                            //     $html .= '<a href="' . env('APP_URL') . '/uploads/' . $row->path_file . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> ' . __('essentials::lang.doc_view') . '</a>
                            //     &nbsp;';
                            // }

                            if (!empty($row->path_file)) {
                                $html .= '<button class="btn btn-xs btn-primary"  onclick="window.location.href = \'/uploads/' . $row->path_file . '\'"><i class="fa fa-eye"></i> ' . __('essentials::lang.doc_view') . '</button>';
                            } else {
                                $html .= '<a class="btn btn-xs btn-warning">' . __('essentials::lang.no_file_path') . '</a>';
                            }

                            $html .= '<button class="btn btn-xs btn-info btn-modal edit_doc_button" data-container="#edit_docs_model" data-href="' . route('doc.edit', ['id' => $row->id]) . '" style="margin: 2px;"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                            $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('doc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->removeColumn('id')
                ->removeColumn('path_file')
                ->rawColumns(['action'])
                ->make(true);
        }



        return view('essentials::bussines_manage.docsView')->with(compact('business_id'));
    }

    public function store(Request $request)
    {

        // dd($request->all());
        $businessDocument = new BusinessDocument();
        $businessDocument->licence_type = $request->input('licence_type');
        $businessDocument->licence_number = $request->input('licence_number');
        $businessDocument->business_id = $request->input('business_id');
        $businessDocument->licence_date = $request->input('licence_date');
        $businessDocument->renew_date = $request->input('renew_date');
        $businessDocument->expiration_date = $request->input('expiration_date');
        $businessDocument->issuing_location = $request->input('issuing_location');
        $businessDocument->details = $request->input('details');
        $businessDocument->unified_number = $request->input('unified_number');


        // if ($request->input('licence_type') === 'COMMERCIALREGISTER') {
        //     $businessDocument->unified_number = $request->unified_number;
        // }
        // else {$businessDocument->unified_number=Null;
        // }

        if ($request->input('licence_type') === 'memorandum_of_association') {
            $businessDocument->capital = $request->input('capital');
        } else {
            $businessDocument->capital = null;
        }


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('/business_documents');
            $businessDocument->path_file = $filePath;
        }


        $businessDocument->save();
        // dd($businessDocument);
        return redirect()->route('business_documents.view', ['id' => $request->business_id])->with('success', 'Business document added successfully');
    }


    public function destroy($id)
    {
        if (!auth()->user()->can('business_documents.destroy')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            BusinessDocument::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function edit($id)
    {
        if (!auth()->user()->can('business_documents.view')) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $auth_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $auth_id);

        $busines = BusinessDocument::find($id);
        
        return view('essentials::bussines_manage.edit_docs', compact('busines'));
    }



    public function update(Request $request, $id)
    {
        $businessDocument = BusinessDocument::find($id);
        $businessDocument->licence_type = $request->input('licence_type');
        $businessDocument->licence_number = $request->input('licence_number');
        $businessDocument->business_id = $request->input('business_id');
        $businessDocument->licence_date = $request->input('licence_date');
        $businessDocument->renew_date = $request->input('renew_date');
        $businessDocument->expiration_date = $request->input('expiration_date');
        $businessDocument->issuing_location = $request->input('issuing_location');
        $businessDocument->details = $request->input('details');
        $businessDocument->unified_number = $request->input('unified_number');


        if ($request->input('licence_type') === 'memorandum_of_association') {
            $businessDocument->capital = $request->input('capital');
        } else {
            $businessDocument->capital = null;
        }


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('/business_documents');
            $businessDocument->path_file = $filePath;
        }


        $businessDocument->save();
       
        return redirect()->back()
            ->with('status', [
                'success' => true,
                'msg' => 'تم التعديل بنجاح',
            ]);

    }
}