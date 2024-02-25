<?php

namespace Modules\FollowUp\Http\Controllers;


use App\Category;

use App\AccessRole;
use App\AccessRoleProject;

use App\WorkerProjectsHistory;
use App\User;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Spatie\Activitylog\Models\Activity;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;

use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\FollowUp\Entities\FollowupUserAccessProject;
use Modules\Sales\Entities\SalesProject;

class FollowUpWorkerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {


        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_crud_workers = auth()->user()->can('followup.crud_workers');
        if (!($is_admin || $can_followup_crud_workers)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $userIds = User::whereIn('id', $userIds)->whereIn('assigned_to', $followupUserAccessProject)->pluck('id')->toArray();
            $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();
        }

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();

        $fields = $this->moduleUtil->getWorkerFields();
        $users = User::whereIn('users.id', $userIds)->where('user_type', 'worker')

            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);

        $users->select(
            'users.*',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        )->orderBy('users.id', 'desc')
            ->groupBy('users.id');



        if (request()->ajax()) {
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
                if (request()->input('project_name') == 'none') {
                    $users = $users->whereNull('users.assigned_to');
                } else {
                    $users = $users->where('users.assigned_to', request()->input('project_name'));
                }
            }

            if (!empty(request()->input('status_fillter')) && request()->input('status_fillter') !== 'all') {

                $users = $users->where('users.status', request()->input('status_fillter'));
            }

            if (request()->date_filter && !empty(request()->filter_start_date) && !empty(request()->filter_end_date)) {
                $start = request()->filter_start_date;
                $end = request()->filter_end_date;

                $users->whereHas('contract', function ($query) use ($start, $end) {
                    $query->whereDate('contract_end_date', '>=', $start)
                        ->whereDate('contract_end_date', '<=', $end);
                });
            }
            if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {

                $users = $users->where('users.nationality_id', request()->nationality);
            }

            return Datatables::of($users)

                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                })
                ->addColumn('residence_permit_expiration', function ($user) {
                    $residencePermitDocument = $user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();
                    if ($residencePermitDocument) {

                        return optional($residencePermitDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })

                ->addColumn('company_id', function ($user) {
                    return  $user->company->name ?? "";
                })
                ->addColumn('insurance', function ($user) {
                    if ($user->essentialsEmployeesInsurance && $user->essentialsEmployeesInsurance->is_deleted == 0) {
                        return __('followup::lang.has_insurance');
                    } else {
                        return __('followup::lang.has_not_insurance');
                    }
                })

                ->addColumn('passport_number', function ($user) {
                    $passportDocument = $user->OfficialDocument
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($user) {
                    $passportDocument = $user->OfficialDocument
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })

                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })

                ->addColumn('admissions_date', function ($user) {
                    // return $this->getDocumentnumber($user, 'admissions_date');
                    return optional($user->essentials_admission_to_works)->admissions_date ?? ' ';
                })
                ->addColumn('admissions_type', function ($user) {
                    // return $this->getDocumentnumber($user, 'admissions_date');
                    return optional($user->essentials_admission_to_works)->admissions_type ?? ' ';
                })
                ->addColumn('admissions_status', function ($user) {
                    // return $this->getDocumentnumber($user, 'admissions_date');
                    return optional($user->essentials_admission_to_works)->admissions_status ?? ' ';
                })


                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })

                ->addColumn('profession', function ($row) use ($appointments, $professions) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $professions[$professionId] ?? '';

                    return $professionName;
                })



                ->addColumn('specialization', function ($row) use ($appointments2, $specializations) {
                    $specializationId = $appointments2[$row->id] ?? '';
                    $specializationName = $specializations[$specializationId] ?? '';

                    return $specializationName;
                })->addColumn('bank_code', function ($user) {

                    $bank_details = json_decode($user->bank_details);
                    return $bank_details->bank_code ?? ' ';
                })
                ->addColumn('contact_name', function ($user) {


                    return $user->contact_name;
                })
                ->addColumn('categorie_id', function ($row) use ($travelCategories) {
                    $item = $travelCategories[$row->categorie_id] ?? '';

                    return $item;
                })
                ->filterColumn('worker', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('residence_permit', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['contact_name', 'worker', 'categorie_id', 'admissions_status', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('followup::workers.index')->with(compact('contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
    }



    public function upload_attachments(Request $request)
    {

        try {
            $input = $request->only(
                [
                    'worker_id',
                    'doc_type',
                    'doc_number',
                    'issue_date',
                    'issue_place',
                    'expiration_date',
                    'file'
                ]
            );

            $worker_id = $request->input('worker_id');

            $input2['type'] = $input['doc_type'];
            $input2['number'] = $input['doc_number'];
            $input2['issue_date'] = $input['issue_date'];
            $input2['expiration_date'] = $input['expiration_date'];
            $input2['employee_id'] =  $request->input('worker_id');
            $input2['issue_place'] = $input['issue_place'];
            $input2['is_active'] = 1;
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


        return redirect()->route('showWorker', ['id' => $worker_id])->with($output);
    }
    private function getDocumentExpirationDate($user, $documentType)
    {
        foreach ($user->OfficialDocument as $off) {
            if ($off->type == $documentType) {
                return $off->expiration_date;
            }
        }

        return ' ';
    }

    private function getDocumentnumber($user, $documentType)
    {
        foreach ($user->OfficialDocument as $off) {
            if ($off->type == $documentType) {
                return $off->number;
            }
        }

        return ' ';
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('followup::create');
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


        $business_id = request()->session()->get('user.business_id');

        $user = User::with(['contactAccess', 'assignedTo', 'OfficialDocument', 'proposal_worker'])
            ->find($id);



        $documents = null;
        $document_delivery = null;

        if ($user->user_type == 'employee') {

            $documents = $user->OfficialDocument;
        } else if ($user->user_type == 'worker') {

            if (!empty($user->proposal_worker_id)) {

                $officialDocuments = $user->OfficialDocument;
                $workerDocuments = $user->proposal_worker?->worker_documents;
                $documents = $officialDocuments->merge($workerDocuments);
            } else {
                $documents = $user->OfficialDocument;
            }

            $document_delivery = FollowupDeliveryDocument::where('user_id', $user->id)->get();
        }




        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();


        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('profession_id');

        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)->name;
        } else {
            $profession = "";
        }

        // $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        // if ($specializationId !== null) {
        //     $specialization = EssentialsSpecialization::find($specializationId)->name;
        // } else {
        //     $specialization = "";
        // }


        $user->profession = $profession;
        // $user->specialization = $specialization;


        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);

        $users = User::forDropdown($business_id, false);

        $activities = Activity::forSubject($user)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $nationality_id = $user->nationality_id;
        $nationality = "";

        if (!empty($nationality_id)) {
            $nationality = EssentialsCountry::select('nationality')->where('id', '=', $nationality_id)->first();
        }




        return view('followup::workers.show')->with(compact(
            'user',
            'view_partials',
            'users',
            'activities',
            'bank_name',
            'admissions_to_work',
            'Qualification',
            'Contract',
            'nationalities',
            'nationality',
            'documents',
            'document_delivery',
        ));
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('followup::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function cancleProject(Request $request)
    {

        try {
            $selectedRowsData = json_decode($request->input('selectedRowsData'));


            if (!$selectedRowsData) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.please_select_rows'),
                ];
                return $output;
            }
            foreach ($selectedRowsData as $row) {
                $worker = User::find($row->id);

                $old_project = $worker->assigned_to;

                if (!$worker) {

                    continue;
                }

                $worker->assigned_to = null;
                $worker->save();


                $history = new WorkerProjectsHistory();

                $history->worker_id = $worker->id ?? null;

                $history->type = 'cancle_project';

                $history->old_project_id = $old_project ?? null;

                $history->canceled_date = $request->canceled_date ?? null;

                $history->notes = $request->notes ?? null;

                $history->save();
            }

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

        return $output;
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
