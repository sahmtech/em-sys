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
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
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

        // if (!($is_admin)) {
        //     return redirect()->route('home')->with('status', [
        //         'success' => false,
        //         'msg' => __('message.unauthorized'),
        //     ]);
        // }
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

        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $residencies = EssentialsOfficialDocument::with(['employee'])
            ->where('type', 'residence_permit')
            ->where('is_active', 1)

            ->whereDate('expiration_date', '<', $today)
            ->orderBy('id', 'desc')
            ->latest('created_at')
            ->get();

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
                })->addColumn('passport_number', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument
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

        return view('reports.expired_residencies');
    }



    public function residencies_almost_finished()
    {
        $today = Carbon::now();
        $after_15_days = Carbon::now()->addDays(15);
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');

        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

        $residencies = EssentialsOfficialDocument::where('type', 'residence_permit')
            ->where('is_active', 1)
            ->whereBetween('expiration_date', [$today, $after_15_days])
            ->orderBy('id', 'desc')
            ->latest('created_at')
            ->get();

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
                })->addColumn('passport_number', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument
                        ->where('type', 'passport')
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument
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

            $contracts = salesContract::whereBetween('end_date', [$today, $after_15_days])->join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')->select([
                'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file', 'sales_contracts.contract_duration',
                'sales_contracts.contract_per_period',
                'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
            ]);

            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $contracts->where('sales_contracts.status', request()->input('status'));
            }
            if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                $contracts->where('transactions.contract_form', request()->input('contract_form'));
            }

            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->contact_id] ?? '';

                    return $item;
                })
                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action'])
                ->make(true);
        }






        return view('reports.contracts_almost_finished');
    }


    public function expired_contracts()
    {

        $business_id = request()->session()->get('user.business_id');


        $contacts = Contact::all()->pluck('supplier_business_name', 'id');

        if (request()->ajax()) {

            $today = Carbon::now();


            $contracts = salesContract::whereDate('end_date', '<', $today)->join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')->select([
                'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file', 'sales_contracts.contract_duration',
                'sales_contracts.contract_per_period',
                'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
            ]);

            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $contracts->where('sales_contracts.status', request()->input('status'));
            }
            if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                $contracts->where('transactions.contract_form', request()->input('contract_form'));
            }

            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->contact_id] ?? '';

                    return $item;
                })
                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action'])
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

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })

                ->filterColumn('number', function ($query, $keyword) {
                    $query->where('number', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $workers = User::whereIn('users.id', $userIds)
            ->whereNot('status', 'inactive')
            ->whereDoesntHave('htrRoomsWorkersHistories', function ($query) {
                $query->where('still_housed', '=', 1);
            })
            ->select(
                'users.id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''), ' - ',COALESCE(users.id_proof_number,'')) as full_name")
            )
            ->pluck('full_name', 'users.id');


        $roomStatusOptions = [
            'busy' => __('housingmovements::lang.busy_rooms'),
            'available' => __('housingmovements::lang.available_rooms'),
        ];
        return view('reports.rooms_and_beds')->with(compact('buildings', 'workers', 'roomStatusOptions'));
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
        $business_id = request()->session()->get('user.business_id');
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
        // dd($insurances->where('essentials_employees_insurances.employee_id', 1730)->get());

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

                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?", ["%$keyword%"])
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
            ->pluck(
                'essentials_employees_insurances.employee_id',

            )->toArray();
        $array = array_diff($userIds, $insurances);
      
        $worker_uninsurances = User::whereIn('id',$array)->where('users.user_type', 'employee')->where('users.status', '!=', 'inactive');
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

                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?", ["%$keyword%"])
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


        return view('reports.employee_without_medical_insurance');
    }

    public function worker_without_medical_insurance()
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
            ->leftjoin('essentials_employees_families', 'essentials_employees_families.id', 'essentials_employees_insurances.family_id')
            ->where(function ($query) use ($userIds) {
                $query->whereHas('user', function ($query1) use ($userIds) {
                    $query1->whereIn('users.id', $userIds)->where('users.user_type', 'worker')->where('users.status', '!=', 'inactive');
                })
                    ->orWhereHas('essentialsEmployeesFamily', function ($query2) use ($userIds) {
                        $query2->whereIn('essentials_employees_families.employee_id', $userIds);
                    });
            })
            ->where('essentials_employees_insurances.is_deleted', 0)
            ->pluck(
                'essentials_employees_insurances.employee_id',

            )->toArray();
        $array = array_diff($userIds, $insurances);
      
        $worker_uninsurances = User::whereIn('id',$array)->where('users.user_type', 'worker')->where('users.status', '!=', 'inactive');
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

                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?", ["%$keyword%"])
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


        return view('reports.worker_without_medical_insurance');
    }

    public function worker_medical_insurance()
    {
        $business_id = request()->session()->get('user.business_id');
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
                    $query1->whereIn('users.id', $userIds)->where('users.user_type', 'worker')->where('users.status', '!=', 'inactive');
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
        // dd($insurances->where('essentials_employees_insurances.employee_id', 1730)->get());

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

                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?", ["%$keyword%"])
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
}