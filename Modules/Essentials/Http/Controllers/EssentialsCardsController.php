<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Notifications\CustomerNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\BusinessLocation;
use App\Utils\Util;
use DB;
use App\User;
use Modules\Essentials\Entities\WorkCard;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
class EssentialsCardsController extends Controller
{

    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
    
    
        $operations = DB::table('essentials_work_cards')
        ->join('essentials_official_documents', 'essentials_work_cards.Residency_id', '=', 'essentials_official_documents.id')
       ->leftjoin('users as u', 'u.id', '=', 'essentials_official_documents.employee_id')
       ->where('essentials_official_documents.type','=','residence_permit')
       ->where('u.business_id', $business_id)
        ->select(
       'essentials_work_cards.id as id',
        DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
        'essentials_official_documents.number as number',
        'essentials_official_documents.expiration_date as expiration_date',
        'essentials_work_cards.project as project',
        'essentials_work_cards.workcard_duration as workcard_duration',
        'essentials_work_cards.Payment_number as Payment_number',
        'essentials_work_cards.fixnumber as fixnumber',
        'essentials_work_cards.fees as fees',
        'essentials_work_cards.company_name as company_name',
      );

      

 
    //   $operations = DB::table('sales_orders_operations')
    //   ->join('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
    //   ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
    //   ->select(
    //       'sales_orders_operations.id as id',
    //       'sales_orders_operations.operation_order_no as operation_order_no',
    //       'contacts.name as contact_name',
    //       'sales_contracts.number_of_contract as contract_number',
    //       'sales_orders_operations.operation_order_type as operation_order_type',
    //       'sales_orders_operations.Status as Status'
    //   );



    // if (request()->input('number_of_contract')) {
       
    //     $operations->where('sales_contracts.number_of_contract', request()->input('number_of_contract'));
    // }

    // if (request()->input('Status') && request()->input('Status') !== 'all') {
    //     $operations->where('sales_orders_operations.operation_order_type', request()->input('Status'));
     
    // }


        if (request()->ajax()) {
         
        
            return Datatables::of($operations)
            // ->addColumn('show_operation', function ($row) {
              
            //     $html = '';
            //     $html = '<a href="#" data-href="'.action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'show'], [$row->id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a>';
            //     return $html;
            // })
        
            ->addColumn('action', function ($row) {
                $html = '';
                $html .= '<button class="btn btn-xs btn-success btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-edit"></i> ' . __('messages.edit') . '</button>';
                

                     return $html;
           
                })

                ->rawColumns(['action']) 
                ->removeColumn('id')
                ->make(true);
        }
        

        return view('essentials::cards.index');
      
    }

    
    public function getResidencyData(Request $request) {
        $employeeId = $request->input('employee_id');
    
        $residencyData = EssentialsOfficialDocument::where('employee_id', $employeeId)
        ->select('id','expiration_date as residency_end_date','number as residency_no')->first();
    
        return response()->json($residencyData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        
        $business_id = request()->session()->get('user.business_id');
        $employees = User::forDropdown($business_id, false, false, false, true);
        $business_id = request()->session()->get('user.business_id');
        return view('essentials::cards.create')
        ->with(compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $data = $request->only([
                'Residency_id',
                'Residency_no',
                'Residency_end_date' ,
                'project',
                'workcard_duration',
                'Payment_number',
                'fees',
                'company_name',
                'employee_id'
              
            ]);
           
            $docId = (int)$request->input('Residency_id');
           // dd( $employeeId);

            $business_id = request()->session()->get('user.business_id');
           
            // $residencyId =DB::table('essentials_official_documents')
            // ->where('employee_id','=',$request->input('employee_id'))
            // ->select('id')->first();
        


            $data['employee_id']=(int)$request->input('employee_id');

       
            $data['Residency_id']=  $docId;
             $data['fixnumber']=700646447;
        //dd($data);
            $workcard = WorkCard::create( $data );
         //   dd($workcard);

           

            
            $output = ['success' => 1,
                'msg' => __('user.user_added'),
            ];
        } 
        
        
        catch (\Exception $e)
         {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            error_log('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

   //  return $output;
        return redirect()->route('cards');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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
