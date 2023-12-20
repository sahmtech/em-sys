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
use App\Contact;
use DB;
use App\User;
use Carbon\Carbon;
use App\ContactLocation;
use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsResidencyHistory;
use Modules\Essentials\Entities\EssentialsWorkCard;
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
    ) 
    {
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

        $card = EssentialsWorkCard::with([ 'user',
        // 'user.assignedTo.contact.responsibleClients',
          'user.OfficialDocument'])
        ->select('id', 'employee_id', 'work_card_no as card_no', 'fees as fees', 'Payment_number as Payment_number');
    
   //dd($card->first()->user->business->documents->where('licence_type','COMMERCIALREGISTER')->unified_number);

    if (!empty($request->input('project'))) {
        $card->whereHas('user.assignedTo', function ($query) use ($request) {
            $query->where('id', $request->input('project'));
        });
    }

    if (!empty($request->input('proof_numbers')) &&  $request->input('proof_numbers') != "all") {
        $card->whereHas('user', function ($query) use ($request) {
            $query->whereIn('id', $request->input('proof_numbers'));
        });
    }


   
      
       if (request()->ajax()) 
       {
          


        
           return Datatables::of($card)
           

           ->addColumn('checkbox', function ($row) {
               return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
           })

         

           ->editColumn('company_name', function ($row) {
            return $row->user->business?->name ?? '';
           })


           
           ->editColumn('fixnumber', function ($row) {
            return $row->user->business?->documents?->where('licence_type','COMMERCIALREGISTER')->first()->unified_number ?? '';
           })

           ->editColumn('user', function ($row) {
            return $row->user->first_name . ' ' . $row->user->mid_name . ' ' . $row->user->last_name ?? '';
           })

           ->editColumn('project', function ($row) {
            return $row->user->assignedTo?->name ?? '';
            })
            
            // ->editColumn('responsible_client', function ($row) {
            //     $user = $row->user;
            
            //     if ($user && $user->assignedTo) {
            //         $assignedToFirst = $user->assignedTo->first();
            
            //         if ($assignedToFirst && $assignedToFirst->contact) {
            //             $responsibleClients = $assignedToFirst->contact->responsibleClients;
            
            //             if (! empty($responsibleClients)) {
            //                 return $responsibleClients->first_name;
            //             }
            //         }
            //     }
            
            //     return '';
            // })


            ->editColumn('proof_number', function ($row) {
                $residencePermitDocument = $row->user->OfficialDocument
                    ->where('type', 'residence_permit')
                    ->first();
            
                return $residencePermitDocument ? $residencePermitDocument->number : '';
            })

            ->editColumn('expiration_date', function ($row) {
                $residencePermitDocument = $row->user->OfficialDocument
                    ->where('type', 'residence_permit')
                    ->first();
            
                return $residencePermitDocument ? $residencePermitDocument->expiration_date : '';
            })

            ->editColumn('nationality', function ($row) {
                return $row->user->country?->nationality ?? '';
            })
           ->addColumn('action', function ($row) {
             $html='';
              
               return $html;
           })
           ->filter(function ($query) use ($request) {
            
               if (!empty($request->input('full_name'))) {
                   $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
               }
           })

         
         
           ->rawColumns(['action', 'profession', 'nationality','checkbox'])
           ->make(true);
       

       }
    
    
        $sales_projects = SalesProject::pluck('name', 'id');
        
        $proof_numbers=User::where('users.user_type', 'worker')
        ->select( DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"),'users.id')->get();


        return view('essentials::cards.index')->with(compact('sales_projects','proof_numbers'));
    }

    public function residencyreports(Request $request)
    {
        $sales_projects = SalesProject::pluck('name', 'id');
        
        $proof_numbers = User::where('users.user_type', 'worker')
            ->select(DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
            ' - ',COALESCE(users.id_proof_number,'')) as full_name"), 'users.id')
            ->get();
    
        $report = EssentialsResidencyHistory::with(['worker'])->select('*');
        if (!empty($request->input('proof_numbers')) &&  $request->input('proof_numbers') != "all") {
            $report->whereHas('worker', function ($query) use ($request) {
                $query->whereIn('id', $request->input('proof_numbers'));
            });
        }
        if ($request->ajax()) {
            return Datatables::of($report)
                ->editColumn('user', function ($row) {
                    return $row->worker->first_name . ' ' . $row->worker->mid_name . ' ' . $row->worker->last_name ?? '';
                })
                ->make(true);
        }
    
        return view('essentials::cards.reports.residenceReport')->with(compact('sales_projects', 'proof_numbers'));
    }
    public function postRenewData(Request $request)
    {
        try {
        $requestData = $request->only([
        'id',
        'employee_id',
        'number',
        'expiration_date',
        'renew_duration',
        'fees',
        'Payment_number',
        
    ]);

    $jsonData = [];

    foreach ($requestData['id'] as $index => $workerId) {
        $jsonObject = [
            'id' => $requestData['id'][$index],
            'employee_id' => $requestData['employee_id'][$index],
            'number' => $requestData['number'][$index],
            'expiration_date' => $requestData['expiration_date'][$index],
            'renew_duration' => $requestData['renew_duration'][$index],
            'fees' => $requestData['fees'][$index],
            'Payment_number'=>$requestData['Payment_number'][$index],
        ];
    
        $jsonData[] = $jsonObject;
    }
    
    $jsonData = json_encode($jsonData);
  
    if (!empty($jsonData)) {
        $business_id = $request->session()->get('user.business_id');
        $selectedData = json_decode($jsonData, true); 

        DB::beginTransaction();
        foreach ($selectedData as $data) {
         
            $card = EssentialsWorkCard::with(['user.OfficialDocument'])->find($data['id']);
            
            $renewStartDate = Carbon::parse($data['expiration_date']);
            $renewEndDate = $renewStartDate->addMonths($data['renew_duration']);
            
         
            if ($card) {

                EssentialsResidencyHistory::create([
                    'worker_id' => $data['employee_id'],
                    'renew_start_date' => $data['expiration_date'],
                    'residency_number' => $data['number'],
                    'duration' => $data['renew_duration'],
                    'renew_end_date' => $renewEndDate,
                ]);

                $newDuration = $card->workcard_duration + $data['renew_duration'];
               
                $card->update(['workcard_duration' => $newDuration]);
              
              
                $card->update(['fees' => $data['fees']]);

                $card->update(['Payment_number' => $data['Payment_number']]);

              
               $document=EssentialsOfficialDocument::where('type', 'residence_permit')
               ->where('employee_id', $data['employee_id'])
               ->first();
                
               $document->update(['expiration_date' => $renewEndDate]);
            }
           
        }
      

        DB::commit();
    
                $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
            } else {
                $output = ['success' => 0, 'msg' => __('lang_v1.no_data_received')];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }
    
        return redirect()->back()->with(['status' => $output]);
    // return $output;
    }


   
    public function getSelectedRowsData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');
         
       
        $data = EssentialsWorkCard::whereIn('id', $selectedRows)
        ->with([ 'user',
         'user.assignedTo.contact.responsibleClients',
         'user.OfficialDocument'])
       
         ->select('id',
        'employee_id',
        'work_card_no as card_no',
        'fees as fees',
         'workcard_duration',
        'Payment_number as Payment_number',
        'fixnumber as fixnumber')->get();
   
          $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
            $data->pluck('workcard_duration')->unique()->first() => __('essentials::lang.workcard_duration'),
        ];
        foreach ($data as $row) {
            $doc = $row->user->OfficialDocument
                ->where('type', 'residence_permit')
                ->first();
            $fixnumber=  $row->user->business?->documents?->where('licence_type','COMMERCIALREGISTER')->first()->unified_number;

            $row->expiration_date = $doc ? $doc->expiration_date : null;
            $row->number = $doc ? $doc->number : null;
            $row->fixnumber= $fixnumber ?  $fixnumber : null;
        }
      
        return response()->json($data);
    }



    public function getResidencyData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $employeeId = $request->input('employee_id');
       
        $residencyData = User::where('users.id','=', $employeeId)
        ->join('essentials_official_documents as doc','doc.employee_id','=','users.id')
            ->select('doc.id',
             'users.border_no as border_no',
             'users.id_proof_number as residency_no',
             'doc.expiration_date as residency_end_date')->first();
        
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
             
            //  $responsible_clients = User::whereHas('appointment', function ($query) use ($professionId) {
            //          $query->where('profession_id', $professionId);
            //      })
            //      ->select('id', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"))
            //      ->get();
     
             return response()->json([
                 'all_responsible_users' => [
                     'id' => null,
                     'name' => trans('essentials::lang.management'),
                 ],
                // 'responsible_client' => $responsible_clients,
             ]);
         } else 
         {
          
            $projects = User::with(['assignedTo:id,name'])
            ->find($employeeId);

            $assignedProject = $projects->assignedTo;
            $projectName = $assignedProject->name ?? '';
            $projectId = $assignedProject->id ?? '';
           
            $all_responsible_users = [
                'id' => $projectId,
                'name' => $projectName,
            ];
     
             if (!$all_responsible_users) {
                 return response()->json(['error' => 'No responsible users found for the given employee ID']);
             }
     
            //  $assignedresponibleClient =$projects->first()->contact?->responsibleClients;
            // // dd( $projects->assignedTo->first()->contact->responsibleClients->id);
            //  $assignedresponibleClientName = $projects->assignedTo->first()->contact->responsibleClients->first_name ?? '';
            //  $assignedresponibleClientId = $projects->assignedTo->first()->contact->responsibleClients->id ?? '';

            //  $responsible_clients = [
            //     'id' => $assignedresponibleClientId,
            //     'name' => $assignedresponibleClientName,
            // ];
            // dd( $responsible_clients);

            $b_id=user::where('id', $employeeId)->select('business_id')->get();
            $business=Business::where('id', 1)->select('name as name','id as id')->get();
            
            return response()->json([
                 'all_responsible_users' => $all_responsible_users,
                 
               //  'responsible_client' => [$responsible_clients],
                 'business' => $business,
             ]);
         }
     }
     
     
     
    public function create(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $employeeId = $request->input('employee_id');
        
        $all_users = User::where(function ($query) {
            $query->whereNotNull('users.border_no')
                ->orWhere('users.id_proof_name', 'eqama');

        })
        ->where('users.user_type', 'worker')
        ->select( DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"),'users.id')->get();

   

        $responsible_users = User::join('contact_locations', 'users.assigned_to', '=', 'contact_locations.id') 
        ->where('users.id', '=', $employeeId)
        ->select('contact_locations.name', 'contact_locations.id')
        ->get();
 

            
        // $responsible_client=user::join('contacts','contacts.responsible_user_id','=','users.id')
        // ->where('users.id','=', $employeeId)
        // ->select('users.id',DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as full_name")) 
        // ->get();

    
        $employees = $all_users->pluck('full_name', 'id');
        $all_responsible_users=$responsible_users->pluck('name', 'id');


       
        $employee=user::with('business') ->where('id','=', $employeeId)
        ->first();

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];
        $business=Business::pluck('name','id');
       
        return view('essentials::cards.create')
            ->with(compact(
            'employees',
            'all_responsible_users',
         //  'responsible_client',
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
           
         

          
           $lastrecord = EssentialsWorkCard::orderBy('work_card_no', 'desc')->first();

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
 
            $workcard = EssentialsWorkCard::create($data);
       




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
        
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        
    }
}