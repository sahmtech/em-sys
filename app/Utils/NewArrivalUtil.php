<?php

namespace App\Utils;

use App\Company;
use App\Contact;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsWorkCard;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\NewWorkersAdSalaryRequest;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Modules\InternationalRelations\Entities\IrWorkersDocument;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;

class NewArrivalUtil extends Util
{

    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function new_arrival_for_workers(Request $request, $view)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housing_crud_htr_trevelers = auth()->user()->can('housingmovements.new_arrival_for_workers');
        if (!($is_admin || $can_housing_crud_htr_trevelers)) {
            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $workers = IrProposedLabor::with([
            'transactionSellLine.service.profession',
            'transactionSellLine.service.nationality',
            'transactionSellLine.transaction.salesContract.salesOrderOperation.contact',
            'transactionSellLine.transaction.salesContract.project',
            'transactionSellLine.transaction.contact',
            'visa',
            'agency',
            'worker_documents',
        ])
            ->select([
                'ir_proposed_labors.profile_image',
                'ir_proposed_labors.id',
                'passport_number',
                'medical_examination',
                'transaction_sell_line_id',
                'visa_id',
                'arrival_date',
                'agency_id',
                DB::raw("CONCAT(COALESCE(first_name, ''),
                 ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ])
        // ->whereNotNull('visa_id')
            ->where('interviewStatus', 'acceptable')
            ->where('arrival_status', 0);

        // return $workers->get();

        if (!empty($request->input('project_name_filter'))) {
        }
        if (!empty(request()->input('project_name_filter')) && request()->input('project_name_filter') !== 'all') {

            if (request()->input('project_name_filter') == 'none') {
                $workers->whereNull('transaction_sell_line_id');
            } else {
                $workers->whereHas('transactionSellLine.transaction.salesContract.project', function ($query) use ($request) {
                    $query->where('id', '=', $request->input('project_name_filter'));
                });
            }
        }

        if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
            $start = request()->filter_start_date;
            $end = request()->filter_end_date;

            $workers->whereHas('visa', function ($query) use ($start, $end) {
                $query->whereDate('arrival_date', '>=', $start)
                    ->whereDate('arrival_date', '<=', $end);
            });
        }

        if (request()->ajax()) {

            return Datatables::of($workers)

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })

