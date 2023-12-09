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
use App\Business;
use DB;
use App\User;
use App\ContactLocation;
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
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        if ((!$is_admin) && (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'sales_module')))) {
            abort(403, 'Unauthorized action.');
        }
        $business_name=Business::where('id', $business_id)->select('name','id')->first();
        $business_name = $business_name ? $business_name->name : null;
      
        $responsible_client = null;
        
        $operations = DB::table('essentials_work_cards')
        ->leftjoin('users as u','essentials_work_cards.employee_id','=','u.id')
        ->leftjoin('contact_locations', 'u.assigned_to', '=', 'contact_locations.id')
        ->leftjoin('essentials_official_documents as doc', 'doc.employee_id', '=', 'u.id')
        ->where('u.business_id', $business_id)
      
        ->select(
            'essentials_work_cards.id as id',
            'essentials_work_cards.work_card_no as card_no',   
            DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
            'doc.number as proof_number',
            'doc.expiration_date as expiration_date',
            'contact_locations.name as project',
            DB::raw("COALESCE('" . optional($responsible_client)->name . "', '') as responsible_client"),
            'essentials_work_cards.workcard_duration as workcard_duration',
            'essentials_work_cards.Payment_number as Payment_number',
            'essentials_work_cards.fixnumber as fixnumber',
            'essentials_work_cards.fees as fees',
           
            DB::raw("'" . $business_name . "' as company_name")
        )  ->orderBy('id', 'desc');

        if (!empty($request->input('project')))
        {
           
           $operations->where('contacts.id', $request->input('project'));
       }
       if (request()->ajax()) {
        return Datatables::of($operations->get()->map(function ($item) {
            $item->project = $item->project ?? __('essentials::lang.management');
          
         // Get responsible client for the project
         $responsible_client = User::join('contacts', 'contacts.responsible_user_id', '=', 'users.id')
         ->where('contacts.supplier_business_name', '=', $item->project)
         ->select('users.id', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"))
         ->get();

         $item->responsible_client = $responsible_client->isEmpty()
           ? ''
         : $responsible_client->pluck('name')->implode(', ');

            return $item;
        }))
        ->addColumn('action', function ($row) {
            $html = '';
            $html .= '<button class="btn btn-xs btn-success btn-modal"  data-href=""><i class="fa fa-edit"></i> ' . __('messages.edit') . '</button>';
            return $html;
        })
        ->rawColumns(['action'])
        ->removeColumn('id')
        ->make(true);
    }
    
    
        $contacts=Contact::where('type','customer')
        ->pluck('name','id');
        return view('essentials::cards.index')->with(compact('contacts'));
    }


    public function getResidencyData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $employeeId = $request->input('employee_id');
       
        $residencyData = User::where('business_id', $business_id)
        ->where('users.id','=', $employeeId)
        ->join('essentials_official_documents as doc','doc.employee_id','=','users.id')
            ->select('doc.id',
             'users.border_no as border_no',
             'users.id_proof_number as residency_no',
             'doc.expiration_date as residency_end_date')->first();
        //dd( $residencyData);
        return response()->json($residencyData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

     public function get_responsible_data(Request $request)
     {
         $employeeId = $request->get('employeeId');
         $business_id = request()->session()->get('user.business_id');
         
         $userType = User::where('id', $employeeId)->value('user_type');
     
         if ($userType !== 'worker') {
             $professionId = 56;
             $responsible_clients = User::where('business_id', $business_id)
                 ->whereHas('appointment', function ($query) use ($professionId) {
                     $query->where('profession_id', $professionId);
                 })
                 ->select('id', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"))
                 ->get();
     
             return response()->json([
                 'all_responsible_users' => [
                     'id' => null,
                     'name' => trans('essentials::lang.management'),
                 ],
                 'responsible_client' => $responsible_clients,
             ]);
         } else {
           
             $all_responsible_users = User::join('contact_locations', 'users.assigned_to', '=', 'contact_locations.id')
                 ->where('users.id', '=', $employeeId)
                 ->select('contact_locations.name', 'contact_locations.id')
                 ->first();
     
             if (!$all_responsible_users) {
                 return response()->json(['error' => 'No responsible users found for the given employee ID']);
             }
     
             $responsible_clients = User::with(['assignedTo'])
                 ->where('contact_locations.id', '=', $all_responsible_users->id)
                 ->select('users.id', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''))
                  as name"))
                 ->get();
     
             return response()->json([
                 'all_responsible_users' => [
                     'id' => $all_responsible_users->id,
                     'name' => $all_responsible_users->name,
                 ],
                 'responsible_client' => $responsible_clients,
             ]);
         }
     }
     
     
     
    public function create(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $employeeId = $request->input('employee_id');
        $all_users = User::where('users.business_id', $business_id)
      
        ->where(function ($query) {
            $query->whereNotNull('users.border_no')
                ->orWhere('users.id_proof_name', 'eqama');

        })
       
        ->select('users.id', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as full_name"))
        ->get();
   

        $responsible_users = User::join('contact_locations', 'users.assigned_to', '=', 'contact_locations.id') 
        ->where('users.id', '=', $employeeId)
        ->select('contact_locations.name', 'contact_locations.id')
        ->get();
 

            
        $responsible_client=user::join('contacts','contacts.responsible_user_id','=','users.id')
        ->where('users.id','=', $employeeId)
        ->select('users.id',DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as full_name")) 
        ->get();

    
        $employees = $all_users->pluck('full_name', 'id');
        $all_responsible_users=$responsible_users->pluck('name', 'id');


        $business=Business::where('id', $business_id)->pluck('name','id');
        $employee=user::with('business') ->where('id','=', $employeeId)
        ->first();

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];

        return view('essentials::cards.create')
            ->with(compact(
            'employees',
            'all_responsible_users',
            'responsible_client',
            'business',
            'employee',
            'durationOptions'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $data = $request->only([
             
                'Residency_no',
            
                'project',
                'workcard_duration',
                'Payment_number',
                'fees',
                'company_name',
                'employee_id',
             

            ]);

 

            $business_id = request()->session()->get('user.business_id');

            $data['employee_id'] = (int)$request->input('employee_id');
           //emp_number
         //  $business_id = request()->session()->get('user.business_id');

          // $numericPart = (int)substr($business_id, 3);
           $lastrecord = WorkCard::orderBy('work_card_no', 'desc')->first();

           if ($lastrecord) {
             
               $lastEmpNumber = (int)substr($lastrecord->work_card_no, 3);

       
              
               $nextNumericPart = $lastEmpNumber + 1;

               $data['work_card_no'] = 'WC' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
           } 
       
           else
            {
             
               $data['work_card_no'] = 'WC' .'000';

           }


            $data['fixnumber'] = 700646447;
 
            $workcard = WorkCard::create($data);
       




            $output = [
                'success' => 1,
                'msg' => __('user.user_added'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

   // return $output;
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
