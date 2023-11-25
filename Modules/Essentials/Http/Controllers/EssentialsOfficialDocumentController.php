<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsOfficialDocument;

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

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
           
            abort(403, 'Unauthorized action.');
        }
        // $can_crud_official_documents = auth()->user()->can('essentials.crud_official_documents');
        // if (!$can_crud_official_documents) {
        //     error_log("2222");
        //     abort(403, 'Unauthorized action.');
        // }
        if (request()->ajax()) {
            $official_documents = EssentialsOfficialDocument::join('users as u', 'u.id', '=', 'essentials_official_documents.employee_id')
                ->where('u.business_id', $business_id)

                ->select([
                    'essentials_official_documents.id',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'essentials_official_documents.type',
                    'essentials_official_documents.status',
                    'essentials_official_documents.number',
                    'essentials_official_documents.expiration_date',
                ]);

            if (!empty(request()->input('user_id')) && request()->input('user_id') !== 'all') {
                $official_documents->where('essentials_official_documents.employee_id', request()->input('user_id'));
            }

            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $official_documents->where('essentials_official_documents.status', request()->input('status'));
            }

            if (!empty(request()->input('doc_type')) && request()->input('doc_type') !== 'all') {
                $official_documents->where('essentials_official_documents.type', request()->input('doc_type'));
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $official_documents->whereDate('essentials_official_documents.expiration_date', '>=', $start)
                    ->whereDate('essentials_official_documents.expiration_date', '<=', $end);
            }
            if (!empty(request()->isForHome)) {
                $official_documents->where('essentials_official_documents.type', 'residence_permit');
            }
            return Datatables::of($official_documents)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                        $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        return view('essentials::employee_affairs.official_docs.index')->with(compact('users'));
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(
                ['employees2',
                 'doc_type',
                'doc_number',
                 'issue_date',
                 'issue_place',
                  'status',
                 'expiration_date',
                 'file']);

           
            $input2['type'] = $input['doc_type'];
            $input2['number'] = $input['doc_number'];
            $input2['issue_date'] = $input['issue_date'];
            $input2['expiration_date'] = $input['expiration_date'];
            $input2['employee_id'] =  $request->input('employees2');
            $input2['status'] = $input['status'];
            $input2['issue_place'] = $input['issue_place'];
            if (request()->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('/officialDocuments');

                $input2['file_path'] = $filePath;
            }

          
            EssentialsOfficialDocument::create($input2);


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

        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        // return $output;
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
            abort(403, 'Unauthorized action.');
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
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }
        $doc = EssentialsOfficialDocument::where('essentials_official_documents.id', $id)
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
            ->firstOrFail();

        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        $all_users = $query->select(['id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name")])->get();
        $users = $all_users->pluck('full_name', 'id');
        $var = $users[$doc->employee_id];

        return view('essentials::employee_affairs.official_docs.edit')->with(compact('users', 'doc', 'var'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }


        try {

            $file2 = EssentialsOfficialDocument::where('id', $id)->get()[0]->file_path;
            $employee2 = EssentialsOfficialDocument::where('id', $id)->get()[0]->employee_id;

            $input = $request->only(['employee', 'doc_type', 'doc_number', 'issue_date', 'issue_place', 'status', 'expiration_date', 'file']);

            $input2['type'] = $input['doc_type'];
            $input2['number'] = $input['doc_number'];
            $input2['issue_date'] = $input['issue_date'];
            $input2['expiration_date'] = $input['expiration_date'];

            $input2['status'] = $input['status'];
            $input2['issue_place'] = $input['issue_place'];
            if ($input['employee'] == null) {
                $input2['file_path'] = $employee2;
            } else {
                $input2['employee_id'] = $input['employee'];
            }
            if ($input['file'] == null) {
                $input2['file_path'] = $file2;
            } else {
                $file = request()->file('file');
                $filePath = $file->store('/officialDocuments');

                $input2['file_path'] = $filePath;
            }

            EssentialsOfficialDocument::where('id', $id)->update($input2);
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

        $query = User::where('business_id', $business_id)->where('users.user_type','!=' ,'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return redirect()->route('official_documents')->with(compact('users'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

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
