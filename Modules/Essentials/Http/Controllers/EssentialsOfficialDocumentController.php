<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Support\Facades\Auth;

class EssentialsOfficialDocumentController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_crud_official_documents = auth()->user()->can('essentials.crud_official_documents');
        if (!$can_crud_official_documents) {
            error_log("2222");
        }
        $can_add_official_documents = auth()->user()->can('essentials.add_official_documents');
        $can_edit_official_documents = auth()->user()->can('essentials.edit_official_documents');
        $can_delete_official_documents = auth()->user()->can('essentials.delete_official_documents');
        $can_show_official_documents = auth()->user()->can('essentials.show_official_documents');


        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }


        $official_documents = EssentialsOfficialDocument::leftjoin('users as u', 'u.id', '=', 'essentials_official_documents.employee_id')
            ->whereIn('u.id',  $userIds)

            ->where(function ($query) {
                $query->where('u.status', 'active')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('u.status', 'inactive')
                            ->whereIn('u.sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })

            // ->where('essentials_official_documents.is_active', 1)
            ->select([
                'essentials_official_documents.id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.mid_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'essentials_official_documents.type',
                'essentials_official_documents.is_active',
                'essentials_official_documents.file_path',
                'essentials_official_documents.issue_date',
                'essentials_official_documents.issue_place',
                'essentials_official_documents.number',
                'essentials_official_documents.expiration_date',
                'u.user_type',
                'u.id_proof_number as id_proof_number',

                'u.border_no',
            ])
            ->orderby('essentials_official_documents.id', 'desc');




        if (request()->ajax()) {
            if (!empty(request()->input('user_id')) && request()->input('user_id') !== 'all') {
                $official_documents->where('essentials_official_documents.employee_id', request()->input('user_id'));
            }
            if (!empty(request()->input('user_type')) && request()->input('user_type') !== 'all') {
                $official_documents->where('u.user_type', request()->input('user_type'));
            }
            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                if (request()->input('status') == 'valid') {
                    $official_documents->where('essentials_official_documents.is_active', 1);
                }
                if (request()->input('status') == 'expired') {
                    $official_documents->where('essentials_official_documents.is_active', 0);
                }
            }

            if (!empty(request()->input('doc_type')) && request()->input('doc_type') !== 'all') {
                $official_documents->where('essentials_official_documents.type', request()->input('doc_type'));
            }
            if (!empty(request()->input('doc_exists')) && request()->input('doc_exists') !== 'all') {
                if (request()->input('doc_exists') == "exists") {
                    $official_documents->whereNotNull('file_path');
                } else {
                    $official_documents->whereNull('file_path');
                }
            }

            // if (!empty(request()->start_date) && !empty(request()->end_date)) {
            //     $start = request()->start_date;
            //     $end = request()->end_date;
            //     $official_documents->whereDate('essentials_official_documents.expiration_date', '>=', $start)
            //         ->whereDate('essentials_official_documents.expiration_date', '<=', $end);
            // }

            $start_date = request()->get('start_date');
            $end_date = request()->get('end_date');

            if (!is_null($start_date)) {
                $official_documents->whereDate('essentials_official_documents.expiration_date', '>=', $start_date);
            }

            if (!is_null($end_date)) {
                $official_documents->whereDate('essentials_official_documents.expiration_date', '<=', $end_date);
            }
            if (!empty(request()->isForHome)) {
                $official_documents->where('essentials_official_documents.type', 'residence_permit');
            }


            return Datatables::of($official_documents)
                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_edit_official_documents, $can_delete_official_documents, $can_show_official_documents) {
                        $html = '';
                        if ($is_admin || $can_edit_official_documents) {
                            $html .= '<button class="btn btn-xs btn-primary open-edit-modal" data-id="' . $row->id . '" data-url="' . route('official_documents.edit', ['docId' => $row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                        }

                        if ($is_admin || $can_show_official_documents) {
                            if ($row->file_path) {
                                $html .= ' &nbsp; <button class="btn btn-xs btn-info btn-modal view_doc_file_modal" data-id="' . $row->id . '" data-href="/uploads/' . $row->file_path . '"> ' . __('essentials::lang.doc_file') . '</button>  &nbsp;';
                            } else {
                                $html .= ' &nbsp; <button class="btn btn-xs btn-secondary btn-modal view_doc_file_modal" data-id="' . $row->id . '" > ' . __('essentials::lang.doc_file') . '</button>  &nbsp;';
                            }
                        }
                        if ($is_admin || $can_delete_official_documents) {
                            $html .= '&nbsp; <button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }



                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.mid_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->where("u.id_proof_number", ["%{$keyword}%"]);
                })

                ->rawColumns(['action'])
                ->make(true);
        }
        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        return view('essentials::employee_affairs.official_docs.index')->with(compact('users'));
    }


    public function storeDocFile(Request $request)
    {
        try {
            if (request()->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('/officialDocuments');
                EssentialsOfficialDocument::where('id', $request->doc_id)->update([
                    'file_path' => $filePath,
                    'updated_by' => Auth::user()->id
                ]);
            } else if (request()->input('delete_file') == 1) {
                EssentialsOfficialDocument::where('id', $request->doc_id)->update([
                    'file_path' => Null,
                    'updated_by' => Auth::user()->id
                ]);
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
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
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(
                [
                    'employees2',
                    'doc_type',
                    'doc_number',
                    'issue_date',
                    'issue_place',
                    'expiration_date',
                    'file',
                    'scannedDoc'

                ]
            );

            $previous_doc = EssentialsOfficialDocument::where('employee_id', $request->input('employees2'))
                ->where('type', $input['doc_type'])->where('is_active', 1)->first();
            if ($previous_doc) {
                $previous_doc->is_active = 0;
                $previous_doc->status = 'expired';
                $previous_doc->save();
            }

            $input2['type'] = $input['doc_type'];
            $input2['number'] = $input['doc_number'];
            $input2['issue_date'] = $input['issue_date'];
            $input2['expiration_date'] = $input['expiration_date'];
            $input2['employee_id'] =  $request->input('employees2');
            $input2['issue_place'] = $input['issue_place'];
            $input2['is_active'] = 1;
            $input2['status'] = 'valid';
            $input2['created_by'] = Auth::user()->id;
            if ($request->has('image')) {
                $image = $request->input('image');

                $file = base64_decode($image);

                $filename = 'scanned_document_' . time() . '.png';

                $filePath = 'officialDocuments/' . $filename;

                file_put_contents(storage_path('app/public/' . $filePath), $file);


                $input2['file_path'] = $filePath;
            } else  if (request()->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('/officialDocuments');
                $input2['file_path'] = $filePath;
            }

            $doc = EssentialsOfficialDocument::create($input2);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return redirect()->route('official_documents')->with(compact('users'));
    }





    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (!auth()->user()->can('user.view')) {
        }

        $business_id = request()->session()->get('user.business_id');

        $doc = EssentialsOfficialDocument::findOrFail($id);

        $user = User::where('id', $doc->employee_id)->first();

        return view('essentials::employee_affairs.official_docs.show')->with(compact('doc', 'user'));
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($docId)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $doc = EssentialsOfficialDocument::where('essentials_official_documents.id', $docId)
            ->join('users as u', 'u.id', '=', 'essentials_official_documents.employee_id')
            ->select([
                'essentials_official_documents.id as id',
                'essentials_official_documents.employee_id as employee_id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as employee"),
                'essentials_official_documents.type as type',
                'essentials_official_documents.status as status',
                'essentials_official_documents.number as number',
                'essentials_official_documents.issue_date as issue_date',
                'essentials_official_documents.issue_place as issue_place',
                'essentials_official_documents.expiration_date as expiration_date',
                'essentials_official_documents.file_path as file_path',
            ])
            ->first();



        return response()->json(['doc' => $doc]);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $docId = $request->docId;

            $input2['expiration_date'] = $request->expiration_date;
            $input2['status'] = $request->status;
            $input2['updated_by'] = Auth::user()->id;
            $input2['type'] = $request->doc_type;
            $input2['number'] = $request->doc_number;
            $input2['issue_date'] = $request->issue_date;
            $input2['issue_place'] = $request->issue_place;
            EssentialsOfficialDocument::where('id', $docId)->update($input2);
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsOfficialDocument::where('id', $id)
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
}
