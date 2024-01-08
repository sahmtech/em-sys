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
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Spatie\Activitylog\Models\Activity;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use App\Contact;
use App\ContactLocation;
use App\User;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\Sales\Entities\SalesProject;

class ProjectWorkersController extends Controller
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


        // $can_crud_workers = auth()->user()->can('followup.crud_workers');
        // if (!$can_crud_workers) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $contacts = SalesProject::all()->pluck('name', 'id');
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $users = User::with(['rooms'])
            ->where('user_type', 'worker')
            ->leftjoin('contact_locations', 'contact_locations.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);

        $users->select(
            'users.*',
            'users.room_id',
            'users.id_proof_number',
            'users.nationality_id',
            'users.essentials_salary',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'contact_locations.name as contact_name'
        );
        if (!$is_admin) {
            $userProjects = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();
                if ($accessRole) {
                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                }
            }
            $userProjects = array_unique($userProjects);
            $users = $users->whereIn('users.assigned_to',   $userProjects);
        }


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

        return view('housingmovements::projects_workers.index')->with(compact('contacts', 'nationalities', 'ContactsLocation'));
    }

    public function available_shopping()
    {

        $business_id = request()->session()->get('user.business_id');


        // $can_crud_workers = auth()->user()->can('followup.crud_workers');
        // if (!$can_crud_workers) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $contacts = SalesProject::all()->pluck('name', 'id');
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $bookedWorker_ids = HousingMovementsWorkerBooking::all()->pluck('user_id');
        $users = User::with(['rooms'])
            ->where('user_type', 'worker')->whereNull('assigned_to')->whereNotIn('id', $bookedWorker_ids);


        if (!$is_admin) {
            $userProjects = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();
                if ($accessRole) {
                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                }
            }
            $userProjects = array_unique($userProjects);
            $users = $users->whereIn('users.assigned_to',   $userProjects);
        }


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
                    function ($row) {

                        $html = '';

                        $html .= '
                        <a href="' . route('worker.book', ['id' => $row->id])  . '"
                        data-href="' . route('worker.book', ['id' => $row->id])  . ' "
                         class="btn btn-xs btn-modal btn-info edit_car_button" style="width: 50px;"  data-container="#book_worker_model"><i class="fa fa-bookmark cursor-pointer" style="padding: 5px;
                         font-size: smaller;"></i>' . __("housingmovements::lang.book") . '</a>';
                        return $html;
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

    public function reserved_shopping()
    {


        $business_id = request()->session()->get('user.business_id');


        $can_crud_workers = auth()->user()->can('followup.crud_workers');
        if (!$can_crud_workers) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

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
        $fields = $this->moduleUtil->getWorkerFields();
        $users = HousingMovementsWorkerBooking::all();



        if (!$is_admin) {
            $userProjects = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();
                if ($accessRole) {
                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                }
            }
            $userProjects = array_unique($userProjects);
            $users = $users->whereIn('users.assigned_to',   $userProjects);
        }

        if (request()->ajax()) {
            // if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

            //     $users = $users->where('users.assigned_to', request()->input('project_name'));
            // }

            // if (!empty(request()->input('status_fillter')) && request()->input('status_fillter') !== 'all') {

            //     $users = $users->where('users.status', request()->input('status_fillter'));
            // }

            // if (!empty(request()->input('nationality')) && request()->input('nationality') !== 'all') {

            //     $users = $users->where('users.nationality_id', request()->nationality);
            // }

            return Datatables::of($users)
                ->editColumn('worker', function ($row) {
                    return $row->user->id_proof_number . ' - ' . $row->user->first_name . ' ' . $row->user->last_name ?? '';
                })
                ->addColumn('nationality', function ($row) {
                    return optional($row->user->country)->nationality ?? ' ';
                })
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
                    return $row->saleProject->name;
                })
                ->addColumn('essentials_salary', function ($row) {
                    return $row->user->essentials_salary;
                })->addColumn('contact_number', function ($row) {
                    return $row->user->contact_number;
                })
                ->addColumn('total_salary', function ($row) {
                    return $row->user->total_salary;
                }) ->addColumn('gender', function ($row) {
                    return $row->user->gender;
                })
                ->addColumn('categorie_id', function ($row) use ($travelCategories) {
                    $item = $travelCategories[$row->user->categorie_id] ?? '';

                    return $item;
                })  ->addColumn(
                    'action',
                    function ($row) {

                        $html = '';

                        // $html .= '
                        // <a href="' . route('worker.unbook', ['id' => $row->id])  . '"
                        // data-href="' . route('worker.unbook', ['id' => $row->id])  . ' "
                        //  class="btn btn-xs btn-modal btn-danger delete_book_worker_button" style="width: 60px;"  ><i class="fa fa-minus-circle cursor-pointer" style="
                        //  font-size: smaller;"></i>' . __("housingmovements::lang.unbook") . '</a>';
                       
                         $html .= '
                    <button data-href="' .  route('worker.unbook', ['id' => $row->id]) . '" class="btn btn-xs btn-danger delete_book_worker_button"><i class="fa fa-minus-circle cursor-pointer"></i>' . __("housingmovements::lang.unbook") . '</button>
                ';
                         return $html;
                    }
                )
               
               
                ->rawColumns(['action','contact_name','contact_number', 'worker', 'categorie_id', 'gender', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'total_salary', 'essentials_salary'])
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


        // $can_crud_workers = auth()->user()->can('followup.crud_workers');
        // if (!$can_crud_workers) {
        //    //temp  abort(403, 'Unauthorized action.');
        // }

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $contacts = SalesProject::all()->pluck('name', 'id');
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $users = User::where('user_type', 'worker')->where('status', 'inactive');


        // if (!$is_admin) {
        //     $userProjects = [];
        //     $roles = auth()->user()->roles;
        //     foreach ($roles as $role) {

        //         $accessRole = AccessRole::where('role_id', $role->id)->first();
        //         if ($accessRole) {
        //             $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
        //             $userProjects = array_merge($userProjects, $userProjectsForRole);
        //         }
        //     }
        //     $userProjects = array_unique($userProjects);
        //     $users = $users->whereIn('users.assigned_to',   $userProjects);
        // }


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


        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('profession_id');

        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)->name;
        } else {
            $profession = "";
        }

        $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        if ($specializationId !== null) {
            $specialization = EssentialsSpecialization::find($specializationId)->name;
        } else {
            $specialization = "";
        }


        $user->profession = $profession;
        $user->specialization = $specialization;


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
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('housingmovements::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
    }
}