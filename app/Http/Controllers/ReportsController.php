<?php

namespace App\Http\Controllers;

use App\Category;
use App\Contact;
use App\ContactLocation;
use App\Report;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsCity;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;

use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\FollowUp\Entities\FollowupUserAccessProject;
use Modules\HousingMovements\Entities\Car;
use Modules\HousingMovements\Entities\HousingMovementsCarsChangeOil;
use Modules\HousingMovements\Entities\HousingMovementsMaintenance;
use Modules\HousingMovements\Entities\HtrBuilding;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\salesContractItem;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;


class ReportsController extends Controller
{
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function landing()
    {
        $reports = Report::all();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (!$is_admin) {
            $reports = Report::whereIn('id', $this->moduleUtil->allowedReports())->get();
        }
        $cardsOfReports = [];
        foreach ($reports as $report) {
            $cardsOfReports[] = [
                'id' => $report->id,
                'name' => $report->name,
                'link' => $report->link ? route($report->link) : '',
            ];
        }

        return view('reports.reports_landing_page')->with(compact('cardsOfReports'));
    }

    public function expired_residencies()
    {
        $today = today()->format('Y-m-d');
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

        $all_expired_residencies = EssentialsOfficialDocument::with(['employee'])->whereIn('employee_id',$userIds)

            ->where('type', 'residence_permit')
            ->where('is_active', 1)

            ->whereDate('expiration_date', '<', $today)
            ->orderBy('id', 'desc')
            ->latest('created_at');

        if (request()->ajax()) {
            return DataTables::of($all_expired_residencies)
                ->addColumn('worker_name', function ($row) {
                    return $row->employee?->first_name .
                        ' ' .

                        $row->employee->mid_name . ' ' . $row->employee->last_name;
                })
                ->addColumn('residency', function ($row) {
                    return $row->number;
                })
                ->addColumn('gender', function ($row) {
                    return $row->employee->gender ?? " ";
                })
                ->addColumn('customer_name', function ($row) {

                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->supplier_business_name ?? null;
                    }
                })
                ->addColumn('project', function ($row) {
                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->salesProjects()->first()->name ?? null;
                    }
                })
                ->addColumn('end_date', function ($row) {
                    return $row->expiration_date;
                })
                ->addColumn('nationality', function ($row) {
                    return optional($row->employee->country)->nationality ?? ' ';
                })
                ->addColumn('company_name', function ($row) {
                    return optional($row->employee->company)->name ?? ' ';
                })
                ->addColumn('dob', function ($row) {
                    return $row->employee->dob ?? '';
                })
                ->addColumn('passport_number', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('type', 'passport')
                        ->where('is_active', 1)
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {
                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('type', 'passport')
                        ->where('is_active', 1)
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->employee->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';

                    return $professionName;
                })
                ->addColumn('border_no', function ($row) {
                    return $row->employee->border_no ?? ' ';
                })
                ->filterColumn('worker_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%$keyword%")
                            ->orWhere('mid_name', 'like', "%$keyword%")
                            ->orWhere('last_name', 'like', "%$keyword%");
                    });
                })



                ->addColumn('action', 'border_no', 'nationality', 'profession', 'passport_expire_date', 'passport_number', 'dob', 'company_name')

                ->filterColumn('worker_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%$keyword%")
                            ->orWhere('mid_name', 'like', "%$keyword%")
                            ->orWhere('last_name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('residency', function ($query, $keyword) {
                    $query->where('number', 'like', "%$keyword%");
                })


                ->rawColumns([
                    'worker_name',
                    'residency',
                    'project',
                    'end_date',
                    'action',
                ])
                ->make(true);
        }

        return view('reports.expired_residencies');
    }

    public function residencies_almost_finished()
    {

        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');


        $residencies = EssentialsOfficialDocument::with(['employee'])->whereIn('employee_id',$userIds)->where(
            'type',
            'residence_permit'
        )
            ->whereBetween('expiration_date', [
                now(),
                now()
                    ->addDays(15)
                    ->endOfDay(),
            ])

            ->where('is_active', 1)
            ->latest('created_at');

        if (request()->ajax()) {
            return DataTables::of($residencies)
                ->addColumn('worker_name', function ($row) {
                    return $row->employee?->first_name .
                        ' ' .
                        $row->employee?->last_name;
                })
                ->addColumn('residency', function ($row) {
                    return $row->number;
                })
                ->addColumn('customer_name', function ($row) {

                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->supplier_business_name ?? null;
                    }
                })
                ->addColumn('project', function ($row) {
                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->salesProjects()->first()->name ?? null;
                    }
                })
                ->addColumn('end_date', function ($row) {
                    return $row->expiration_date;
                })
                ->addColumn('nationality', function ($row) {
                    return optional($row->employee->country)->nationality ?? ' ';
                })
                ->addColumn('company_name', function ($row) {
                    return optional($row->employee->company)->name ?? ' ';
                })
                ->addColumn('dob', function ($row) {
                    return $row->employee->dob ?? '';
                })
                ->addColumn('gender', function ($row) {
                    return $row->employee->gender ?? '';
                })
                ->addColumn('passport_number', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('is_active', 1)
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('is_active', 1)
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->employee->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';

                    return $professionName;
                })
                ->addColumn('border_no', function ($row) {
                    return $row->employee->border_no ?? ' ';
                })


                ->addColumn('action', 'border_no', 'nationality', 'profession', 'passport_expire_date', 'passport_number', 'dob', 'company_name')

                ->removeColumn('id')
                ->rawColumns([
                    'worker_name',
                    'residency',
                    'project',
                    'end_date',
                    'action',
                ])
                ->make(true);
        }

        return view('reports.residencies_almost_finished');
    }


    public function contracts_almost_finished()
    {

        $business_id = request()->session()->get('user.business_id');


        $contacts = Contact::all()->pluck('supplier_business_name', 'id');

        if (request()->ajax()) {

            $today = Carbon::now();
            $after_15_days = Carbon::now()->addDays(15);

            $contracts = salesContract::whereBetween('end_date', [$today, $after_15_days])
                ->join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')
                ->select([
                    'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                    'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file', 'sales_contracts.contract_duration',
                    'sales_contracts.contract_per_period',
                    'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
                ]);

            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->contact_id] ?? '';

                    return $item;
                })
                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('contract_form', function ($query, $keyword) {
                    $query->whereRaw("transactions.contract_form like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('supplier_business_name', function ($query, $keyword) {
                    $query->whereHas('contact', function ($q) use ($keyword) {
                        $q->where('supplier_business_name', 'like', "%{$keyword}%");
                    });
                })


                ->make(true);
        }

        return view('reports.contracts_almost_finished');
    }


    public function expired_contracts()
    {
        $contacts = Contact::all()->pluck('supplier_business_name', 'id');

        if (request()->ajax()) {

            $today = Carbon::now();

            $contracts = salesContract::with(['transaction', 'transaction.contact'])
                ->whereDate('end_date', '<', $today)
                ->join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')
                ->select([
                    'sales_contracts.number_of_contract',
                    'sales_contracts.id',
                    'sales_contracts.offer_price_id',
                    'sales_contracts.start_date',
                    'sales_contracts.end_date',
                    'sales_contracts.status',
                    'sales_contracts.file',
                    'sales_contracts.contract_duration',
                    'sales_contracts.contract_per_period',
                    'transactions.contract_form as contract_form',
                    'transactions.contact_id',
                    'transactions.id as tra'
                ]);



            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->contact_id] ?? '';
                    return $item;
                })


                ->filterColumn('sales_project_id', function ($query, $keyword) {
                    $query->whereHas('transaction.contact', function ($q) use ($keyword) {
                        $q->where('supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contract_form', function ($query, $keyword) {
                    $query->whereHas('transaction', function ($q) use ($keyword) {
                        $q->where('contract_form', 'like', "%{$keyword}%");
                    });
                })

                ->make(true);
        }
        return view('reports.expired_contracts');
    }

    public function rooms_and_beds()
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $buildings = DB::table('htr_buildings')->get()->pluck('name', 'id');
        $rooms = DB::table('htr_rooms')
            ->select(['id', 'room_number', 'htr_building_id', 'area', 'beds_count', 'contents', 'total_beds'])
            ->orderBy('id', 'desc');
        if (request()->ajax()) {
            return Datatables::of($rooms)


                ->editColumn('htr_building_id', function ($row) use ($buildings) {
                    $item = $buildings[$row->htr_building_id] ?? '';

                    return $item;
                })


                ->filterColumn('number', function ($query, $keyword) {
                    $query->where('number', 'like', "%{$keyword}%");
                })
                ->filterColumn('htr_building_id', function ($query, $keyword) use ($buildings) {
                    $query->whereIn('htr_building_id', function ($q) use ($buildings, $keyword) {
                        $q->select('id')
                            ->from('htr_buildings')
                            ->where('name', 'like', "%{$keyword}%");
                    });
                })


                ->make(true);
        }




        return view('reports.rooms_and_beds')
            ->with(compact('buildings'));
    }

    public function building()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $query = User::whereIn('users.id', $userIds)->whereNot('status', 'inactive');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $cities = EssentialsCity::forDropdown();

        $buildings = HtrBuilding::select([
            'id', 'name', 'city_id', 'address',
            'guard_ids_data',
            'supervisor_ids_data', 'cleaner_ids_data',
            'building_contract_end_date'
        ])
            ->orderBy('id', 'desc');

        if (request()->ajax()) {


            return Datatables::of($buildings)
                ->editColumn('city_id', function ($row) use ($cities) {
                    $item = $cities[$row->city_id] ?? '';

                    return $item;
                })
                ->editColumn('guard_id', function ($row) use ($users) {
                    if ($row->guard_ids_data != null) {
                        $guardIds = json_decode($row->guard_ids_data, true);
                        $guardNames = [];
                        foreach ($guardIds as $guardId) {
                            $guardNames[] = $users[$guardId] ?? '';
                        }
                        return implode('<br>', $guardNames);
                    } else {
                        return '';
                    }
                })
                ->editColumn('supervisor_id', function ($row) use ($users) {
                    if ($row->supervisor_ids_data != null) {
                        $guardIds = json_decode($row->supervisor_ids_data, true);
                        $guardNames = [];
                        foreach ($guardIds as $guardId) {
                            $guardNames[] = $users[$guardId] ?? '';
                        }
                        return implode('<br>', $guardNames);
                    } else {
                        return '';
                    }
                })
                ->editColumn('cleaner_id', function ($row) use ($users) {
                    if ($row->cleaner_ids_data != null) {
                        $guardIds = json_decode($row->cleaner_ids_data, true);
                        $guardNames = [];
                        foreach ($guardIds as $guardId) {
                            $guardNames[] = $users[$guardId] ?? '';
                        }
                        return implode('<br>', $guardNames);
                    } else {
                        return '';
                    }
                })


                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->rawColumns(['action', 'guard_id', 'supervisor_id', 'cleaner_id'])
                ->make(true);
        }


        return view('reports.building');
    }


    public function cars_change_oil()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $CarsChangeOil = HousingMovementsCarsChangeOil::all();
        if (request()->ajax()) {
            return DataTables::of($CarsChangeOil)


                ->editColumn('car', function ($row) {
                    return $row->car->CarModel->CarType->name_ar . ' - ' . $row->car->CarModel->name_ar ?? '';
                })

                ->editColumn('current_speedometer', function ($row) {
                    return $row->current_speedometer ?? '';
                })

                ->editColumn('next_change_oil', function ($row) {
                    return  $row->next_change_oil ?? '';
                })
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ?? '';
                })
                ->editColumn('date', function ($row) {
                    return  Carbon::parse($row->date)->format('Y-m-d') ?? '';
                })
                ->rawColumns(['action', 'car'])
                ->make(true);
        }
        return view('reports.movment_changeOil');
    }

    public function car_maintenances(Request $request)
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $carsMaintenance = HousingMovementsMaintenance::all();

        if (request()->ajax()) {

            if (!empty(request()->input('carSelect')) && request()->input('carSelect') !== 'all') {


                $carsMaintenance = $carsMaintenance->where('car_id', request()->input('carSelect'));
            }


            return DataTables::of($carsMaintenance)


                ->editColumn('car', function ($row) {
                    return $row->car->CarModel->CarType->name_ar . ' - ' . $row->car->CarModel->name_ar ?? '';
                })

                ->editColumn('current_speedometer', function ($row) {
                    return $row->current_speedometer ?? '';
                })
                ->editColumn('maintenance_type', function ($row) {
                    return $row->maintenance_type ?? '';
                })
                ->editColumn('maintenance_description', function ($row) {
                    return $row->maintenance_description ?? '';
                })
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ?? '';
                })
                ->editColumn('date', function ($row) {
                    return  Carbon::parse($row->date)->format('Y-m-d') ?? '';
                })


                ->filter(function ($query) use ($request) {

                    // if (!empty($request->input('full_name'))) {
                    //     $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('driver')}%"]);
                    // }
                })

                ->rawColumns(['action', 'car'])
                ->make(true);
        }

        return view('reports.car_maintenances');
    }


    public function employee_medical_insurance()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $insurance_companies = Contact::where('type', 'insurance')
            ->pluck('supplier_business_name', 'id');

        $insurance_classes = EssentialsInsuranceClass::all()
            ->pluck('name', 'id');


            $insurances = EssentialsEmployeesInsurance::with('user', 'user.business')
            ->leftjoin('essentials_employees_families', 'essentials_employees_families.id', 'essentials_employees_insurances.family_id')
            ->where(function ($query) use ($userIds) {
                $query->whereHas('user', function ($query1) use ($userIds) {
                    $query1->whereIn('users.id', $userIds)->where('users.user_type', 'employee')->where('users.status', '!=', 'inactive');
                })
                    ->orWhereHas('essentialsEmployeesFamily', function ($query2) use ($userIds) {
                        $query2->whereIn('essentials_employees_families.employee_id', $userIds);
                    });
            })
            ->where('essentials_employees_insurances.is_deleted', 0)
            ->select(
                'essentials_employees_insurances.employee_id',
                'essentials_employees_insurances.family_id',
                'essentials_employees_families.employee_id as family_employee_id',
                'essentials_employees_insurances.id as id',
                'essentials_employees_insurances.insurance_company_id',
                'essentials_employees_insurances.insurance_classes_id'
            )

            ->orderBy('essentials_employees_insurances.employee_id');

        //dd($insurances->get());

        if (request()->ajax()) {

            return Datatables::of($insurances)
            ->addColumn('user', function ($row) {
                $item = '';

                if ($row->employee_id != null) {
                    $item = $row->user->first_name  . ' ' . $row->user->last_name ?? '';
                    //  $item = $row->english_name;
                } else if ($row->employee_id == null) {
                    $item = $row->essentialsEmployeesFamily->full_name ?? '';
                }

                return $item;
            })

            ->addColumn('english_name', function ($row) {
                $item = '';

                if ($row->employee_id != null) {
                    $item = $row->user->english_name  ?? '';
                }

                return $item;
            })

            ->addColumn('dob', function ($row) {
                $item = '';
                if ($row->employee_id != null) {
                    $item = $row->user->dob ?? '';
                } else if ($row->employee_id == null) {
                    $item = $row->essentialsEmployeesFamily->dob ?? '';
                }
                return $item;
            })

            ->editColumn('fixnumber', function ($row) {
                $item = '';
                if ($row->employee_id != null) {
                    $item = $row->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                        ->first()->unified_number ?? '';
                } else if ($row->employee_id == null) {
                    $item = $row->essentialsEmployeesFamily->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                        ->first()->unified_number ?? '';
                }
                return  $item;
            })


            ->addColumn('proof_number', function ($row) {
                $item = '';
                if ($row->employee_id != null) {
                    $item = $row->user->id_proof_number ?? '';
                } else if ($row->employee_id == null) {
                    $item = $row->essentialsEmployeesFamily->eqama_number ?? '';
                }

                return $item;
            })

            ->editColumn('insurance_company_id', function ($row) use ($insurance_companies) {
                $item = $insurance_companies[$row->insurance_company_id] ?? '';

                return $item;
            })
            ->editColumn('insurance_classes_id', function ($row) use ($insurance_classes) {
                $item = $insurance_classes[$row->insurance_classes_id] ?? '';

                return $item;
            })
          


                ->filterColumn('user', function ($query, $keyword) {

                    $query->whereRaw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("f.full_name LIKE ?", ["%$keyword%"]);
                })

                ->filterColumn('proof_number', function ($query, $keyword) {
                    $query->whereRaw("CASE
                                            WHEN u.id_proof_number IS NOT NULL THEN u.id_proof_number
                                          
                                            ELSE ''
                                        END LIKE ?", ["%$keyword%"]);
                })

                ->make(true);
        }



        return view('reports.employee_medical_insurance');
    }

    public function employee_without_medical_insurance()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        // employee_id

        $insurances = EssentialsEmployeesInsurance::with('user', 'user.business')

            ->where(function ($query) use ($userIds) {
                $query->whereHas('user', function ($query1) use ($userIds) {
                    $query1->whereIn('users.id', $userIds)
                        ->where('users.user_type', 'employee')
                        ->where('users.status', '!=', 'inactive');
                });
            })
            ->where('essentials_employees_insurances.is_deleted', 0)
            ->pluck('essentials_employees_insurances.employee_id')->toArray();
        $array = array_diff($userIds, $insurances);

        $worker_uninsurances = User::whereIn('id', $array)
            ->where('users.user_type', 'employee')
            ->where('users.status', '!=', 'inactive');

        if (request()->ajax()) {

            return Datatables::of($worker_uninsurances)
                ->addColumn('user', function ($row) {
                    $item = '';


                    $item = $row->first_name  . ' ' . $row->mid_name . ' ' . $row->last_name ?? '';

                    return $item;
                })

                ->addColumn('english_name', function ($row) {
                    $item = '';

                    $item = $row->english_name  ?? '';
                    return $item;
                })

                ->addColumn('dob', function ($row) {
                    $item = '';

                    $item = $row->dob ?? '';

                    return $item;
                })

                ->editColumn('fixnumber', function ($row) {
                    $item = '';

                    $item = $row->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                        ->first()->unified_number ?? '';

                    return  $item;
                })


                ->addColumn('proof_number', function ($row) {
                    $item = '';

                    $item = $row->id_proof_number ?? '';


                    return $item;
                })

                ->addColumn('company_name', function ($row) {
                    return optional($row->company)->name ?? ' ';
                })

                ->filterColumn('user', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->where('first_name', 'like', "%{$keyword}%")
                                ->orWhere('last_name', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->where('full_name', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('proof_number', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->where('id_proof_number', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->where('eqama_number', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('fixnumber', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user.business.documents', function ($query) use ($keyword) {
                            $query->where('licence_type', 'COMMERCIALREGISTER')
                                ->where('unified_number', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily.user.business.documents', function ($query) use ($keyword) {
                                $query->where('licence_type', 'COMMERCIALREGISTER')
                                    ->where('unified_number', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('dob', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->whereDate('dob', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->whereDate('dob', 'like', "%{$keyword}%");
                            });
                    });
                })

                ->make(true);
        }
        return view('reports.employee_without_medical_insurance');
    }

    public function worker_without_medical_insurance()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $insurances = EssentialsEmployeesInsurance::with('user', 'user.business')

            ->where(function ($query) use ($userIds) {
                $query->whereHas('user', function ($query1) use ($userIds) {
                    $query1->whereIn('users.id', $userIds)
                        ->where('users.user_type', 'worker')
                        ->where('users.status', '!=', 'inactive');
                });
            })
            ->where('essentials_employees_insurances.is_deleted', 0)
            ->pluck('essentials_employees_insurances.employee_id')->toArray();
        $array = array_diff($userIds, $insurances);

        $worker_uninsurances = User::whereIn('id', $array)->where('users.user_type', 'worker')->where('users.status', '!=', 'inactive');
        // dd($insurances->where('essentials_employees_insurances.employee_id', 1730)->get());

        if (request()->ajax()) {

            return Datatables::of($worker_uninsurances)
                ->addColumn('user', function ($row) {
                    $item = '';


                    $item = $row->first_name  . ' ' . $row->last_name ?? '';
                    //  $item = $row->english_name;



                    return $item;
                })

                ->addColumn('english_name', function ($row) {
                    $item = '';


                    $item = $row->english_name  ?? '';


                    return $item;
                })

                ->addColumn('dob', function ($row) {
                    $item = '';

                    $item = $row->dob ?? '';

                    return $item;
                })

                ->editColumn('fixnumber', function ($row) {
                    $item = '';

                    $item = $row->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                        ->first()->unified_number ?? '';

                    return  $item;
                })


                ->addColumn('proof_number', function ($row) {
                    $item = '';

                    $item = $row->id_proof_number ?? '';


                    return $item;
                })

                ->addColumn('company_name', function ($row) {
                    return optional($row->company)->name ?? ' ';
                })

                ->filterColumn('user', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->where('first_name', 'like', "%{$keyword}%")
                                ->orWhere('last_name', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->where('full_name', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('proof_number', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->where('id_proof_number', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->where('eqama_number', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('fixnumber', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user.business.documents', function ($query) use ($keyword) {
                            $query->where('licence_type', 'COMMERCIALREGISTER')
                                ->where('unified_number', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily.user.business.documents', function ($query) use ($keyword) {
                                $query->where('licence_type', 'COMMERCIALREGISTER')
                                    ->where('unified_number', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('dob', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->whereDate('dob', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->whereDate('dob', 'like', "%{$keyword}%");
                            });
                    });
                })


                ->make(true);
        }


        return view('reports.worker_without_medical_insurance');
    }

    public function worker_medical_insurance()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $insurance_companies = Contact::where('type', 'insurance')
            ->pluck('supplier_business_name', 'id');

        $insurance_classes = EssentialsInsuranceClass::all()
            ->pluck('name', 'id');


        $insurances = EssentialsEmployeesInsurance::with('user', 'user.business')
            ->leftjoin('essentials_employees_families', 'essentials_employees_families.id', 'essentials_employees_insurances.family_id')

            ->where(function ($query) use ($userIds) {
                $query->whereHas('user', function ($query1) use ($userIds) {
                    $query1->whereIn('users.id', $userIds)
                        ->where('users.user_type', 'worker')
                        ->where('users.status', '!=', 'inactive');
                })
                    ->orWhereHas('essentialsEmployeesFamily', function ($query2) use ($userIds) {
                        $query2->whereIn('essentials_employees_families.employee_id', $userIds);
                    });
            })

            ->where('essentials_employees_insurances.is_deleted', 0)
            ->select(
                'essentials_employees_insurances.employee_id',
                'essentials_employees_insurances.family_id',
                'essentials_employees_families.employee_id as family_employee_id',
                'essentials_employees_insurances.id as id',
                'essentials_employees_insurances.insurance_company_id',
                'essentials_employees_insurances.insurance_classes_id'
            )
            ->orderBy('essentials_employees_insurances.employee_id');
        //dd($insurances->get());

        if (request()->ajax()) {

            return Datatables::of($insurances)
                ->addColumn('user', function ($row) {
                    $item = '';

                    if ($row->employee_id != null) {
                        $item = $row->user->first_name  . ' ' . $row->user->last_name ?? '';
                        //  $item = $row->english_name;
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->full_name ?? '';
                    }

                    return $item;
                })

                ->addColumn('english_name', function ($row) {
                    $item = '';

                    if ($row->employee_id != null) {
                        $item = $row->user->english_name  ?? '';
                    }

                    return $item;
                })

                ->addColumn('dob', function ($row) {
                    $item = '';
                    if ($row->employee_id != null) {
                        $item = $row->user->dob ?? '';
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->dob ?? '';
                    }
                    return $item;
                })

                ->editColumn('fixnumber', function ($row) {
                    $item = '';
                    if ($row->employee_id != null) {
                        $item = $row->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                            ->first()->unified_number ?? '';
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                            ->first()->unified_number ?? '';
                    }
                    return  $item;
                })


                ->addColumn('proof_number', function ($row) {
                    $item = '';
                    if ($row->employee_id != null) {
                        $item = $row->user->id_proof_number ?? '';
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->eqama_number ?? '';
                    }

                    return $item;
                })

                ->editColumn('insurance_company_id', function ($row) use ($insurance_companies) {
                    $item = $insurance_companies[$row->insurance_company_id] ?? '';

                    return $item;
                })
                ->editColumn('insurance_classes_id', function ($row) use ($insurance_classes) {
                    $item = $insurance_classes[$row->insurance_classes_id] ?? '';

                    return $item;
                })




                ->filterColumn('user', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->where('first_name', 'like', "%{$keyword}%")
                                ->orWhere('last_name', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->where('full_name', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('proof_number', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->where('id_proof_number', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->where('eqama_number', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('fixnumber', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user.business.documents', function ($query) use ($keyword) {
                            $query->where('licence_type', 'COMMERCIALREGISTER')
                                ->where('unified_number', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily.user.business.documents', function ($query) use ($keyword) {
                                $query->where('licence_type', 'COMMERCIALREGISTER')
                                    ->where('unified_number', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('dob', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->whereHas('user', function ($query) use ($keyword) {
                            $query->whereDate('dob', 'like', "%{$keyword}%");
                        })
                            ->orWhereHas('essentialsEmployeesFamily', function ($query) use ($keyword) {
                                $query->whereDate('dob', 'like', "%{$keyword}%");
                            });
                    });
                })




                ->rawColumns(['action'])
                ->make(true);
        }



        return view('reports.worker_medical_insurance');
    }


    public function final_exit()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

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

        return view('reports.final_exit');
    }


    public function projects()
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';


        $contacts = Contact::whereIn('type', ['customer', 'lead'])

            ->with([
                'transactions', 'transactions.salesContract', 'salesProject', 'salesProject.users',
                'transactions.salesContract.salesOrderOperation'

            ]);


        $salesProjects = SalesProject::with(['contact']);

        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $contacts_ids =   SalesProject::whereIn('id', $followupUserAccessProject)->pluck('contact_id')->unique()->toArray();
            $contacts->whereIn('id',   $contacts_ids);
            $salesProjects =   $salesProjects->whereIn('id', $followupUserAccessProject);
        }



        if (request()->ajax()) {


            return Datatables::of($salesProjects)
                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'contact_name',
                    function ($row) {
                        return $row->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'contact_location_name',
                    function ($row) {
                        return  $row->name;
                    }
                )
                ->addColumn('number_of_contract', function ($row) {
                    return $row->salesContract?->number_of_contract ?? null;
                })
                ->addColumn('start_date', function ($row) {
                    return $row->salesContract?->start_date ?? null;
                })
                ->addColumn('end_date', function ($row) {
                    return $row->salesContract?->end_date ?? null;
                })
                ->addColumn('active_worker_count', function ($row) {

                    return $row->users
                        ->where('user_type', 'worker')
                        ->where('status', 'active')
                        ->count();
                })
                ->addColumn('worker_count', function ($row) {

                    return $row->users
                        ->where('user_type', 'worker')

                        ->count();
                })
                ->addColumn('duration', function ($row) {
                    return $row->contract_duration    ?? null;
                })

                ->addColumn('contract_form', function ($row) {
                    return $row->salesContract?->transaction?->contract_form ?? null;;
                })

                ->addColumn('status', function ($row) {
                    return $row->salesContract?->status     ?? null;;
                })
                ->addColumn('type', function ($row) {
                    return $row->salesContract?->salesOrderOperation?->operation_order_type ?? null;;
                })
                ->filterColumn('contact_name', function ($query, $keyword) {

                    $query->whereHas('contact', function ($qu) use ($keyword) {
                        $qu->where('supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contact_location_name', function ($query, $keyword) {

                    $query->where('name', 'like', "%{$keyword}%");
                })


                ->rawColumns(['id', 'contact_location_name', 'contract_form', 'contact_name', 'active_worker_count', 'worker_count', 'action'])
                ->make(true);
        }


        return view('reports.projects');
    }


    public function project_workers()
    {
        $business_id = request()->session()->get('user.business_id');



        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $is_manager = User::find(auth()->user()->id)->user_type == 'manager';

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();


        if (!($is_admin || $is_manager)) {
            $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
            $userIds = User::whereIn('id',   $userIds)->whereIn('assigned_to',  $followupUserAccessProject)->pluck('id')->toArray();
            $contacts_fillter = SalesProject::whereIn('id',  $followupUserAccessProject)->pluck('name', 'id');
        }

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
        $fields = $this->moduleUtil->getWorkerFields_hrm();
        $users = User::whereIn('users.id', $userIds)->where('user_type', 'worker')

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


            return DataTables::of($users)
                ->addColumn('worker_id', function ($user) {
                    return $user->worker_id ?? ' ';
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
                ->rawColumns(['contact_name', 'worker_id', 'company_name', 'passport_number', 'passport_expire_date', 'worker', 'categorie_id', 'admissions_status', 'admissions_type', 'nationality', 'residence_permit_expiration', 'residence_permit', 'admissions_date', 'contract_end_date'])
                ->make(true);
        }

        return view('reports.projectWorkers')->with(compact('fields'));
    }


    public function employee_almost_finish_contracts()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $today = \Carbon::now();
        $after_15_days = $today->copy()->addDays(15);
        $contract_end_date = EssentialsEmployeesContract::whereIn('employee_id', $userIds)
            ->with(['user'])
            ->whereBetween('contract_end_date', [$today, $after_15_days])
            ->select('contract_end_date', 'employee_id')
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->latest('created_at');

        if (request()->ajax()) {

            return DataTables::of($contract_end_date)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user?->first_name . ' ' . $row->user?->mid_name . ' ' . $row->user?->last_name ?? '';
                    }
                )

                ->addColumn('project', function ($row) {
                    if ($row->user->user_type == 'employee' || $row->user->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->user->assignedTo?->contact
                            ->salesProjects()->first()->name ?? "";
                    }
                })
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user?->assignedTo?->contact?->supplier_business_name ?? "";
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->contract_end_date ?? "";
                    }
                )
                ->filterColumn('worker_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($query) use ($keyword) {
                        $query->where('first_name', 'like', "%$keyword%")
                            ->orWhere('mid_name', 'like', "%$keyword%")
                            ->orWhere('last_name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('project', function ($query, $keyword) {
                    $query->whereHas('user.assignedTo.contact.salesProjects', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('user.assignedTo.contact', function ($query) use ($keyword) {
                        $query->where('supplier_business_name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('end_date', function ($query, $keyword) {
                    $query->whereDate('contract_end_date', 'like', "%$keyword%");
                })
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date'])
                ->make(true);
        }

        return view('reports.employee_almost_finish_contracts');
    }

    public function employee_finish_contracts()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $today = \Carbon::now();
        $contract_end_date = EssentialsEmployeesContract::whereIn('employee_id', $userIds)
            ->with(['user'])
            ->whereDate('contract_end_date', '<=', $today)
            ->select('contract_end_date', 'employee_id')->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->latest('created_at');



        if (request()->ajax()) {

            return DataTables::of($contract_end_date)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user?->first_name . ' ' . $row->user?->mid_name . ' ' . $row->user?->last_name ?? '';
                    }
                )

                ->addColumn('project', function ($row) {
                    if ($row->user?->user_type == 'employee' || $row->user?->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->user->assignedTo?->contact
                            ->salesProjects()->first()->name ?? "";
                    }
                })
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user?->assignedTo?->contact?->supplier_business_name ?? "";
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->contract_end_date ?? "";
                    }
                )
                ->filterColumn('worker_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($query) use ($keyword) {
                        $query->where('first_name', 'like', "%$keyword%")
                            ->orWhere('mid_name', 'like', "%$keyword%")
                            ->orWhere('last_name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('project', function ($query, $keyword) {
                    $query->whereHas('user.assignedTo.contact.salesProjects', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('user.assignedTo.contact', function ($query) use ($keyword) {
                        $query->where('supplier_business_name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('end_date', function ($query, $keyword) {
                    $query->whereDate('contract_end_date', 'like', "%$keyword%");
                })

                ->rawColumns(['worker_name', 'residency', 'project', 'end_date'])
                ->make(true);
        }

        return view('reports.employee_finish_contracts');
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
}