<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Excel;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Yajra\DataTables\Facades\DataTables;

class HousingMovementsController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {

        return view('housingmovements::index');
    }
    public function department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_department_employees = auth()->user()->can('housingmovements.housingmovements_view_department_employees');

        if (!($is_admin || $can_department_employees)) {
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
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->where('name', 'LIKE', '%سكن%')
                ->where('name', 'LIKE', '%حرك%');
        })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereIn('user_type', ['employee', 'manager'])->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
        })->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"),
            'users.id_proof_number',
        ]);
        if (request()->ajax()) {

            return Datatables::of($users)

                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'full_name',
                    function ($row) {
                        return $row->full_name;
                    }
                )
                ->addColumn(
                    'id_proof_number',
                    function ($row) {
                        return $row->id_proof_number;
                    }
                )
                ->addColumn(
                    'appointment',
                    function ($row) {
                        return $row->appointment?->profession->name ?? '';
                    }
                )

                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment'])
                ->make(true);
        }

        return view('housingmovements::department_employees');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('housingmovements::create');
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
        return view('housingmovements::show');
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

    //
    public function importWorkers_newArrival()
    {

        return view('housingmovements::.new_arrival_import');
    }

    // new Arrival
    public function postImportWorkersNewArrival(Request $request)
    {
        $delegation_id = $request->input('delegation_id');
        $agency_id = $request->input('agency_id');
        $transaction_sell_line_id = $request->input('transaction_sell_line_id');
        $unSupportedworker_order_id = $request->input('unSupportedworker_order_id');

        try {
            // Validate the uploaded file
            $request->validate([
                'workers_csv' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            if (!$request->hasFile('workers_csv')) {
                return back()->withErrors(['workers_csv' => 'File not uploaded. Please try again.']);
            }

            $file = $request->file('workers_csv');

            $parsed_array = Excel::toArray([], $file);
            $imported_data = array_splice($parsed_array[0], 1); // Skip header row
            $passport_numbers = [];
            $formated_data = [];

            DB::beginTransaction();

            foreach ($imported_data as $key => $value) {
                $row_no = $key + 2; // Account for header row in Excel

                // Skip empty rows
                if (empty(array_filter($value))) {
                    continue;
                }

                $worker_array = [];

                if (!empty($value[0])) {
                    $worker_array['first_name'] = $value[0];
                } else {
                    throw new \Exception(__('essentials::lang.first_name_required') . " at row $row_no");
                }

                $worker_array['mid_name'] = $value[1];

                if (!empty($value[2])) {
                    $worker_array['last_name'] = $value[2];
                } else {
                    throw new \Exception(__('essentials::lang.last_name_required') . " at row $row_no");
                }

                if (!empty($value[3])) {
                    $worker_array['nationality'] = $value[3];
                } else {
                    throw new \Exception(__('essentials::lang.nationality_required') . " at row $row_no");
                }

                if (!empty($value[4])) {
                    $worker_array['passport_number'] = $value[4];
                } else {
                    throw new \Exception(__('essentials::lang.passport_number_required') . " at row $row_no");
                }

                if (in_array($worker_array['passport_number'], $passport_numbers)) {
                    throw new \Exception(__('lang_v1.the_passport_number_already_exists') . " at row $row_no");
                }

                $existing_passport = IrProposedLabor::where('passport_number', $worker_array['passport_number'])->exists();
                if ($existing_passport) {
                    throw new \Exception(__('lang_v1.the_passport_number_already_exists') . " at row $row_no");
                }
                $passport_numbers[] = $worker_array['passport_number'];

                if (!empty($value[5])) {
                    $worker_array['company_id'] = $value[5];
                } else {
                    throw new \Exception(__('essentials::lang.sponsor_required') . " at row $row_no");
                }

                if (!empty($value[7])) {
                    if (is_numeric($value[7])) {
                        // Convert Excel numeric date
                        $worker_array['arrival_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value[7])->format('Y-m-d');
                    } else {
                        // Convert string date
                        $date = DateTime::createFromFormat('d/m/Y', $value[7]);
                        if ($date) {
                            $worker_array['arrival_date'] = $date->format('Y-m-d');
                        } else {
                            throw new \Exception(__('essentials::lang.invalid_date_format') . " at row $row_no");
                        }
                    }
                } else {
                    throw new \Exception(__('essentials::lang.arrival_date_required') . " at row $row_no");
                }

                $formated_data[] = $worker_array;
            }

            foreach ($formated_data as $worker_data) {
                IrProposedLabor::create($worker_data);
            }

            DB::commit();

            return redirect()->route('proposed_laborIndex')->with('notification', [
                'success' => 1,
                'msg' => __('product.file_imported_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error importing workers: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('notification', [
                'success' => 0,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    public function proposed_laborIndex(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_view_worker_profile = auth()->user()->can('internationalrelations.view_worker_profile');

        if (!($is_admin)) {
        }

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $business_id = request()->session()->get('user.business_id');
        $agencys = Contact::where('type', 'recruitment')->pluck('supplier_business_name', 'id');
        $workers = IrProposedLabor::with('transactionSellLine.service', 'agency', 'unSupportedworker_order')->where('interviewStatus', null)->select([
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(mid_name, ''),' ', COALESCE(last_name, '')) as full_name"),
            'age',
            'gender',
            'email',
            'profile_image',
            'dob',
            'marital_status',
            'blood_group',
            'contact_number',
            'permanent_address',
            'current_address',
            'interviewStatus',
            'agency_id',
            'transaction_sell_line_id',
            'unSupportedworker_order_id',
        ]);

        if (!empty($request->input('specialization'))) {
            $workers->where(function ($query) use ($request) {
                $query->whereHas('transactionSellLine.service', function ($subQuery) use ($request) {
                    $subQuery->where('specialization_id', $request->input('specialization'));
                })
                    ->orWhereHas('unSupportedworker_order', function ($subQuery) use ($request) {
                        $subQuery->where('specialization_id', $request->input('specialization'));
                    });
            });
        }
        if (!empty($request->input('profession'))) {
            $workers->where(function ($query) use ($request) {
                $query->whereHas('transactionSellLine.service', function ($subQuery) use ($request) {
                    $subQuery->where('profession_id', $request->input('profession'));
                })
                    ->orWhereHas('unSupportedworker_order', function ($subQuery) use ($request) {
                        $subQuery->where('profession_id', $request->input('profession'));
                    });
            });
        }

        if (!empty($request->input('agency'))) {
            $workers->where('agency_id', $request->input('agency'));
        }

        if (request()->ajax()) {

            return Datatables::of($workers)

                ->addColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->transactionSellLine?->service?->profession_id] ?? $professions[$row->unSupportedworker_order?->profession_id] ?? '';

                    return $item;
                })
                ->addColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->transactionSellLine?->service?->specialization_id] ?? $specializations[$row->unSupportedworker_order?->specialization_id] ?? '';

                    return $item;
                })

                ->addColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->transactionSellLine?->service?->nationality_id] ?? $nationalities[$row->unSupportedworker_order?->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('agency_id', function ($row) use ($agencys) {

                    return $agencys[$row->agency_id] ?? '';
                })

                ->addColumn('action', function ($row) use ($is_admin, $can_view_worker_profile) {
                    $html = '';
                    if ($is_admin || $can_view_worker_profile) {
                        $html = '<a href="#" data-href="' . action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'], [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a>';
                    }
                    return $html;
                })

                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action', 'profession_id', 'specialization_id', 'nationality_id'])
                ->make(true);
        }

        $interview_status = $this->statuses;
        return view('internationalrelations::worker.proposed_laborIndex')->with(compact('interview_status', 'nationalities', 'specializations', 'professions', 'agencys'));
    }
}
