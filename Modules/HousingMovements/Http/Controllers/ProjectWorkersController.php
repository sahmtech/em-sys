<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\AccessRole;
use App\AccessRoleProject;
use App\Category;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Utils\NewArrivalUtil;
use App\Events\UserCreatedOrModified;
use Illuminate\Support\Facades\DB;
use Modules\InternationalRelations\Entities\IrWorkersDocument;
use Modules\Essentials\Entities\EssentialsWorkCard;
use Illuminate\Support\Facades\Auth;
use Modules\Essentials\Entities\Shift;

use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Spatie\Activitylog\Models\Activity;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use App\Contact;
use App\ContactLocation;
use App\User;
use App\Company;
use App\WorkerProjectsHistory;
use Carbon\Carbon;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\FollowUp\Entities\FollowupDeliveryDocument;
use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\HousingMovements\Entities\NewWorkersAdSalaryRequest;

use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsInsuranceClass;



class ProjectWorkersController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    protected $newArrivalUtil;



    public function __construct(ModuleUtil $moduleUtil, NewArrivalUtil $newArrivalUtil)
    {

        $this->moduleUtil = $moduleUtil;
        $this->newArrivalUtil = $newArrivalUtil;
    }


    public function index()
    {

        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housemovements_all_worker = auth()->user()->can('housingmovements.all_workers');
        if (!($is_admin || $can_housemovements_all_worker)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();

        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields_housing();
        $users = User::whereIn('users.id', $userIds)->where('user_type', 'worker')->whereNot('status', 'inactive')
            ->with(['htrRoomsWorkersHistory'])
            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);
        $users->select(
            'users.*',
            'users.id as worker_id',
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

            return DataTables::of($users)

                ->addColumn('worker_id', function ($user) {
                    return $user->worker_id ?? ' ';
                })

                ->addColumn('building', function ($user) {

                    return $user->htrRoomsWorkersHistory?->last()->room?->building?->name ?? '';
                })

                ->addColumn('building_address', function ($user) {

                    return $user->htrRoomsWorkersHistory?->last()->room?->building?->address ?? '';
                })

                ->addColumn('room_number', function ($user) {

                    return $user->htrRoomsWorkersHistory?->last()->room?->room_number ?? '';
                })

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
                })->addColumn('company_name', function ($user) {
                    return optional($user->company)->name ?? ' ';
                })

                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })
                ->addColumn('admissions_date', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_date ?? ' ';
                })
                ->addColumn('admissions_type', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_type ?? ' ';
                })
                ->addColumn('admissions_status', function ($user) {

                    return optional($user->essentials_admission_to_works)->admissions_status ?? ' ';
                })
                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })

                ->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';

                    return $professionName;
                })

                ->addColumn('bank_code', function ($user) {

                    $bank_details = json_decode($user->bank_details);
                    return $bank_details->bank_code ?? ' ';
                })
                ->addColumn('contact_name', function ($user) {

                    return $user->assignedTo->name ?? '';
                })
                ->addColumn('dob', function ($user) {

                    return $user->dob ?? '';
                })->addColumn('insurance', function ($user) {
                    if ($user->essentialsEmployeesInsurance && $user->essentialsEmployeesInsurance->is_deleted == 0) {
                        return __('followup::lang.has_insurance');
                    } else {
                        return __('followup::lang.has_not_insurance');
                    }
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
                ->rawColumns(['contact_name', 'worker_id', 'building', 'building_address', 'room_number', 'company_name', 'passport_number', 'passport_expire_date', 'worker', 'categorie_id', 'admissions_status', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('housingmovements::projects_workers.index')
            ->with(compact('contacts_fillter', 'status_filltetr',  'fields', 'nationalities'));
    }

    public function available_shopping()
    {

        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_housing_all_workers = auth()->user()->can('housingmovements.all_workers');
        if (!($is_admin || $can_housing_all_workers)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $contacts = SalesProject::all()->pluck('name', 'id');
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $days = 3;
        $fillterDate = now()->subDays($days)->toDateString();
        HousingMovementsWorkerBooking::where('booking_end_Date', '<=', $fillterDate)->delete();
        $bookedWorker_ids = HousingMovementsWorkerBooking::all()->pluck('user_id');
        $users = User::whereIn('users.id', $userIds)->with(['rooms'])
            ->where('user_type', 'worker')->whereNot('status', 'inactive')->whereNull('assigned_to')->whereNotIn('id', $bookedWorker_ids);



        if (request()->ajax()) {

            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

                $users = $users->where('users.assigned_to', request()->input('project_name'));
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
                ->addColumn('worker', function ($user) {
                    return $user->first_name . ' ' . $user->last_name;
                })


                ->addColumn('building', function ($user) {
                    return $user->rooms?->building->name;
                })

                ->addColumn('building_address', function ($user) {
                    return $user->rooms?->building->address;
                })

                ->addColumn('room_number', function ($user) {
                    return $user->rooms?->room_number;
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

                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {

                        $html = '';
                        if ($is_admin  || auth()->user()->can('worker.book')) {
                            $html .= '
                        <a href="' . route('worker.book', ['id' => $row->id])  . '"
                        data-href="' . route('worker.book', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button" style="width: 50px;"  data-container="#book_worker_model"><i class="fa fa-bookmark cursor-pointer" style="padding: 5px;
                         font-size: smaller;"></i>' . __("housingmovements::lang.book") . '</a>';
                            return $html;
                        }
                    }
                )

                ->filterColumn('worker', function ($query, $keyword) {
                    $query->where('first_name', 'LIKE', "%{$keyword}%")->orWhere('last_name', 'LIKE', "%{$keyword}%");
                })

                ->rawColumns(['nationality', 'action', 'worker', 'residence_permit_expiration', 'contract_end_date'])
                ->make(true);
        }

        return view('housingmovements::projects_workers.available_shopping')->with(compact('contacts', 'nationalities', 'ContactsLocation'));
    }


    public function addProject(Request $request)
    {

        try {
            if (!$request->project) {
                $output = [
                    'success' => false,
                    'msg' => __('housingmovements::lang.please_select_project'),
                ];
                return $output;
            }
            $selectedRowsData = json_decode($request->input('selectedRowsData'));

            if (!$selectedRowsData) {
                $output = [
                    'success' => false,
                    'msg' => __('housingmovements::lang.please_select_rows'),
                ];
                return $output;
            }

            foreach ($selectedRowsData as $row) {
                $worker = User::find($row->id);



                if (!$worker) {

                    continue;
                }

                $worker->assigned_to  = $request->project;
                $worker->save();


                $history = new WorkerProjectsHistory();

                $history->worker_id = $worker->id ?? null;

                $history->type = 'add_project';

                $history->new_project_id = $request->project ?? null;

                $history->adding_date = $request->adding_date ?? null;

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

    public function reserved_shopping()
    {


        $business_id = request()->session()->get('user.business_id');


        $can_crud_workers = auth()->user()->can('followup.crud_workers');
        if (!$can_crud_workers) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->whereNot('status', 'inactive')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $contacts = SalesProject::all()->pluck('name', 'id');
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $travelCategories = EssentialsTravelTicketCategorie::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        // $fields = $this->moduleUtil->getWorkerFields_hrm();

        $days = 3;
        $fillterDate = now()->subDays($days)->toDateString();
        HousingMovementsWorkerBooking::where('booking_end_Date', '<=', $fillterDate)->delete();

        $users = HousingMovementsWorkerBooking::whereIn('user_id', $userIds)->get();
        $can_unbook = auth()->user()->can('worker.unbook');

        if (request()->ajax()) {


            return Datatables::of($users)
                ->editColumn('worker', function ($row) {
                    return  $row->user->first_name . ' ' . $row->user->last_name ?? '';
                })
                ->addColumn('nationality', function ($row) {
                    return optional($row->user->country)->nationality ?? ' ';
                })
                // ->addColumn('residence_permit_expiration', function ($row) {
                //     $residencePermitDocument = $row->user->OfficialDocument
                //         ->where('type', 'residence_permit')
                //         ->first();

                //     if ($residencePermitDocument) {
                //         return optional($residencePermitDocument)->expiration_date ?? ' ';
                //     } else {

                //         return ' ';
                //     }
                // })

                // ->addColumn('residence_permit', function ($row) {
                //     return   $row->user->id_proof_number;
                // })
                ->addColumn('residence_permit_expiration', function ($row) {
                    $residencePermitDocument = $row->user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();
                    if ($residencePermitDocument) {

                        return optional($residencePermitDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })

                ->addColumn('residence_permit', function ($row) {
                    return $this->getDocumentnumber($row->user, 'residence_permit');
                })
                ->addColumn('contact_name', function ($row) {
                    return $row->saleProject?->name;
                })
                ->addColumn('booking_start_Date', function ($row) {
                    return $row->booking_start_Date;
                })
                ->addColumn('booking_end_Date', function ($row) {
                    return $row->booking_end_Date;
                })



                ->addColumn('essentials_salary', function ($row) {
                    return $row->user->essentials_salary;
                })->addColumn('contact_number', function ($row) {
                    return $row->user->contact_number;
                })
                ->addColumn('total_salary', function ($row) {
                    return $row->user->total_salary;
                })->addColumn('gender', function ($row) {
                    return $row->user->gender;
                })
                ->addColumn('categorie_id', function ($row) use ($travelCategories) {
                    $item = $travelCategories[$row->user->categorie_id] ?? '';

                    return $item;
                })
                ->addColumn('created_by', function ($row) use ($travelCategories) {

                    return  $row->creator->first_name . ' ' . $row->creator->last_name ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_unbook) {

                        $html = '';

                        if ($is_admin  || $can_unbook) {
                            $html .= '
                    <button data-href="' .  route('worker.unbook', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_book_worker_button"><i class="fa fa-minus-circle cursor-pointer"></i>' . __("housingmovements::lang.unbook") . '</button>
                ';
                            return $html;
                        }
                    }
                )


                ->rawColumns(['action', 'created_by', 'contact_name', 'contact_number', 'worker', 'categorie_id', 'gender', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'total_salary', 'essentials_salary'])
                ->make(true);
        }

        return view('housingmovements::projects_workers.reserved_shopping')->with(compact('contacts', 'nationalities', 'ContactsLocation'));
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
    public function final_exit()
    {


        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $contacts = SalesProject::all()->pluck('name', 'id');
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $EssentailsEmployeeOperation_emplyeeIds = EssentailsEmployeeOperation::where('operation_type', 'final_visa')->pluck('employee_id');
        $users = User::whereIn('id', $userIds)->whereIn('id', $EssentailsEmployeeOperation_emplyeeIds)->where('user_type', 'worker')->where('status', 'inactive');



        if (request()->ajax()) {

            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

                $users = $users->where('users.assigned_to', request()->input('project_name'));
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
                ->addColumn('worker', function ($user) {
                    return $user->first_name . ' ' . $user->last_name;
                })
                ->addColumn('contact_name', function ($user) {
                    return $user->assignedTo?->name;
                })


                ->addColumn('building', function ($user) {
                    return $user->rooms?->building->name;
                })

                ->addColumn('building_address', function ($user) {
                    return $user->rooms?->building->address;
                })

                ->addColumn('room_number', function ($user) {
                    return $user->rooms?->room_number;
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

                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })
                ->filterColumn('worker', function ($query, $keyword) {
                    $query->where('first_name', 'LIKE', "%{$keyword}%")->orWhere('last_name', 'LIKE', "%{$keyword}%");
                })

                ->rawColumns(['nationality', 'worker', 'residence_permit_expiration', 'contract_end_date'])
                ->make(true);
        }

        return view('housingmovements::projects_workers.final_exit')->with(compact('contacts', 'nationalities', 'ContactsLocation'));
    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        // if (!auth()->user()->can('user.view')) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');

        $user = User::with(['contactAccess', 'assignedTo', 'OfficialDocument', 'proposal_worker'])
            ->find($id);



        $documents = null;

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
        }




        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();
        $deliveryDocument =  FollowupDeliveryDocument::where('user_id', $user->id)->get();

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


        $bookedInfo =  HousingMovementsWorkerBooking::where('user_id', $user->id)->first();

        return view('housingmovements::projects_workers.show')->with(compact(
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
            'deliveryDocument',
            'bookedInfo',
        ));
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_add_wroker = auth()->user()->can('housingmovements.create_worker');
        if (!($is_admin || $can_add_wroker)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $username_ext = $this->moduleUtil->getUsernameExtension();
        // $locations = BusinessLocation::where('business_id', $business_id)
        //     ->Active()
        //     ->get();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = SalesProject::pluck('name', 'id')->toArray();
        $contacts = [null => __('essentials::lang.undefined')] + $contacts;
        $shifts = Shift::pluck('name', 'id');
        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];



        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $countries = $countries = EssentialsCountry::forDropdown();
        $resident_doc = null;
        $user = null;
        $designations = Category::forDropdown($business_id, 'hrm_designation');

        $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');
        $pay_comoponenets = EssentialsAllowanceAndDeduction::forDropdown($business_id);

        $user = !empty($data['user']) ? $data['user'] : null;

        $allowance_deduction_ids = [];
        if (!empty($user)) {
            $allowance_deduction_ids = EssentialsUserAllowancesAndDeduction::where('user_id', $user->id)
                ->pluck('allowance_deduction_id')
                ->toArray();
        }

        if (!empty($user)) {
            $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();
        } else {
            $contract = null;
        }

        // $locations = BusinessLocation::forDropdown($business_id, false, false, true, false);
        $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();
        $travel_ticket_categorie = EssentialsTravelTicketCategorie::pluck('name', 'id')->all();
        $contract_types = EssentialsContractType::where('type', '!=', 'تمهير')->pluck('type', 'id')->all();
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');

        $company = Company::all()->pluck('name', 'id');

        return  view('essentials::employee_affairs.workers_affairs.create')
            ->with(compact(
                'departments',
                'countries',
                'spacializations',
                'nationalities',
                'username_ext',
                'blood_types',
                'job_titles',
                'shifts',
                'contacts',
                'company',
                'banks',
                'contract_types',
                'form_partials',
                'resident_doc',
                'user',

                'allowance_types',
                'travel_ticket_categorie',
                'contract_types',
                'nationalities',
                'specializations',
                'professions'

            ));
    }


    public function edit($id)
    {
        return view('housingmovements::edit');
    }


    public function update(Request $request, $id)
    {
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
                    'updated_by' => Auth::user()->id
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



    public function addSIM(Request $request)
    {

        $user = User::findOrFail($request->user);

        $user->update([
            'cell_phone_company' => $request->cell_phone_company,
            'contact_number' => $request->contact_number,
            'has_SIM' => 1,
            'updated_by' => Auth::user()->id
        ]);
        $output = [
            'success' => true,
            'msg' => __('lang_v1.added_success'),
        ];
        return redirect()->back()
            ->with('status', $output);
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
            'updated_by' => Auth::user()->id
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
                'created_by' => auth()->user()->id,
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


    public function addEqama(Request $request)
    {
        if (!$request->id_proof_number || $request->id_proof_number == NULL) {
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
            $numericPart = (int)substr($latestRefNo, 'adv_');
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
            'note' =>  $request->note,
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


    public function medicalExamination()
    {
        $view = 'housingmovements::travelers.medicalExamination';
        return $this->newArrivalUtil->medicalExamination($view);
    }
    public function SIMCard()
    {
        $view = 'housingmovements::travelers.SIMCard';
        return $this->newArrivalUtil->SIMCard($view);
    }
    public function workCardIssuing()
    {
        $view = 'housingmovements::travelers.workCardIssuing';
        return $this->newArrivalUtil->workCardIssuing($view);
    }
    public function medicalInsurance()
    {
        $view = 'housingmovements::travelers.medicalInsurance';
        return $this->newArrivalUtil->medicalInsurance($view);
    }
    public function bankAccounts()
    {
        $view = 'housingmovements::travelers.bankAccounts';
        return $this->newArrivalUtil->bankAccounts($view);
    }
    public function QiwaContracts()
    {
        $view = 'housingmovements::travelers.QiwaContracts';
        return $this->newArrivalUtil->QiwaContracts($view);
    }
    public function residencyPrint()
    {
        $view = 'housingmovements::travelers.residencyPrint';
        return $this->newArrivalUtil->residencyPrint($view);
    }
    public function residencyDelivery()
    {
        $view = 'housingmovements::travelers.residencyDelivery';
        return $this->newArrivalUtil->residencyDelivery($view);
    }
    public function advanceSalaryRequest()
    {
        $view = 'housingmovements::travelers.advanceSalaryRequest';
        return $this->newArrivalUtil->advanceSalaryRequest($view);
    }
}
