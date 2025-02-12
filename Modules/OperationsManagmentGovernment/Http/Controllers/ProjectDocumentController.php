<?php
namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use App\ProjectDocument;
use App\User;
use App\Utils\RequestUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;

class ProjectDocumentController extends Controller
{
    protected $requestUtil;
    public function __construct(RequestUtil $requestUtil)
    {

        $this->requestUtil = $requestUtil;
    }
    public function index(Request $request)
    {

        // Fetch sales projects with attachments and created_by details
        $sales_projects_data = ProjectDocument::with('salesProject', 'attachments', 'created_by')
            ->where('document_type', 'report')
            ->orderBy('id', 'desc')
            ->get();

        // Check if the request is an AJAX request (for DataTable)
        if ($request->ajax()) {

            return DataTables::of($sales_projects_data)
            // Format project name
                ->editColumn('name', function ($row) {
                    return $row?->salesProject?->name ?? '';
                })
                ->editColumn('created_by', function ($row) {
                    $user = User::find($row?->created_by);
                    return $user ? trim($user->first_name . ' ' . $user->mid_name . ' ' . $user->last_name) : '';
                })

            // Format note column
                ->editColumn('note', function ($row) {
                    return $row->note ?? '';
                })
            // Format attachments column
                ->editColumn('attachments', function ($row) {
                    if ($row->attachments->isNotEmpty()) {
                        return $row->attachments->map(function ($attachment) {
                            return '<a href="' . asset('storage/' . $attachment->file_path) . '" target="_blank" class="btn btn-primary btn-sm"
                                    style="padding: 8px 12px; margin: 4px; color: white; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                                    <i class="fas fa-file" style="margin-left: 5px;"></i> عرض ' . e($attachment->file_name) . '
                                </a>';
                        })->implode('<br>'); // Concatenate multiple attachments with line breaks
                    }
                    return '<span class="text-muted"><i class="fas fa-paperclip" style="margin-right: 15px;"></i>لا يوجد مرفق</span>'; // If no attachments, display this text with muted color
                })
            // Add action column for Edit/Delete buttons (if needed)
                ->addColumn('action', function ($row) {
                    if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('operationsmanagmentgovernment.delete_project_report')) {
                        return '<button class="btn btn-danger btn-sm delete_document_button" data-href="' . route('projects_documents.destroy', ['id' => $row->id]) . '" style="padding: 8px 12px; margin: 4px;">
                                <i class="fas fa-trash"></i> حذف
                            </button>';
                    }
                    return '';
                })

            // Enable raw HTML in 'action' and 'attachments' columns
                ->rawColumns(['action', 'attachments'])
                ->make(true); // Return the formatted data in JSON format
        }