                ->editColumn('project', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->project?->name ?? '';
                })
                ->editColumn('contact', function ($row) {
                    return $row->transactionSellLine?->transaction?->contact?->supplier_business_name ?? '';
                })

                ->editColumn('location', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->project?->Location ?? '';
                })

                ->editColumn('arrival_date', function ($row) {
                    return $row->arrival_date ?? '';
                })

                ->editColumn('profession', function ($row) {
                    return $row->transactionSellLine?->service?->profession?->name ?? '';
                })
                ->editColumn('nationality', function ($row) {
                    return $row->transactionSellLine?->service?->nationality?->nationality ?? '';
                })

                ->filter(function ($query) use ($request) {

                    if (!empty($request->input('full_name'))) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
                    }
                })

                ->rawColumns(['action', 'profession', 'nationality', 'checkbox'])
                ->make(true);
        }

        $buildings = DB::table('htr_buildings')->get()->pluck('name', 'id');

        $salesProjects = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();

        $roomStatusOptions = [
            'busy' => __('housingmovements::lang.busy_rooms'),
            'available' => __('housingmovements::lang.available_rooms'),
        ];
        return view($view)->with(compact('salesProjects', 'buildings', 'roomStatusOptions'));
    }
    public function housed_workers_index(Request $request, $view)
    {
        $business_id = request()->session()->get('user.business_id');

        $buildings = DB::table('htr_buildings')->get()->pluck('name', 'id');
        $availableRooms = HtrRoom::where('beds_count', '>', 0)->pluck('room_number', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $workers = IrProposedLabor::with([
            'transactionSellLine.service.profession',
            'transactionSellLine.service.nationality',
            'transactionSellLine.transaction.salesContract.salesOrderOperation.contact',
            'transactionSellLine.transaction.salesContract.project',
            'visa',
            'agency',
        ])
            ->select([
                'ir_proposed_labors.id',
                'passport_number',
                'arrival_date',
                'transaction_sell_line_id',
                'visa_id',
                'agency_id',
                DB::raw("CONCAT(COALESCE(first_name, ''),
                 ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ])
        // ->whereNotNull('visa_id')
            ->where('interviewStatus', 'acceptable')
            ->where('arrival_status', 1)
            ->where('housed_status', 0);

        if (!empty($request->input('project_name_filter'))) {
            $workers->whereHas('transactionSellLine.transaction.salesContract.project', function ($query) use ($request) {
                $query->where('id', '=', $request->input('project_name_filter'));
            });
        }

        if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
            $start = request()->filter_start_date;
            $end = request()->filter_end_date;

            $workers->whereHas('visa', function ($query) use ($start, $end) {
                $query->whereDate('arrival_date', '>=', $start)
                    ->whereDate('arrival_date', '<=', $end);
            });
        }

        if (request()->ajax()) {

            return Datatables::of($workers)

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })

                ->editColumn('project', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->project?->name ?? '';
                })

                ->editColumn('location', function ($row) {
                    return $row->transactionSellLine?->transaction?->salesContract?->salesOrderOperation?->Location ?? '';
                })

                ->editColumn('arrival_date', function ($row) {
                    return $row->arrival_date ?? '';
                })

                ->editColumn('profession', function ($row) {
                    return $row->transactionSellLine?->service?->profession?->name ?? '';
                })
                ->editColumn('nationality', function ($row) {
                    return $row->transactionSellLine?->service?->nationality?->nationality ?? '';
                })

                ->filter(function ($query) use ($request) {

                    if (!empty($request->input('full_name'))) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
                    }
                })

                ->rawColumns(['action', 'profession', 'nationality', 'checkbox'])
                ->make(true);
        }

        $salesProjects = SalesProject::all()->pluck('name', 'id');
        $roomStatusOptions = [
            'busy' => __('housingmovements::lang.busy_rooms'),
            'available' => __('housingmovements::lang.available_rooms'),
        ];
        return view($view)->with(compact('salesProjects', 'buildings', 'availableRooms', 'roomStatusOptions'));
    }
    public function medicalExamination($view)
    {

        $workers = IrProposedLabor::with(['worker_documents'])
            ->whereNotNull('visa_id')
            ->where('interviewStatus', 'acceptable')
            ->where('arrival_status', 1)
            ->select([
                'id',
                'medical_examination',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ]);

        if (request()->ajax()) {
            return Datatables::of($workers)
                ->addColumn('action', function ($worker) {

                    $buttonHtml = $worker->medical_examination == 1 ?
                    '<button class="btn btn-primary" onclick="addFile(' . $worker->id . ')">Add File</button>' :
                    '<button class="btn btn-primary" onclick="addFile(' . $worker->id . ')">Add File</button>';

                    foreach ($worker->worker_documents as $document) {
                        if ($document->type == "medical_examination") {
                            $buttonHtml = '<a href="/uploads/' . $document->attachment . '" class="btn btn-success" target="_blank">View File</a>';
                            break;
                        }
                    }
                    return $buttonHtml;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($view);
    }

    public function uploadMedicalDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        IrProposedLabor::where('id', $request->workerId)->update([
            'medical_examination' => 1,
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('/workers_documents');
            $uploadedFile = new IrWorkersDocument();
            $uploadedFile->worker_id = $request->workerId;
            $uploadedFile->type = 'medical_examination';
            $uploadedFile->uploaded_by = auth()->user()->id;
            $uploadedFile->uploaded_at = Carbon::now();
            $uploadedFile->attachment = $path;

            $uploadedFile->save();
        }

        return response()->json(['message' => 'File uploaded successfully']);
    }

    public function medicalInsurance($view)
    {

        $insurance_companies = Contact::where('type', 'insurance')
            ->pluck('supplier_business_name', 'id');

        $insurance_classes = EssentialsInsuranceClass::all()
            ->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $workers = User::with(['proposal_worker'])
            ->whereNotNull('proposal_worker_id')
            ->whereIn('id', $userIds)
            ->select([
                'id',
                'has_insurance',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ]);

        if (request()->ajax()) {
            return Datatables::of($workers)
                ->addColumn('action', function ($worker) {
                    if ($worker->has_insurance) {
                        return '<button onclick="viewInsurance(' . $worker->id . ')" class="btn btn-info">' . __('housingmovements::lang.view_insurance_info') . '</button>';
                    } else {
                        return '<button onclick="addInsurance(' . $worker->id . ')" class="btn btn-primary">' . __('essentials::lang.add_Insurance') . '</button>';
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($view)->with(compact('insurance_companies', 'insurance_classes'));
    }

    public function workCardIssuing($view)
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $responsible_client = null;

        $userIds = User::whereNot('user_type', 'admin')->whereNotNull('proposal_worker_id')
            ->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $all_users = User::whereIn('id', $userIds)
            ->whereNotNull('proposal_worker_id')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, ''), ' - ', COALESCE(users.border_no, '')) as full_name, users.border_no"),
                'users.id'
            )
            ->whereNotIn('users.id', function ($query) {
                $query->select('employee_id')->from('essentials_work_cards');
            })
            ->get();

        $employees = $all_users->mapWithKeys(function ($item) {
            return [$item->id => [
                'name' => $item->full_name,
                'border_no' => $item->border_no,
            ]];
        });

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
            //  '1' => __('essentials::lang.1_year'),
        ];
        $companies = Company::pluck('name', 'id');
        $card = EssentialsWorkCard::whereIn('employee_id', $userIds)
            ->where('is_active', 1)
            ->with(['user', 'user.OfficialDocument'])
            ->select(
                'id',
                'employee_id',
                'workcard_duration',
                'work_card_no as card_no',
                'fees as passport_fees',
                'work_card_fees as work_card_fees',
                'other_fees',
                'Payment_number as Payment_number'
            );

        $all_users = User::select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');
        $sales_projects = SalesProject::pluck('name', 'id');

        if (request()->ajax()) {
            return Datatables::of($card)

                ->editColumn('company_name', function ($row) {
                    return $row->user->company?->name ?? '';
                })

                ->editColumn('fixnumber', function ($row) {
                    return $row->user->company?->documents
                    ?->where('licence_type', 'COMMERCIALREGISTER')
                        ->first()->unified_number ?? '';
                })

                ->editColumn('user', function ($row) {
                    return $row->user->first_name .
                    ' ' .
                    $row->user->mid_name .
                    ' ' .
                    $row->user->last_name ??
                        '';
                })
            // ->addColumn('assigned_to', function ($row) use ($sales_projects) {
            //     if ($row->user->assigned_to) {

            //         return $sales_projects[$row->user->assigned_to];
            //     } else {
            //         return '';
            //     }
            // })

                ->editColumn('project', function ($row) {
                    if ($row->user->assignedTo) {

                        return $row->user->assignedTo->name ?? '';
                    } else {
                        return '';
                    }
                })->addColumn('responsible_client', function ($row) use ($name_in_charge_choices) {
                if (empty($row->user->assignedTo)) {
                    return '';
                }

                $userIds = json_decode($row->user->assignedTo->assigned_to, true) ?? [];

                $names = [];

                foreach ($userIds as $userId) {
                    if (!empty($name_in_charge_choices[$userId])) {
                        $names[] = $name_in_charge_choices[$userId];
                    }
                }

                return implode(', ', $names);
            })

                ->editColumn('proof_number', function ($row) {
                    $residencePermitDocument = $row->user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();

                    if ($residencePermitDocument) {
                        return $residencePermitDocument->number;
                    } elseif ($row->user->border_no) {
                        return $row->user->border_no;
                    } else {
                        return '';
                    }
                })

                ->editColumn('nationality', function ($row) {
                    return $row->user->country?->nationality ?? '';
                })

                ->rawColumns([
                    'action',
                    'profession',
                    'nationality',
                    'checkbox',

                ])
                ->make(true);
        }

        $proof_numbers = User::whereIn('users.id', $userIds)
            ->where('users.user_type', 'worker')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"),
                'users.id'
            )
            ->get();

        return view($view)->with(
            compact(
                'sales_projects',
                'proof_numbers',
                'employees',
                'companies',
                'durationOptions'
            )
        );
    }
    public function storeWorkCard(Request $request)
    {

        try {
            $data = $request->only([
                'employee_id', 'border_no', 'business', 'company_id',
                'workcard_duration_input',
                'Payment_number',
                'passport_fees_input',
                'work_card_fees',
                'other_fees',

            ]);

            if ($request->input('Payment_number') != null && strlen($request->input('Payment_number')) !== 14) {
                $output = [
                    'success' => 0,
                    'msg' => __('essentials::lang.payment_number_invalid'),
                ];
            } else {
                $data['employee_id'] = (int) $request->input('employee_id');
                $data['fees'] = $request->input('passport_fees_input');
                $data['work_card_fees'] = $request->input('work_card_fees');
                $data['other_fees'] = $request->input('other_fees');
                $data['workcard_duration'] = (int) $request->input(
                    'workcard_duration_input'
                );
                $data['is_active'] = 1;
                $lastrecord = EssentialsWorkCard::orderBy(
                    'work_card_no',
                    'desc'
                )->first();

                if ($lastrecord) {
                    $lastEmpNumber = (int) substr($lastrecord->work_card_no, 3);
                    $nextNumericPart = $lastEmpNumber + 1;
                    $data['work_card_no'] =
                    'WC' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
                } else {
                    $data['work_card_no'] = 'WC' . '000';
                }

                EssentialsWorkCard::create($data);
                $user = User::findOrFail($request->input('employee_id'));
                $user->update([
                    'company_id' => $request->input('company_id'),
                    'updated_by' => Auth::user()->id,
                ]);

                $output = [
                    'success' => 1,
                    'msg' => __('essentials::lang.card_added_sucessfully'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage()
            );

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messeages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function SIMCard($view)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $workers = User::with(['proposal_worker'])->whereIn('id', $userIds)
            ->whereNotNull('proposal_worker_id')->whereNot('status', 'inactive')
            ->select([
                'id',
                'contact_number',
                'has_SIM', 'cell_phone_company',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ]);

        if (request()->ajax()) {
            return Datatables::of($workers)
                ->addColumn('action', function ($worker) {
                    if ($worker->has_SIM) {
                        return $worker->cell_phone_company;
                    } else {
                        return '<button onclick="addSIM(' . $worker->id . ')" class="btn btn-primary">' . __('housingmovements::lang.add_SIM') . '</button>';
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($view);
    }

    public function addSIM(Request $request)
    {

        $user = User::findOrFail($request->user);

        $user->update([
            'cell_phone_company' => $request->cell_phone_company,
            'contact_number' => $request->contact_number,
            'has_SIM' => 1,
            'updated_by' => Auth::user()->id,
        ]);
        $output = [
            'success' => true,
            'msg' => __('lang_v1.added_success'),
        ];
        return redirect()->back()
            ->with('status', $output);
    }

    public function bankAccounts($view)
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');
        $all_users = User::whereIn('id', $userIds)->whereNotNull('proposal_worker_id')->whereNull('bank_details')->select(
            DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"),
            'users.id'
        )->get();

        $employees = $all_users->pluck('full_name', 'id');
        $workers = User::with(['proposal_worker', 'activeIban'])
            ->whereNotNull('proposal_worker_id')
            ->whereNotNull('bank_details')
            ->where('status', '!=', 'inactive')
            ->select([
                'id',
                'bank_details',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ]);

        if (request()->ajax()) {
            return Datatables::of($workers)
                ->addColumn('account_holder_name', function ($worker) {

                    return json_decode($worker->bank_details, true)['account_holder_name'] ?? '';
                })
                ->addColumn('account_number', function ($worker) {
                    return json_decode($worker->bank_details, true)['account_number'] ?? '';
                })
                ->addColumn('bank_name', function ($worker) {
                    return json_decode($worker->bank_details, true)['bank_name'] ?? '';
                })
                ->addColumn('bank_code', function ($worker) {
                    return json_decode($worker->bank_details, true)['bank_code'] ?? '';
                })
                ->addColumn('iban_file', function ($worker) {
                    $document = $worker->activeIban;
                    if ($document) {
                        return '<a href="/uploads/' . $document->file_path . '" class="btn btn-success" target="_blank">View File</a>';
                    }
                    return __('housingmovements::lang.no_files');
                })
                ->rawColumns(['account_holder_name', 'account_number', 'bank_name', 'bank_code', 'iban_file'])
                ->make(true);
        }

        return view($view)->with(compact('employees', 'banks'));
    }

    public function addBank(Request $request)
    {
        $is_existing = EssentialsOfficialDocument::where('number', $request->bank_details['bank_code'])->where('is_active', 1)->first();
        if ($is_existing) {
            $output = [
                'success' => false,
                'msg' => __('housingmovements::lang.the_bank code is exists already'),
            ];
            return redirect()->back()
                ->with('status', $output);
        }
        $user = User::findOrFail($request->user_id);
        $user->update([
            'bank_details' => json_encode($request->bank_details),
            'updated_by' => Auth::user()->id,
        ]);

        if ($request->hasFile('iban_file')) {

            $file = $request->file('iban_file');

            $path = $file->store('/officialDocuments');

            $documentData = [
                'type' => 'Iban',
                'status' => 'valid',
                'is_active' => 1,
                'employee_id' => $request->user_id,
                'number' => $request->bank_details['bank_code'],
                'created_by' => Auth::user()->id,
                'file_path' => $path,
            ];

            EssentialsOfficialDocument::create($documentData);
        }
        $output = [
            'success' => true,
            'msg' => __('lang_v1.added_success'),
        ];
        return redirect()->back()
            ->with('status', $output);
    }

    public function QiwaContracts($view)
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')->whereNotNull('proposal_worker_id')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $employees_contracts = EssentialsEmployeesContract::join('users as u', 'u.id', '=', 'essentials_employees_contracts.employee_id')
            ->whereIn('u.id', $userIds)
            ->where('u.status', '!=', 'inactive')

            ->select([
                'essentials_employees_contracts.id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.mid_name, '') ,' ' ,COALESCE(u.last_name, '')) as user"),
                'essentials_employees_contracts.contract_number',
                'essentials_employees_contracts.contract_start_date',
                'essentials_employees_contracts.contract_end_date',
                'essentials_employees_contracts.contract_duration',
                'essentials_employees_contracts.contract_per_period',
                'essentials_employees_contracts.probation_period',
                'essentials_employees_contracts.contract_type_id',
                'essentials_employees_contracts.is_renewable',
                'essentials_employees_contracts.is_active',
                'essentials_employees_contracts.file_path',
                DB::raw("
                CASE
                    WHEN essentials_employees_contracts.contract_end_date IS NULL THEN NULL
                    WHEN essentials_employees_contracts.contract_start_date IS NULL THEN NULL
                    WHEN DATE(essentials_employees_contracts.contract_end_date) <= CURDATE() THEN 'canceled'
                    WHEN DATE(essentials_employees_contracts.contract_end_date) > CURDATE() THEN 'valid'
                    ELSE 'Null'
                END as status
            "),
            ])
            ->where('essentials_employees_contracts.is_active', 1)
            ->orderby('id', 'desc');

        $contract_types = EssentialsContractType::pluck('type', 'id')->all();
        if (request()->ajax()) {

            return Datatables::of($employees_contracts)
                ->editColumn('contract_type_id', function ($row) use ($contract_types) {
                    $item = $contract_types[$row->contract_type_id] ?? '';
                    return $item;
                })

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';

                        // if ($is_admin || $can_show_employee_contracts) {
                        if (!empty($row->file_path)) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.open(\'/uploads/' . $row->file_path . '\', \'_blank\')"><i class="fa fa-eye"></i> ' . __('essentials::lang.contract_view') . '</button>';
                            '&nbsp;';
                        } else {
                            $html .= '<span class="text-warning">' . __('sales::lang.no_file_to_show') . '</span>';
                        }
                        // }

                        // if ($is_admin || $can_delete_employee_contracts) {
                        $html .= ' &nbsp; <button class="btn btn-xs btn-danger delete_employeeContract_button" data-href="' . route('employeeContract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        //   }

                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('file_path')
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
        $query = User::whereIn('id', $userIds)->whereDoesntHave('activeContract');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        return view($view)->with(compact('users', 'contract_types'));
    }

    public function residencyPrint($view)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $workers = User::with(['proposal_worker'])->whereIn('id', $userIds)
            ->whereNotNull('proposal_worker_id')->whereNot('status', 'inactive')
            ->select([
                'id',
                'residency_print', 'id_proof_number',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ]);

        if (request()->ajax()) {
            return Datatables::of($workers)
                ->addColumn('id_proof_number', function ($worker) {
                    if ($worker->id_proof_number) {
                        return $worker->id_proof_number;
                    } else {
                        return '<button onclick="add_eqama(' . $worker->id . ')" class="btn btn-warning">' . __('housingmovements::lang.add_eqama') . '</button>';
                    }
                })
                ->addColumn('action', function ($worker) {
                    if ($worker->id_proof_number) {
                        if ($worker->residency_print) {
                            return __('housingmovements::lang.done');
                        } else {
                            return '<button onclick="print_residency(' . $worker->id . ')" class="btn btn-primary">' . __('housingmovements::lang.print_residency') . '</button>';
                        }
                    }
                })
                ->rawColumns(['action', 'id_proof_number'])
                ->make(true);
        }

        return view($view);
    }

    public function addEqama(Request $request)
    {
        if (!$request->id_proof_number || $request->id_proof_number == null) {
            $output = [
                'success' => false,
                'msg' => __('housingmovements::lang.please add the eqama number'),
            ];
            return redirect()->back()
                ->with('status', $output);
        }
        $user = User::find($request->user);
        $user->id_proof_name = 'eqama';
        $user->id_proof_number = $request->id_proof_number;
        $user->updated_by = auth()->user()->id;
        $user->save();
        $documentData = [
            'type' => 'residence_permit',
            'status' => 'valid',
            'is_active' => 1,
            'employee_id' => $request->user,
            'number' => $request->id_proof_number,
            'created_by' => Auth::user()->id,
        ];

        EssentialsOfficialDocument::create($documentData);
        $output = [
            'success' => true,
            'msg' => __('housingmovements::lang.updated_successfully'),
        ];
        return redirect()->back()
            ->with('status', $output);
    }
    public function updateResidencyPrint(Request $request)
    {

        $worker = User::findOrFail($request->id);

        $worker->residency_print = 1;
        if ($worker->save()) {
            return response()->json([
                'success' => true,
                'msg' => __('housingmovements::lang.updated_successfully'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'msg' => __('housingmovements::lang.update_failed'),
            ], 500);
        }
    }

    public function residencyDelivery($view)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $workers = User::with(['proposal_worker.worker_documents'])->whereIn('id', $userIds)
            ->whereNotNull('proposal_worker_id')->whereNotNull('id_proof_number')->whereNot('status', 'inactive')
            ->select([
                'id', 'proposal_worker_id',
                'residency_delivery',
                DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            ]);
        //  return $workers->get();
        if (request()->ajax()) {
            return Datatables::of($workers)
                ->addColumn('action', function ($worker) {

                    $actionButton = '<button onclick="delivery_residency(' . $worker->id . ')" class="btn btn-primary">' . __('housingmovements::lang.residencyDelivery') . '</button>';

                    if ($worker->residency_delivery) {
                        error_log('here');
                        foreach ($worker->proposal_worker->worker_documents as $document) {
                            error_log('here2');
                            error_log($document->type);

                            if ($document->type == "residency_delivery") {
                                error_log('here3');
                                return '<a href="/uploads/' . $document->attachment . '" class="btn btn-success" target="_blank">' . __('housingmovements::lang.delivery_file') . '</a>';
                            }
                        }
                        return 'No file available';
                    }
                    return $actionButton;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($view);
    }

    public function deliveryResidency(Request $request)
    {
        if (!$request->hasFile('file')) {
            $output = [
                'success' => false,
                'msg' => __('housingmovements::lang.please uplode the delivery file'),
            ];
            return redirect()->back()
                ->with('status', $output);
        }

        $worker = User::findOrFail($request->user);
        $worker->residency_delivery = 1;
        $worker->save();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('/workers_documents');
            $uploadedFile = new IrWorkersDocument();
            $uploadedFile->worker_id = $worker->proposal_worker_id;
            $uploadedFile->type = 'residency_delivery';
            $uploadedFile->uploaded_by = auth()->user()->id;
            $uploadedFile->uploaded_at = Carbon::now();
            $uploadedFile->attachment = $path;

            $uploadedFile->save();
        }

        $output = [
            'success' => true,
            'msg' => __('housingmovements::lang.updated_successfully'),
        ];
        return redirect()->back()
            ->with('status', $output);
    }

    public function advanceSalaryRequest($view)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $users = User::whereIn('id', $userIds)->whereNotNull('proposal_worker_id')
            ->where('status', '!=', 'inactive')->whereNotIn('users.id', function ($query) {
            $query->select('related_to')->from('new_workers_ad_salary_requests');
        })->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');
        $requests = NewWorkersAdSalaryRequest::leftJoin('users', 'users.id', '=', 'new_workers_ad_salary_requests.related_to')
            ->whereIn('new_workers_ad_salary_requests.related_to', $userIds)->whereNotNull('proposal_worker_id')->where('users.status', '!=', 'inactive')
            ->select([
                'new_workers_ad_salary_requests.request_no', 'new_workers_ad_salary_requests.related_to',
                'new_workers_ad_salary_requests.advSalaryAmount', 'new_workers_ad_salary_requests.monthlyInstallment',
                'new_workers_ad_salary_requests.installmentsNumber', 'new_workers_ad_salary_requests.status',
                'new_workers_ad_salary_requests.note', 'new_workers_ad_salary_requests.created_at',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.border_no',
            ]);

        if (request()->ajax()) {

            return DataTables::of($requests ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })

                ->editColumn('status', function ($row) use ($is_admin) {
                    if ($row->status) {
                        $status = trans('request.' . $row->status);

                        return $status;
                    }
                })

                ->rawColumns(['status'])

                ->make(true);
        }

        return view($view)->with(compact('users'));
    }
    public function newWorkersAdvSalaryStore(Request $request)
    {

        $path = '';
        if ($request->hasFile('attachment')) {

            $file = $request->file('attachment');

            $path = $file->store('/requests_attachments');
        }

        $latestRecord = NewWorkersAdSalaryRequest::orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $numericPart = (int) substr($latestRefNo, 'adv_');
            $numericPart++;
            $request_no = 'adv_' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $request_no = 'adv_0001';
        }
        $documentData = [
            'advSalaryAmount' => $request->amount,
            'request_no' => $request_no,
            'monthlyInstallment' => $request->monthlyInstallment,
            'related_to' => $request->user_id,
            'installmentsNumber' => $request->installmentsNumber,
            'status' => 'pending',
            'employee_id' => $request->user_id,
            'note' => $request->note,
            'created_by' => Auth::user()->id,
            'attachment' => $path,
        ];
        NewWorkersAdSalaryRequest::create($documentData);
        $output = [
            'success' => true,
            'msg' => __('housingmovements::lang.added_successfully'),
        ];
        return redirect()->back()
            ->with('status', $output);
    }
}
