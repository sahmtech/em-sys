<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;
use App\Request as UserRequest;
use Modules\CEOManagment\Entities\RequestsType;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use App\Company;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\InternationalRelations\Entities\RequestTravelCategorie;
use Yajra\DataTables\Facades\DataTables;

class TravelCategorieController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::has('employee_travle_categorie')->whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $travel_ticket_categories = DB::table('essentials_travel_ticket_categories')->pluck('name', 'id');

        $airplane_Companies = DB::table('contacts')->where('type', 'travel_agency')->get();
        $companies = Company::all()->pluck('name', 'id');

        $requestsProcess = null;

        $types = RequestsType::whereIn('type', ['exitRequest', 'returnRequest', 'cancleContractRequest', 'leavesAndDepartures'])->pluck('id')->toArray();
        $allRequestTypes = RequestsType::pluck('type', 'id');

        $requestsProcess = UserRequest::select([
            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.status',
            'requests.note as note', 'requests.start_date', 'requests.end_date', 'requests.has_travel_categorie',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number', 'users.company_id',

            'users.status as userStatus', 'essentials_employee_travel_categories.categorie_id as categorie'

        ])

            ->whereIn('requests.request_type_id', $types)->where('requests.status', 'approved')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->leftJoin('essentials_employee_travel_categories', 'essentials_employee_travel_categories.employee_id', '=', 'users.id')
            ->whereIn('requests.related_to', $userIds);




        if (request()->ajax()) {
            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('categorie', function ($row) use ($travel_ticket_categories) {
                    if ($row->categorie) {
                        return $travel_ticket_categories[$row->categorie];
                    } else {
                        return '';
                    }
                })->editColumn('request_type', function ($row) use ($allRequestTypes) {
                    if ($row->request_type_id) {
                        return $allRequestTypes[$row->request_type_id];
                    } else {
                        return '';
                    }
                })
                ->editColumn('company_id', function ($row) use ($companies) {
                    if ($row->company_id) {
                        return $companies[$row->company_id];
                    }
                })
                ->rawColumns(['categorie', 'request_type'])
                ->make(true);
        }

        return view('internationalrelations::requests.travel_categories')->with(compact('airplane_Companies'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function book_visa(Request $request)
    {


        try {
            $tripDateTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->tripDateTime);
            $tripDate = $tripDateTime->toDateString();
            $tripTime = $tripDateTime->toTimeString();
            $travelRequest = new RequestTravelCategorie();
            $travelRequest->request_id = $request->requestId;
            $travelRequest->travel_agency_id = $request->airplaneCompany;
            $travelRequest->tripDate = $tripDate;
            $travelRequest->tripTime = $tripTime;
            $travelRequest->save();


            UserRequest::where('id', $request->requestId)->update([
                'has_travel_categorie' => 1,
                'is_done' => '1',
            ]);
            $output = [
                'success' => 1,
                'msg' => __('messages.added_success'),
            ];
        } catch (\Exception $e) {
            error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function getVisaData($requestId)
    {
        error_log('getVisaData');

        $tripInfo = RequestTravelCategorie::with('request', 'company')->where('request_id', $requestId)->firstOrFail();

        return response()->json(['tripInfo' => $tripInfo]);
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('internationalrelations::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
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
