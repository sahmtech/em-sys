<?php

namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OperationsManagmentGovernment\Entities\Report;
use Yajra\DataTables\Facades\DataTables;
use App\Contact;
use App\User;
use Modules\OperationsManagmentGovernment\Entities\ContactActivityPermission;
use Modules\OperationsManagmentGovernment\Entities\Incident;
use Modules\OperationsManagmentGovernment\Entities\LostItem;
use Modules\OperationsManagmentGovernment\Entities\LostItems;
use Modules\OperationsManagmentGovernment\Entities\Penalty;
use Modules\OperationsManagmentGovernment\Entities\PhotoConsents;
use Modules\OperationsManagmentGovernment\Entities\SubscriberStatus;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $reportTypes = [
            'penalty' => __('operationsmanagmentgovernment::lang.penalty'),
            'lost_items' => __('operationsmanagmentgovernment::lang.lost_items'),
            'subscriber_status' => __('operationsmanagmentgovernment::lang.subscriber_status'),
            'photo_consents' => __('operationsmanagmentgovernment::lang.photo_consents'),
            'incident' => __('operationsmanagmentgovernment::lang.incident'),
        ];
        if ($request->ajax()) {
            $reports = Report::query();

            if (!empty($request->type)) {
                $reports->where('type', $request->type);
            }

            return DataTables::of($reports)
                ->editColumn('created_by', function ($row) {
                    return $row->createdBy ? $row->createdBy->first_name . ' ' . $row->createdBy->last_name : '-';
                })
                ->editColumn('type', function ($row) use ($reportTypes) {
                    return $reportTypes[$row->type];
                })
                ->editColumn('contact', function ($row) {
                    return $row->contact ? $row->contact->supplier_business_name : '-';
                })
                ->addColumn('action', function ($row) {


                    //     <button data-id="' . $row->id . '" 
                    //     class="btn btn-xs btn-primary open-edit-modal">
                    //     <i class="fas fa-edit"></i> ' . __("messages.edit") . '
                    // </button> 

                    return '<button data-id="' . $row->id . '" class="btn btn-xs btn-info open-view-modal">
            <i class="fas fa-eye"></i> ' . __("messages.view") . '
        </button>
         <button data-id="' . $row->id . '" class="btn btn-xs btn-primary open-edit-modal">
                <i class="fa fa-edit"></i> ' . __("messages.edit") . '
            </button>
                            <button data-href="' . route('operationsmanagmentgovernment.reports.destroy', $row->id) . '" 
                                class="btn btn-xs btn-danger delete-report-button">
                                <i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '
                            </button>';
                })
                ->addColumn('file', function ($row) {
                    if ($row->file_path) {
                        $fileUrl = asset('uploads/' . $row->file_path);
                        return '<a href="' . $fileUrl . '" target="_blank" class="btn btn-xs btn-info">
                                    <i class="fa fa-file"></i> ' . __('home.view_attach') . '
                                </a>';
                    }
                    return '';
                })
                ->rawColumns(['action', 'file'])
                ->make(true);
        }

        $contacts = Contact::pluck('supplier_business_name', 'id')->toArray();
        $users = User::pluck('first_name', 'id')->toArray();
        $ids = ContactActivityPermission::where('activity_id', 1)->pluck('contact_id')->toArray();
        $report_contact_id = Contact::whereIn('id', $ids)->pluck('supplier_business_name', 'id')->toArray();


        $damage_types = [
            'مصدات ماكرو أو مكعبة' => __('operationsmanagmentgovernment::lang.macro_cubic_bumpers'),
            'رصيف' => __('operationsmanagmentgovernment::lang.sidewalk'),
            'نخلة' => __('operationsmanagmentgovernment::lang.palm_tree'),
            'شجرة' => __('operationsmanagmentgovernment::lang.tree'),
            'عامود إنارة' => __('operationsmanagmentgovernment::lang.light_pole'),
            'إشارة مرور' => __('operationsmanagmentgovernment::lang.traffic_signal'),
            'أخرى' => __('operationsmanagmentgovernment::lang.other'),
        ];
        return view('operationsmanagmentgovernment::reports.index', compact('contacts', 'users', 'reportTypes', 'report_contact_id', 'damage_types'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $report = Report::create([
            'date' => $data['date'],
            'time' => $data['time'],
            'type' => $data['type'],
            'created_by' => auth()->user()->id,
            'contact_id' => $data['report_contact_id'],
            'file_path' => $request->hasFile('file') ? $request->file('file')->store('reports') : null,
        ]);

        switch ($data['type']) {
            case 'penalty':
                Penalty::create([
                    'report_id' => $report->id,
                    'national_id' => $data['penalty_national_id'] ?? null,
                    'full_name' => $data['penalty_full_name'] ?? null,
                    'phone_number' => $data['penalty_phone_number'] ?? null,
                    'penalty_type' => $data['penalty_type'] ?? null,
                    'violation_note' => $data['penalty_violation_note'] ?? null,
                    'security_supervisor' => $data['penalty_security_supervisor'] ?? null,
                    'contact_supervisor' => $data['penalty_contact_supervisor_for_marien_front'] ?? null,
                ]);
                break;

            case 'lost_items':
                $lostItemsReport = LostItems::create([
                    'report_id' => $report->id,
                    'receiving_entity_name' => $data['lost_items_receiving_entity_name'] ?? null,
                    'recipient_name' => $data['lost_items_recipient_name'] ?? null,
                    'notes' => $data['lost_items_violation_note'] ?? null,
                    'supervisor' => $data['lost_items_supervisor_name'] ?? null,
                    'ref_number' => $data['lost_items_ref_number'] ?? null,
                ]);

                if (!empty($data['lost_items_item_name'])) {
                    foreach ($data['lost_items_item_name'] as $key => $itemName) {
                        LostItem::create([
                            'lost_items_reports_id' => $lostItemsReport->id,
                            'item_name' => $itemName ?? null,
                            'item_contents' => $data['lost_items_item_contents'][$key] ?? null,
                        ]);
                    }
                }
                break;

            case 'subscriber_status':
                SubscriberStatus::create([
                    'report_id' => $report->id,
                    'type' => $data['subscriber_status_type'] ?? null,
                    'location' => $data['subscriber_status_location'] ?? null,
                    'name' => $data['subscriber_full_name'] ?? null,
                    'company' => $data['subscriber_company'] ?? null,
                    'national_id' => $data['subscriber_national_id'] ?? null,
                    'phone_number' => $data['subscriber_phone_number'] ?? null,
                    'plate_number' => $data['subscriber_plate_number'] ?? null,
                    'status_details' => $data['subscriber_status_details'] ?? null,
                    'commercial_register_number' => $data['subscriber_commercial_register_number'] ?? null,
                    'security_supervisor' => $data['subscriber_security_supervisor'] ?? null,
                    'contact_supervisor' => $data['subscriber_rotana_supervisor'] ?? null,
                ]);
                break;

            case 'photo_consents':
                PhotoConsents::create([
                    'report_id' => $report->id,
                    'name' => $data['photo_full_name'] ?? null,
                    'national_id' => $data['photo_national_eqama_id'] ?? null,
                    'phone_number' => $data['photo_phone_number'] ?? null,
                ]);
                break;

            case 'incident':
                Incident::create([
                    'report_id' => $report->id,
                    'supervisor_name' => $data['incident_gathering_supervisor'] ?? null,
                    'rotion_damage_types' => !empty($data['incident_rotion_damage_types']) ? json_encode($data['rotion_damage_types']) : null,
                    'location' => $data['incident_incident_location'] ?? null,
                    'squar' => $data['incident_squar'] ?? null,
                    'full_name' => $data['incident_full_name'] ?? null,
                    'national_id' => $data['incident_national_id'] ?? null,
                    'phone_number' => $data['incident_phone_number'] ?? null,
                    'insurance_company' => $data['incident_insurance_company'] ?? null,
                    'insurance_policy_number' => $data['incident_insurance_policy_number'] ?? null,
                    'plate_number' => $data['incident_car_plate_number'] ?? null,
                    'car_model' => $data['incident_car_model'] ?? null,
                    'car_year' => $data['incident_car_year'] ?? null,
                    'notes' => $data['incident_notes'] ?? null,
                    'damage_quantity' => $data['incident_damage_quantity'] ?? null,
                    'full_damage' => isset($data['incident_full_damage']) ? 1 : 0,
                    'partial_damage' => isset($data['incident_partial_damage']) ? 1 : 0,
                    'security_supervisor' => $data['incident_security_supervisor'] ?? null,
                    'contact_supervisor' => $data['incident_star_supervisor'] ?? null,
                ]);
                break;
        }
        return redirect()->back()
            ->with('status', ['success' => true, 'msg' => __('messages.success')]);
    }

    public function view($id)
    {
        $report = Report::with(['penalty', 'lostItems.items', 'photoConsents', 'subscriberStatus', 'contact'])->findOrFail($id);
        return response()->json($report);
    }


    // In your ReportsController

    public function edit($id)
    {
        // Make sure we load all relationships we need for editing
        $report = Report::with([
            'penalty',
            'lostItems.items',
            'subscriberStatus',
            'photoConsents',
            'incident'
        ])->findOrFail($id);

        return response()->json($report);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $data = $request->all();

        // Basic report fields
        $report->update([
            'date'      => $data['date'],
            'time'      => $data['time'],
            'contact_id' => $data['report_contact_id'],
            // if a new file is uploaded, overwrite; otherwise keep old path
            'file_path' => $request->hasFile('file')
                ? $request->file('file')->store('reports')
                : $report->file_path,
        ]);

        // Update type-specific data (same idea as you do in "store")
        switch ($report->type) {
            case 'penalty':
                $report->penalty()->update([
                    'full_name'          => $data['penalty_full_name'] ?? null,
                    'national_id'        => $data['penalty_national_id'] ?? null,
                    'phone_number'       => $data['penalty_phone_number'] ?? null,
                    'penalty_type'       => $data['penalty_type'] ?? null,
                    'violation_note'     => $data['penalty_violation_note'] ?? null,
                    'security_supervisor' => $data['penalty_security_supervisor'] ?? null,
                    'contact_supervisor' => $data['penalty_contact_supervisor_for_marien_front'] ?? null,
                ]);
                break;

            case 'lost_items':
                $report->lostItems()->update([
                    'receiving_entity_name' => $data['lost_items_receiving_entity_name'] ?? null,
                    'recipient_name'        => $data['lost_items_recipient_name'] ?? null,
                    'notes'                 => $data['lost_items_violation_note'] ?? null,
                    'supervisor'            => $data['lost_items_supervisor_name'] ?? null,
                    'ref_number'            => $data['lost_items_ref_number'] ?? null,
                ]);

                // Re-create items
                $report->lostItems->items()->delete();
                if (!empty($data['lost_items_item_name'])) {
                    foreach ($data['lost_items_item_name'] as $key => $itemName) {
                        $report->lostItems->items()->create([
                            'item_name'     => $itemName,
                            'item_contents' => $data['lost_items_item_contents'][$key] ?? null,
                        ]);
                    }
                }
                break;

            case 'subscriber_status':
                $report->subscriberStatus()->update([
                    'type'                       => $data['subscriber_status_type'] ?? null,
                    'location'                   => $data['subscriber_status_location'] ?? null,
                    'name'                       => $data['subscriber_full_name'] ?? null,
                    'company'                    => $data['subscriber_company'] ?? null,
                    'national_id'                => $data['subscriber_national_id'] ?? null,
                    'phone_number'               => $data['subscriber_phone_number'] ?? null,
                    'plate_number'               => $data['subscriber_plate_number'] ?? null,
                    'status_details'             => $data['subscriber_status_details'] ?? null,
                    'commercial_register_number' => $data['subscriber_commercial_register_number'] ?? null,
                    'security_supervisor'        => $data['subscriber_security_supervisor'] ?? null,
                    'contact_supervisor'         => $data['subscriber_rotana_supervisor'] ?? null,
                ]);
                break;

            case 'photo_consents':
                $report->photoConsents()->update([
                    'name'         => $data['photo_full_name'] ?? null,
                    'national_id'  => $data['photo_national_eqama_id'] ?? null,
                    'phone_number' => $data['photo_phone_number'] ?? null,
                ]);
                break;

            case 'incident':
                // If using rotion_damage_types, ensure you encode them properly:
                $rotionTypes = $data['incident_rotion_damage_types'] ?? [];
                $report->incident()->update([
                    'supervisor_name'         => $data['incident_gathering_supervisor'] ?? null,
                    'rotion_damage_types'     => !empty($rotionTypes)
                        ? json_encode($rotionTypes)
                        : null,
                    'location'                => $data['incident_incident_location'] ?? null,
                    'squar'                   => $data['incident_squar'] ?? null,
                    'full_name'               => $data['incident_full_name'] ?? null,
                    'national_id'             => $data['incident_national_id'] ?? null,
                    'phone_number'            => $data['incident_phone_number'] ?? null,
                    'insurance_company'       => $data['incident_insurance_company'] ?? null,
                    'insurance_policy_number' => $data['incident_insurance_policy_number'] ?? null,
                    'plate_number'            => $data['incident_car_plate_number'] ?? null,
                    'car_model'               => $data['incident_car_model'] ?? null,
                    'car_year'                => $data['incident_car_year'] ?? null,
                    'notes'                   => $data['incident_notes'] ?? null,
                    'damage_quantity'         => $data['incident_damage_quantity'] ?? null,
                    'full_damage'             => isset($data['incident_full_damage']) ? 1 : 0,
                    'partial_damage'          => isset($data['incident_partial_damage']) ? 1 : 0,
                    'security_supervisor'     => $data['incident_security_supervisor'] ?? null,
                    'contact_supervisor'      => $data['incident_star_supervisor'] ?? null,
                ]);
                break;
        }

        return response()->json(['success' => true, 'msg' => __('messages.updated_successfully')]);
    }





    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['success' => true, 'msg' => __('messages.deleted_successfully')]);
    }
}
