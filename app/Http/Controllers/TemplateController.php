<?php

namespace App\Http\Controllers;

use App\Template;
use App\TemplateSection;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TemplateController extends Controller
{


    public function index()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_edit_templates = auth()->user()->can('sales.edit_templates') ? true : false;
        $can_delete_templates = auth()->user()->can('sales.delete_templates') ? true : false;
        $templates = Template::orderBy('id', 'desc');
        if (request()->ajax()) {
            return Datatables::of($templates)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_templates, $can_delete_templates) {
                        $html = '';
                        $html .= '<a href="' . route('templates.show', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> ' . __('messages.view') . '</a>
                            &nbsp;';

                        if ($is_admin || $can_edit_templates) {
                            $html .= '<a href="' . route('templates.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                            &nbsp;';
                        }


                        if ($is_admin || $can_delete_templates) {
                            $html .= '<button class="btn btn-xs btn-danger delete_template_button" data-href="' . route('templates.delete', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                ->make(true);
        }
        return view('templates.index');
    }

    public function create()
    {
        // Return the view to create a new template
        return view('templates.create');
    }


    public function store(Request $request)
    {
        try {
            // $request->validate([
            //     'name' => 'required|string|max:255',
            //     'primary_header' => 'nullable|string|max:255',
            //     'secondary_header' => 'nullable|string|max:255',
            //     'sections.*.header_left' => 'nullable|string|max:255',
            //     'sections.*.header_right' => 'nullable|string|max:255',
            //     'sections.*.header_color' => 'nullable|string|size:7',
            //     'sections.*.content_left' => 'required|string',
            //     'sections.*.content_right' => 'required|string',
            //     'sections.*.order' => 'required|integer|min:1',
            // ]);

            // Create the template
            $template = new Template();
            $template->name = $request->name;
            $template->primary_header = $request->primary_header;
            $template->primary_footer = $request->primary_footer;
            // $template->secondary_header = $request->secondary_header;
            $template->header_color = $request->header_color ?? '#FFFFFF';
            $template->save();

            // Create the sections
            foreach ($request->sections as $sectionData) {
                $section = new TemplateSection();
                $section->template_id = $template->id;
                $section->header_left = $sectionData['header_left'] ?? null;
                $section->header_right = $sectionData['header_right'] ?? null;
                $section->header_color = $sectionData['header_color'] ?? '#FFFFFF';

                // Determine if it's a single-column or two-column section
                if (isset($sectionData['content'])) {
                    // Single-column section
                    $section->content = $sectionData['content'];
                } else {
                    // Two-column section
                    $section->content_left = $sectionData['content_left'];
                    $section->content_right = $sectionData['content_right'];
                }

                $section->order = $sectionData['order'];
                $section->save();
            }
            $output = [
                'success' => 1,
                'msg' => 'Template created successfully!',
            ];
        } catch (\Exception $e) {


            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('templates.index')
            ->with('status', $output);
    }


    public function edit($id)
    {
        $can_edit_templates = auth()->user()->can('sales.edit_templates') ? true : false;
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || $can_edit_templates)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $template = Template::with('sections')->findOrFail($id);
        return view('templates.edit')->with(compact('template'));
    }


    public function update(Request $request, $id)
    {
        try {
            // $request->validate([
            //     'name' => 'required|string|max:255',
            //     'primary_header' => 'nullable|string|max:255',
            //     'secondary_header' => 'nullable|string|max:255',
            //     'sections.*.header_left' => 'nullable|string|max:255',
            //     'sections.*.header_right' => 'nullable|string|max:255',
            //     'sections.*.header_color' => 'nullable|string|size:7',
            //     'sections.*.content_left' => 'required|string',
            //     'sections.*.content_right' => 'required|string',
            //     'sections.*.order' => 'required|integer|min:1',
            // ]);

            $template = Template::findOrFail($id);
            $template->update([
                'name' => $request->name,
                'header_color' => $request->header_color ?? '#FFFFFF',
                'primary_header' => $request->primary_header,
                'primary_footer' => $request->primary_footer,
            ]);

            TemplateSection::where('template_id', $id)->delete();

            foreach ($request->sections as $sectionData) {
                $section = new TemplateSection();
                $section->template_id = $template->id;
                $section->header_left = $sectionData['header_left'] ?? null;
                $section->header_right = $sectionData['header_right'] ?? null;
                $section->header_color = $sectionData['header_color'] ?? '#FFFFFF';

                // Determine if it's a single-column or two-column section
                if (isset($sectionData['content'])) {
                    // Single-column section
                    $section->content = $sectionData['content'];
                } else {
                    // Two-column section
                    $section->content_left = $sectionData['content_left'];
                    $section->content_right = $sectionData['content_right'];
                }

                $section->order = $sectionData['order'];
                $section->save();
            }

            $output = [
                'success' => 1,
                'msg' => 'Template created successfully!',
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('templates.index')
            ->with('status', $output);
    }


    public function show($id)
    {
        $template = Template::with('sections')->findOrFail($id);

        // Sorting sections by their 'order' attribute if needed
        $sections = $template->sections->sortBy('order');

        return view('templates.show', compact('template', 'sections'));
    }


    public function print($id)
    {
        $template = Template::with('sections')->findOrFail($id);

        // Optionally, sort sections by 'order' if it's important for the print layout
        $sections = $template->sections->sortBy('order');

        return view('templates.print', compact('template', 'sections'));
    }

    public function destroy($id)
    {
        $can_delete_templates = auth()->user()->can('sales.delete_templates') ? true : false;
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || $can_delete_templates)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        try {
            $template = Template::findOrFail($id);
            TemplateSection::where('template_id', $template->id)->delete();
            $template->delete();
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('templates.index')
            ->with('status', $output);
    }
}
