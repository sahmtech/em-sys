<?php
namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use App\Contact;
use App\ProjectDepartment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProjectDepartmentController extends Controller
{

    public function index(Request $request)
    {
        $project_departments = ProjectDepartment::orderBy('id', 'desc')->with('contact', 'project');

        if ($request->ajax()) {
            return DataTables::of($project_departments->get())
                ->editColumn('name_ar', function ($row) {
                    $name = $row->name_ar ?? '';
                    return $name;
                })
                ->editColumn('name_en', function ($row) {
                    $name = $row->name_ar ?? '';
                    return $name;
                })
                ->editColumn('contact', function ($row) {
                    $name = $row->contact?->supplier_business_name ?? '';
                    return $name;
                })
                ->editColumn('project', function ($row) {
                    $name = $row->project->name ?? '';
                    return $name;
                })
                ->addColumn('action', function ($row) {
                    if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('operationsmanagmentgovernment.delete_project_department')) {
                        return '<button class="btn btn-danger btn-sm delete_document_button" data-href="' . route('project_departments.destroy', ['id' => $row->id]) . '" style="padding: 8px 12px; margin: 4px;">
                                    <i class="fas fa-trash"></i> حذف
                                </button>';
                    }
                    return '';
                })

                ->rawColumns(['name_ar', 'name_en', 'contact', 'project', 'action'])
                ->make(true);
        }

        return view('operationsmanagmentgovernment::project_departments.index');
    }

    public function create()
    {
        $contacts = Contact::whereNotNull('supplier_business_name')
            ->pluck('supplier_business_name', 'id');

        return view('operationsmanagmentgovernment::project_departments.create', compact('contacts'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            ProjectDepartment::create([
                'name_ar'          => $request->name_ar,
                'name_en'          => $request->name_en ?? null,
                'contact_id'       => $request->contact_id,
                'sales_project_id' => $request->project_id,
            ]);
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

    public function edit(ProjectDepartment $projectDepartment)
    {
        return view('project_departments.edit', compact('projectDepartment'));
    }

    public function update(Request $request, ProjectDepartment $projectDepartment)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $projectDepartment->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('project_departments.index');
    }

    public function destroy(Request $request, $id)
    {

        // Find the project ProjectDepartment by its ID
        $project_department = ProjectDepartment::find($id);

        // Check if the ProjectDepartment exists
        if (! $project_department) {
            return response()->json(['success' => false, 'msg' => 'لم يتم العثور على قسم المشروع']);
        }

        // Delete the project document
        $project_department->delete();

        // Return a success response
        return response()->json(['success' => true, 'msg' => 'تم حذف قسم المشروع بنجاح']);
    }
}
