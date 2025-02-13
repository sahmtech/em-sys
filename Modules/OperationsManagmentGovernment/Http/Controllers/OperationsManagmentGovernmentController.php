<?php

namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use App\Company;
use App\Contact;
use App\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OperationsManagmentGovernment\Entities\WaterWeight;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\OperationsManagmentGovernment\Entities\ContactActivity;
use Modules\OperationsManagmentGovernment\Entities\ContactActivityPermission;
use Modules\Sales\Entities\SalesProject;

class OperationsManagmentGovernmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('operationsmanagmentgovernment::index');
    }

    public function permissions()
    {
        $contacts = Contact::query();
        $activities = ContactActivity::all()->pluck('name', 'id'); // Get all activities

        if (request()->ajax()) {
            return DataTables::of($contacts)
                ->addColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) {
                    return $row->supplier_business_name;
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-xs btn-primary open-permissions-modal" data-id="' . $row->id . '" data-url="' . route('operationsmanagmentgovernment.get_contact_permissions', ['id' => $row->id]) . '">' . __('messages.edit') . '</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('operationsmanagmentgovernment::permissions.index', compact('activities'));
    }

    public function get_contact_permissions($id)
    {
        $permissions = ContactActivityPermission::where('contact_id', $id)->pluck('activity_id')->toArray();
        $all_activities = ContactActivity::all()->pluck('name', 'id')->toArray();

        return response()->json(['permissions' => $permissions ?: [], 'all_activities' => $all_activities]);
    }

    public function update_permissions(Request $request, $id)
    {
        ContactActivityPermission::where('contact_id', $id)->delete();

        if ($request->has('activities')) {
            foreach ($request->activities as $activity_id) {
                ContactActivityPermission::create([
                    'contact_id' => $id,
                    'activity_id' => $activity_id,
                ]);
            }
        }

        return response()->json(['success' => true, 'msg' => __('lang_v1.added_success')]);
    }




    public function zone()
    {

        return view('operationsmanagmentgovernment::zone.index');
    }

    public function water()
    {
        $is_admin = auth()->user()->hasRole('Admin#1');
        $can_index_water_weight = auth()->user()->can('operationsmanagmentgovernment.water_weight');

        if (!($is_admin || $can_index_water_weight)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $WaterWeights = WaterWeight::all();
        $companies = Company::all();

        $ids = ContactActivityPermission::pluck('contact_id')->toArray();
        $contacts = Contact::whereIn('id', $ids)->pluck('supplier_business_name', 'id')->toArray();
        $contact_ids = Contact::whereIn('id', $ids)->pluck('id')->toArray();
        $projects = SalesProject::whereIn('contact_id', $contact_ids)->pluck('name', 'id')->toArray();
        if (request()->ajax()) {
            return DataTables::of($WaterWeights)
                ->editColumn('company', function ($row) {
                    return $row->Company?->name ?? '-';
                })
                ->editColumn('project', function ($row) {
                    $tmp = SalesProject::Where('id', $row->project_id)->first()?->name ?? '';
                    return $tmp ?? '-';
                })
                ->editColumn('driver', function ($row) {
                    return $row->driver;
                })
                ->editColumn('plate_number', function ($row) {
                    return $row->plate_number ?? '-';
                })
                ->editColumn('weight_type', function ($row) {
                    return __('operationsmanagmentgovernment::lang.' . $row->weight_type);
                })
                ->editColumn('sample_result', function ($row) {
                    return $row->sample_result ?? '-';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : '-';
                })
                ->editColumn('created_by', function ($row) {
                    $tmp = User::where('id', $row->created_by)->first();
                    return  $tmp?->first_name . ' ' .  $tmp?->last_ame ?? '';
                })
                ->addColumn('action', function ($row) use ($is_admin) {
                    $html = '';
                    if ($is_admin) {
                        $html   .= '<button data-id="' . $row->id . '" 
                        class="btn btn-xs btn-info open-edit-modal">
                        <i class="fas fa-edit"></i> ' . __("messages.edit") . '
                    </button> ';
                        $html .= '<button data-href="' . route('operationsmanagmentgovernment.water_weight.delete', ['id' => $row->id]) . '" 
                                     class="btn btn-xs btn-danger delete_water_weight_button"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</button>';
                    }
                    return $html;
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('operationsmanagmentgovernment::water.index', compact('WaterWeights', 'companies', 'projects'));
    }



    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('operationsmanagmentgovernment::create');
    }


    public function store_water(Request $request)
    {
        try {
            DB::beginTransaction();

            $waterWeight = WaterWeight::create([
                'company_id' => 1,
                'driver' => $request->input('driver'),
                'contact_id' => $request->input('contact_id'),
                'plate_number' => $request->input('plate_number'),
                'project_id' => $request->input('project_id'),
                'water_droping_location' => $request->input('water_droping_location'),
                'weight_type' => $request->input('weight_type'),
                'sample_result' => $request->input('sample_result'),
                'date' => $request->input('date'),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('status', [
                    'success' => true,
                    'msg' => __('operationsmanagmentgovernment::lang.added_success'),
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
    }


    public function edit_water($id)
    {
        $waterWeight = WaterWeight::findOrFail($id);

        return response()->json([
            'id' => $waterWeight->id,
            'company_id' => $waterWeight->company_id,
            'project_id' => $waterWeight->project_id,
            'driver' => $waterWeight->driver,
            'plate_number' => $waterWeight->plate_number,
            'weight_type' => $waterWeight->weight_type,
            'water_droping_location' => $waterWeight->water_droping_location,
            'sample_result' => $waterWeight->sample_result,
            'date' => $waterWeight->date
        ]);
    }


    public function update_water(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $waterWeight = WaterWeight::findOrFail($id);
            $waterWeight->update([
                'driver' => $request->input('driver'),
                'contact_id' => $request->input('contact_id'),
                'plate_number' => $request->input('plate_number'),
                'weight_type' => $request->input('weight_type'),
                'sample_result' => $request->input('sample_result'),
                'date' => $request->input('date'),
            ]);

            DB::commit();

            return redirect()->route('operationsmanagmentgovernment.water')
                ->with('status', [
                    'success' => true,
                    'msg' => __('operationsmanagmentgovernment::lang.updated_success'),
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
    }

    public function delete_water($id)
    {
        try {
            DB::beginTransaction();

            $waterWeight = WaterWeight::findOrFail($id);
            $waterWeight->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => __('operationsmanagmentgovernment::lang.deleted_success'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('operationsmanagmentgovernment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('operationsmanagmentgovernment::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
