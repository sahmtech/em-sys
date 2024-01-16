<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Http\Response;
use Modules\Essentials\Entities\AttendanceStatus;
use Modules\Essentials\Entities\EssentialsAttendanceStatus;

class AttendanceStatusController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');


        $can_crud_attendance_status = auth()->user()->can('essentials.crud_attencances_status');
        $can_delete_attendance_status = auth()->user()->can('essentials.delete_attencances_status');
        $can_add_attendance_status = auth()->user()->can('essentials.add_attencances_status');

        if (!$can_crud_attendance_status) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $attencancesStatuses = EssentialsAttendanceStatus::all();
        if (request()->ajax()) {
            return Datatables::of($attencancesStatuses)
                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'name',
                    function ($row) {
                        return $row->name;
                    }
                )
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin ,  $can_delete_attendance_status ) {
                        $html = '';
                        if ($is_admin ||   $can_delete_attendance_status ) {
                            $html .= '<button class="btn btn-xs btn-danger delete_country_button" data-href="' . route('attendanceStatus.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
                // ->filterColumn('name', function ($query, $keyword) {
                //     $query->where('name', 'like', "%{$keyword}%");
                // })

                ->rawColumns(['id', 'name', 'action'])
                ->make(true);
        }
        return view('essentials::settings.partials.attendance_status.index');
    }


    public function store(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['name']);
            EssentialsAttendanceStatus::create($input);

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
            // return response()->json(['success' => true, 'message' =>  __('lang_v1.saved_successfully')]);
            return redirect()->route('attendanceStatus')->with('success', $output['msg']);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            // return response()->json(['success' => true, 'message' =>  __('lang_v1.saved_successfully')]);
            return redirect()->route('attendanceStatus')->withErrors([$output['msg']]);
        }
    }

    // public function show($id)
    // {
    //     return view('essentials::show');
    // }



    // public function update(Request $request, $id)
    // {

    //     $business_id = $request->session()->get('user.business_id');
    //     $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



    //     try {
    //         $input = $request->only(['arabic_name', 'english_name', 'nationality', 'details', 'is_active']);

    //         $input2['name'] = json_encode(['ar' => $input['arabic_name'], 'en' => $input['english_name']]);

    //         $input2['nationality'] = $input['nationality'];

    //         $input2['details'] = $input['details'];

    //         $input2['is_active'] = $input['is_active'];

    //         AttendanceStatus::where('id', $id)->update($input2);
    //         $output = [
    //             'success' => true,
    //             'msg' => __('lang_v1.updated_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => false,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }


    //     return redirect()->route('countries');
    // }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsAttendanceStatus::where('id', $id)
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
