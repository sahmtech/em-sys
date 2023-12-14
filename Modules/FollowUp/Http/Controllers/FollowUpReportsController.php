<?php

namespace Modules\FollowUp\Http\Controllers;

use App\Category;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Contact;
use App\ContactLocation;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use App\Transaction;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\SalesProject;

class FollowUpReportsController extends Controller
{
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function projectWorkers()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_workers = auth()->user()->can('followup.crud_workers');
        if (!$can_crud_workers) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $contacts_fillter = SalesProject::all()->pluck('name', 'id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $status_filltetr = $this->moduleUtil->getUserStatus();
        $fields = $this->moduleUtil->getWorkerFields();
        $users = User::where('user_type', 'worker')

            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);
        $users->select(
            'users.*',
            'users.id_proof_number',
            'users.nationality_id',
            'users.essentials_salary',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        );

        if (request()->ajax()) {
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {

                $users = $users->where('users.assigned_to', request()->input('project_name'));
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

                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })
                ->addColumn('admissions_date', function ($user) {
                    // return $this->getDocumentnumber($user, 'admissions_date');
                    return optional($user->essentials_admission_to_works)->admissions_date ?? ' ';
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
                ->rawColumns(['nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('followup::reports.projectWorkers')->with(compact('contacts_fillter', 'status_filltetr', 'fields', 'nationalities'));
    }


    public function projects()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_projects = auth()->user()->can('followup.crud_projects');
        if (!$can_crud_projects) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $contacts_fillter = Contact::all()->pluck('supplier_business_name', 'id');


        $contacts = Contact::whereIn('type', ['customer', 'lead'])

            ->with([
                'transactions', 'transactions.salesContract', 'salesProject', 'salesProject.users',
                'transactions.salesContract.salesOrderOperation'

            ]);


        if (request()->ajax()) {
            $contracts = salesContract::with([
                'transaction.contact.user',
                'salesOrderOperation',
            ]);

            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
                $contacts->where('id', request()->input('project_name'));
            }

            return Datatables::of($contacts)
                ->addColumn('contact_name', function ($contact) {
                    return $contact->supplier_business_name ?? null;
                })
                ->addColumn('project', function ($contact) {
                    return $contact->name ?? null;
                })
                ->addColumn('number_of_contract', function ($contact) {
                    return $contact->transactions?->salesContract?->number_of_contract ?? null;
                })
                ->addColumn('start_date', function ($contact) {
                    return $contact->transactions?->salesContract?->start_date ?? null;
                })
                ->addColumn('end_date', function ($contact) {
                    return $contact->transactions?->salesContract?->end_date ?? null;
                })
                ->addColumn('active_worker_count', function ($contact) {
                    return optional($contact->contactLocation)->sum(function ($location) {
                        return $location->assignedTo
                            ->where('user_type', 'worker')
                            ->where('status', 'active')
                            ->count();
                    }) ?? 0;
                })
                ->addColumn('worker_count', function ($contact) {
                    return optional($contact->contactLocation)->sum(function ($location) {
                        return $location->assignedTo
                            ->where('user_type', 'worker')

                            ->count();
                    }) ?? 0;
                })->addColumn('duration', function ($contact) {
                    $startDate = $contact->transactions?->salesContract?->start_date ?? null;
                    $endDate = $contact->transactions?->salesContract?->end_date ?? null;

                    if ($startDate && $endDate) {
                        $startDate = \Carbon\Carbon::parse($startDate);
                        $endDate = \Carbon\Carbon::parse($endDate);

                        $duration = $startDate->diff($endDate);

                        $years = $duration->y > 0 ? ($duration->y == 1 ? $duration->y . ' ' . trans('sales::lang.year') : $duration->y . ' ' . trans_choice('sales::lang.years', $duration->y)) : '';
                        $months = $duration->m > 0 ? ($duration->m == 1 ? $duration->m . ' ' . trans('sales::lang.month') : $duration->m . ' ' . trans_choice('sales::lang.months', $duration->m)) : '';
                        $days = $duration->d > 0 ? ($duration->d == 1 ? $duration->d . ' ' . trans('sales::lang.day') : $duration->d . ' ' . trans_choice('sales::lang.days', $duration->d)) : '';

                        $durationString = implode(', ', array_filter([$years, $months, $days]));

                        return $durationString;
                    }

                    return null;
                })->filterColumn('project', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })


                ->rawColumns([
                    'contact_name', 'number_of_contract', 'start_date', 'end_date', 'duration',
                    'active_worker_count', 'worker_count'

                ])

                ->make(true);;
        }

        return view('followup::reports.projects')->with(compact('contacts_fillter'));
    }


    public function chooseFields_projectsworker()
    {
        return view('followup::reports.chooseFields');
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
        return view('followup::show');
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
