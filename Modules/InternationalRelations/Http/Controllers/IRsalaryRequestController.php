<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use App\User;
use DB;
use App\Utils\ModuleUtil;

use Yajra\DataTables\Facades\DataTables;
use Modules\Sales\Entities\SalesSalariesRequest;

class IRsalaryRequestController extends Controller
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
        $can_crud_sales_salary_requests= auth()->user()->can('sales.crud_sales_salary_requests');
       
        if (! $can_crud_sales_salary_requests) {
           
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $salary_requests=salesSalariesRequest::whereIn('worker_id',$userIds)->select(['id','worker_id','salary','file','arrival_period','recruitment_fees']);
        
        if (request()->ajax()) {
                        

            return Datatables::of($salary_requests)
           
            ->editColumn('worker_id', function ($row) {
                return $row->user->last_name . ', ' . $row->user->first_name;
            })

            ->editColumn('nationality', function ($row) {
                return $row->user->country->nationality ?? '' ;
            })
            ->addColumn('profession', function ($row) use ($appointments, $professions) {
                
                $professionId = $appointments[$row->worker_id] ?? null;
            
                if ($professionId !== null) {
                    return $professions[$professionId] ?? '';
                }
            
                
                return $row->user->transactionSellLine?->service->profession->name ?? '';
            })
            // ->addColumn(
            //     'action',
            //     function ($row) use($is_admin) {
            //         $html = '';
            //         if ($is_admin) {
            //         $html .= '<button class="btn btn-xs btn-primary open-edit-modal" data-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</button>';
            //         $html .= '<button class="btn btn-xs btn-danger delete_salary_request_button" data-href="' . route('salay_request.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
            //         }
            //         return $html;
            //     }
            // )
           
           
            
            ->rawColumns(['action'])
            ->make(true);
        
        
            }


        
        return view('internationalrelations::salaryRequest.index');
    }
   

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('internationalrelations::create');
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
