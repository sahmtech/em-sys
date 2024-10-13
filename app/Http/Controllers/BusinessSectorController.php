<?php

namespace App\Http\Controllers;


use App\Report;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsCountry;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCity;
use Yajra\DataTables\Facades\DataTables;

class BusinessSectorController extends Controller
{
    protected $moduleUtil;
    protected $statuses;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->statuses = [
            'qualified' => [
                'name' => __('sales::lang.qualified'),
                'class' => 'bg-green',
            ],
            'unqualified' => [
                'name' => __('sales::lang.unqualified'),
                'class' => 'bg-red',
            ],

        ];
    }

    public function landing()
    {



        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $query = User::where('business_id', $business_id);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        $contacts = DB::table('contacts')
            ->select([
                'id',
                'supplier_business_name',
                'type',
                'contact_id',
                'created_by',
                'created_at',
                'commercial_register_no',
                'mobile',
                'email',
                'city',
                'note_draft',
                'name'

            ])->where('business_id', $business_id)->where('type', 'draft')->orderByDesc('created_at');
        $cities = EssentialsCity::forDropdown();
        if (request()->ajax()) {


            return Datatables::of($contacts)



                ->editColumn('created_by', function ($row) use ($users) {

                    return $users[$row->created_by];
                })
                ->editColumn('created_at', function ($row) use ($users) {

                    return Carbon::parse($row->created_at);
                })

                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $status = $this->statuses;
        $nationalities = EssentialsCountry::nationalityForDropdown();
        return view('business_sector.index')->with(compact('users', 'status', 'cities', 'nationalities'));
    }
}
