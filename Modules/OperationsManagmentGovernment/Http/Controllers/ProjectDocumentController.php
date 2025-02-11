<?php
namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use App\ProjectDocument;
use App\User;
use App\Utils\RequestUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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
        // جلب مشاريع المبيعات وترتيبها حسب الاسم (تصاعديًا)
        $sales_projects_data = ProjectDocument::with('salesProject', 'attachments', 'created_by')->orderBy('id', 'desc')->get();
        // dd($sales_projects);
        // $sales_projects = SalesProject::pluck('name', 'id')->toArray();

        if ($request->ajax()) {
            return DataTables::of($sales_projects_data)
                ->editColumn('name', function ($row) {
                    return $row?->salesProject?->name ?? '';
                })
                ->editColumn('name', function ($row) {
                    return $row?->salesProject?->name ?? '';
                })
                ->editColumn('created_by', function ($row) {
                    $user = User::find($row?->created_by);
                    return $user->first_name ?? '';
                })
                ->editColumn('note', function ($row) {
                    return $row->note ?? '';
                })

                ->addColumn('action', function ($row) {
                    return '';

                })
                ->filter(function ($query) use ($request) {
                    // هنا يمكنك إضافة عمليات تصفية إضافية إن رغبت
                })
                ->rawColumns(['action', 'name', 'note'])
                ->make(true);
        }

        return view('operationsmanagmentgovernment::projectsdocuments.index');
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
            // إدخال البيانات في جدول project_documents بدون استخدام Model
            $projectDocumentId = DB::table('projects_documents')->insertGetId([
                'sales_project_id' => $request->sales_project_id,
                'document_type'    => 'report',
                'note'             => $request->description,
                'created_by'       => auth()->user()->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // التحقق من وجود مرفقات
            if ($request->hasFile('attachment')) {
                $attachments = [];
                                                  // الحصول على مصفوفة الأسماء من الـ Request
                $names = $request->input('name'); // أو $request->name

                foreach ($request->file('attachment') as $index => $file) {
                    // إنشاء اسم عشوائي للملف
                    $filename = Str::random(10) . '-' . $file->getClientOriginalName();
                    $path     = $file->storeAs('documents', $filename, 'public'); // تخزين الملف في `storage/app/public/documents`

                    // الحصول على الاسم المقابل للمرفق الحالي من مصفوفة الأسماء
                    $fileNameForAttachment = isset($names[$index]) ? $names[$index] : $filename;

                    // تجهيز بيانات الإدراج للمرفق
                    $attachments[] = [
                        'project_document_id' => $projectDocumentId,
                        'file_name'           => $fileNameForAttachment,
                        'file_path'           => $path,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }

                // إدراج المرفقات دفعة واحدة في جدول project_document_attachments
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
}
