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
use App\Contact;
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


        $assgin_to_client = contact::where('type', 'customer')
        ->join('users as u','u.assigned_to','=','contacts.id')
         ->select('contacts.id','contacts.supplier_business_name')
         ->get();

        $operations = DB::table('essentials_work_cards')
        ->join('users as u','essentials_work_cards.employee_id','=','u.id')
        ->join('contacts', 'u.assigned_to', '=', 'contacts.id')
        ->join('essentials_official_documents as doc', 'doc.employee_id', '=', 'u.id')
        ->where('u.business_id', $business_id)
      
        ->select(
            'essentials_work_cards.id as id',
            'essentials_work_cards.work_card_no as card_no',   
            DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
            'doc.number as proof_number',
            'doc.expiration_date as expiration_date',

            'contacts.supplier_business_name as project',
            'essentials_work_cards.workcard_duration as workcard_duration',
            'essentials_work_cards.Payment_number as Payment_number',
            'essentials_work_cards.fixnumber as fixnumber',
            'essentials_work_cards.fees as fees',
            'essentials_work_cards.company_name as company_name',
        );

        if (request()->ajax()) {


            return Datatables::of($operations)
            

                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<button class="btn btn-xs btn-success btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-edit"></i> ' . __('messages.edit') . '</button>';


                    return $html;
                })

                ->rawColumns(['action'])
                ->removeColumn('id')
                ->make(true);
        }

        $contacts=Contact::where('type','customer')->pluck('supplier_business_name','id');
        return view('essentials::cards.index')->with(compact('contacts'));
    }


    public function getResidencyData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $employeeId = $request->input('employee_id');

        $residencyData = User::where('business_id', $business_id)
        ->join('essentials_official_documents as doc','doc.employee_id','=','users.id')
            ->select('doc.id', 'doc.number as residency_no','doc.expiration_date as residency_end_date')->first();

        return response()->json($residencyData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {

        $business_id = request()->session()->get('user.business_id');
       
       $all_users = User::where('users.business_id', $business_id)
        ->select('users.id',
            DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as full_name")
        ) ->where('users.user_type', 'worker')
        ->get();

     $responsible_users = contact::where('type', 'customer')
   ->join('users as u','u.assigned_to','=','contacts.id')
    ->select('contacts.id','contacts.supplier_business_name')
    ->get();
 

 $responsible_client=user::join('contacts','contacts.responsible_user_id','=','users.id')
 ->select('users.id',DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as full_name")) 
->get();

    
        $employees = $all_users->pluck('full_name', 'id');
        $all_responsible_users=$responsible_users->pluck('supplier_business_name', 'id');

        return view('essentials::cards.create')
            ->with(compact('employees','all_responsible_users','responsible_client'));
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

               $data['work_card_no'] = 'WC' . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
           } 
       
           else
            {
             
               $data['work_card_no'] = 'WC' .'00';

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