        return view('operationsmanagmentgovernment::projectsdocuments.index'); // Return the view
    }

    // blueprint
    public function blueprintIndex(Request $request)
    {
        // Fetch sales projects with attachments and created_by details
        $sales_projects_data = ProjectDocument::with('salesProject', 'attachments', 'created_by')
            ->where('document_type', 'blueprint')
            ->orderBy('id', 'desc')
            ->get();

        // Check if the request is an AJAX request (for DataTable)
        if ($request->ajax()) {
            return DataTables::of($sales_projects_data)
            // Format project name
                ->editColumn('name', function ($row) {
                    return $row?->salesProject?->name ?? '';
                })
            // Format created_by column by fetching the user's first name
                ->editColumn('created_by', function ($row) {
                    $user = User::find($row?->created_by);
                    return $user ? trim($user->first_name . ' ' . $user->mid_name . ' ' . $user->last_name) : '';
                })
            // Format note column
                ->editColumn('note', function ($row) {
                    return $row->note ?? '';
                })
            // Format attachments column
                ->editColumn('attachments', function ($row) {
                    if ($row->attachments->isNotEmpty()) {
                        return $row->attachments->map(function ($attachment) {
                            return '<a href="' . asset('storage/' . $attachment->file_path) . '" target="_blank" class="btn btn-primary btn-sm"
                                    style="padding: 8px 12px; margin: 4px; color: white; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                                    <i class="fas fa-file" style="margin-left: 5px;"></i> عرض ' . e($attachment->file_name) . '
                                </a>';
                        })->implode('<br>'); // Concatenate multiple attachments with line breaks
                    }
                    return '<span class="text-muted"><i class="fas fa-paperclip" style="margin-right: 15px;"></i>لا يوجد مرفق</span>'; // If no attachments, display this text with muted color
                })

            // Add action column for Edit/Delete buttons (if needed)

                ->addColumn('action', function ($row) {
                    if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('operationsmanagmentgovernment.delete_project_report')) {
                        return '<button class="btn btn-danger btn-sm delete_document_button" data-href="' . route('projects_documents.destroy', ['id' => $row->id]) . '" style="padding: 8px 12px; margin: 4px;">
                                    <i class="fas fa-trash"></i> حذف
                                </button>';
                    }
                    return '';
                })
            // Enable raw HTML in 'action' and 'attachments' columns
                ->rawColumns(['action', 'attachments'])
                ->make(true); // Return the formatted data in JSON format
        }

        return view('operationsmanagmentgovernment::projectsdocuments.blueprint'); // Return the view
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $sales_projects = SalesProject::pluck('name', 'id')->toArray();

        return view('operationsmanagmentgovernment::projectsdocuments.create', compact('sales_projects'));
    }

    public function createBluePrint()
    {
        $sales_projects = SalesProject::pluck('name', 'id')->toArray();

        return view('operationsmanagmentgovernment::projectsdocuments.create_bluePrint', compact('sales_projects'));
    }

    public function edit()
    {
        return response()->json(['success' => false, 'msg' => 'لم يتم العثور على المستند']);

        $sales_projects = SalesProject::pluck('name', 'id')->toArray();

        return view('operationsmanagmentgovernment::projectsdocuments.create', compact('sales_projects'));
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
            // dd($request->all());
            $projectDocumentId = DB::table('projects_documents')->insertGetId([
                'sales_project_id' => $request->sales_project_id,
                'document_type'    => 'report',
                'note'             => $request->description,
                'created_by'       => auth()->user()->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            if ($request->hasFile('attachment')) {
                $attachments = [];
                $names       = $request->input('name');

                foreach ($request->file('attachment') as $index => $file) {
                    $filename = Str::random(10) . '-' . $file->getClientOriginalName();
                    $path     = $file->storeAs('documents', $filename, 'public');

                    $fileNameForAttachment = isset($names[$index]) ? $names[$index] : $filename;

                    $attachments[] = [
                        'project_document_id' => $projectDocumentId,
                        'file_name'           => $fileNameForAttachment,
                        'file_path'           => $path,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }

                DB::table('project_document_attachments')->insert($attachments);
            }

            $output = [
                'success' => 1,
                'msg'     => __('messages.added_success'),
            ];
            DB::commit();
            return redirect()->back()->with('success', $output['msg']);

        } catch (\Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg'     => __('messages.something_went_wrong'),
            ];
            return redirect()->back()->withErrors([$output['msg']]);
        }
    }

    public function storeBluePrint(Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($request->all());
            // إدخال البيانات في جدول project_documents بدون استخدام Model
            $projectDocumentId = DB::table('projects_documents')->insertGetId([
                'sales_project_id' => $request->sales_project_id,
                'document_type'    => 'blueprint',
                'note'             => $request->description,
                'created_by'       => auth()->user()->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            if ($request->hasFile('attachment')) {
                $attachments = [];
                $names       = $request->input('name');

                foreach ($request->file('attachment') as $index => $file) {
                    $filename = Str::random(10) . '-' . $file->getClientOriginalName();
                    $path     = $file->storeAs('documents', $filename, 'public');

                    $fileNameForAttachment = isset($names[$index]) ? $names[$index] : $filename;

                    $attachments[] = [
                        'project_document_id' => $projectDocumentId,
                        'file_name'           => $fileNameForAttachment,
                        'file_path'           => $path,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }

                DB::table('project_document_attachments')->insert($attachments);
            }

            $output = [
                'success' => 1,
                'msg'     => __('messages.added_success'),
            ];
            DB::commit();
            return redirect()->back()->with('success', $output['msg']);
        } catch (\Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg'     => __('messages.something_went_wrong'),
            ];
            return redirect()->back()->withErrors([$output['msg']]);
        }
    }

    public function destroy(Request $request, $id)
    {
        // Find the project document by its ID
        $projectDocument = ProjectDocument::find($id);

        // Check if the document exists
        if (! $projectDocument) {
            return response()->json(['success' => false, 'msg' => 'لم يتم العثور على المستند']);
        }

        // If attachments exist, delete them from storage
        if ($projectDocument->attachments->isNotEmpty()) {
            foreach ($projectDocument->attachments as $attachment) {
                // Check if the file exists before attempting to delete it
                if (Storage::exists('public/' . $attachment->file_path)) {
                    Storage::delete('public/' . $attachment->file_path);
                }
            }
        }

        // Delete the project document
        $projectDocument->delete();

        // Return a success response
        return response()->json(['success' => true, 'msg' => 'تم حذف المستند بنجاح']);
    }

}
