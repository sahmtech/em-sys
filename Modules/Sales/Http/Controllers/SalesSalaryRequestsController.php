<?php

namespace Modules\Sales\Http\Controllers;

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
class SalesSalaryRequestsController extends Controller
{
    protected $moduleUtil;
   

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
        $business_id = request()->session()->get('user.business_id');
        $can_delete_sales_salary_request= auth()->user()->can('sales.delete_sales_salary_request');
        $can_edit_sales_salary_request= auth()->user()->can('sales.edit_sales_salary_request');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

        $workers=User::where('user_type', 'worker')
             ->select( DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
             ' - ',COALESCE(users.id_proof_number,'')) as full_name"),'users.id',)->get();


        $salary_requests=salesSalariesRequest::select(['id','worker_id','salary','file','arrival_period','recruitment_fees']);
        
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
            ->addColumn(
                'action',
                function ($row) use($is_admin,$can_delete_sales_salary_request,$can_edit_sales_salary_request) {
                    $html = '';
                    if ($is_admin || $can_edit_sales_salary_request) {
                    $html .= '<button class="btn btn-xs btn-primary open-edit-modal" data-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</button>';
                    } if ($is_admin || $can_delete_sales_salary_request) {
                    $html .= '<button class="btn btn-xs btn-danger delete_salary_request_button" data-href="' . route('salay_request.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    }
                    return $html;
                }
            )
           
           
            
            ->rawColumns(['action'])
            ->make(true);
        
        
            }


        
        return view('sales::salary_requests.index')->with(compact('workers','nationalities','professions'));
    }


    public function fetchWorkerDetails($workerId)
        {
            $worker = User::findOrFail($workerId);

            $professions = EssentialsProfession::all()->pluck('name', 'id');
            $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
    
           
            $professionId=null;
            if( $worker )
            {
                $professionId = $appointments[$workerId] ?? null;
           
            
                if ($professionId == null)
                 {
                    $professionId=$worker->transactionSellLine?->service->profession->id;
                }
             $nationality_id=$worker->country->id;
              $nationalities = EssentialsCountry::where('id', $nationality_id)->pluck('nationality', 'id');
              $professions = EssentialsProfession::where('id', $professionId)->pluck('name', 'id');
       
               
            }
            
           
            return response()->json(['nationalities' => $nationalities ,'professions'=>$professions ]);
        }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
      
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


 
        try {
            $input = $request->only(['worker_id', 'salary',
             'arrival_period','recruitment_fees','file']);
            

            $input2['worker_id'] = $input['worker_id'];
            
            $input2['salary'] = $input['salary'];
           
            $input2['arrival_period'] = $input['arrival_period'];
         
            $input2['recruitment_fees'] = $input['recruitment_fees'];
           
            if ($request->hasFile('file')) {
               
                $file = request()->file('file');
               
                $filePath = $file->store('/salesSalaryRequest');

                $input2['file'] = $filePath;
            }
           
         
            $salay=salesSalariesRequest::create($input2);
         
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];

        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

      return redirect()->route('salary-requests-index')->with(['output']);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request, $salaryId)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $professions = EssentialsProfession::all()->pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

    
        try {
            $req = salesSalariesRequest::findOrFail($salaryId);
            $professionId = $appointments[$req->worker_id] ?? null;
        
            if ($professionId == null) {
                $professionId = $req->user->transactionSellLine?->service->profession->id;
            }
        
            
            $existingFile = $req->file??''; 
           
        
            $output = [
                'success' => true,
                'data' => [
                    'worker_id' => $req->worker_id,
                    'salary' => $req->salary,
                    'arrival_period' => $req->arrival_period,
                    'recruitment_fees' => $req->recruitment_fees,
                    'nationality' => $req->user->country->id ?? null,
                    'profession' => $professionId,
                    'file'=>$existingFile
                ],
             
               
              
                'msg' => __('lang_v1.fetched_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        
        return response()->json($output);
        
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $salaryId)
    {
        
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    
  
    
        try {
          
            $req = salesSalariesRequest::find($salaryId);
            $filePath="";
          if( $req)
          {
            if ($request->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('/salesSalaryRequest');

              
            }
            $req->update([
                    'worker_id' => $request->input('workers'),
                    'salary' => $request->input('salary'),
                    'arrival_period' =>  $request->input('arrival_period'),
                    'recruitment_fees' =>  $request->input('recruitment_fees'),
                    'file'=>  $filePath
                
            ]);
    
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
          }
          else{
            $output = [
                'success' => true,
                'msg' => __('lang_v1.no_data'),
            ];
          }
           
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
    
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    
        return response()->json($output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            salesSalariesRequest::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
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
}
