<?php

namespace Modules\FollowUp\Http\Controllers;

use App\Contact;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Spatie\Activitylog\Models\Activity;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;


class FollowUpWorkerController extends Controller
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
     
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_workers= auth()->user()->can('followup.crud_workers');
        if (! $can_crud_workers) {
            abort(403, 'Unauthorized action.');
        }
        
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id); 
        $contacts=Contact::where('type','customer')->pluck('name','id');
        $nationalities=EssentialsCountry::nationalityForDropdown();
        if (request()->ajax()) {
           $users = User::where('user_type', 'worker')
            ->join('contacts', 'contacts.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument'])
            ;
        
            if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
                $users->where('contacts.id', request()->input('project_name'));
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
            
                $users->whereHas('contract', function ($query) use ($start, $end) {
                    $query->whereDate('contract_end_date', '>=', $start)
                        ->whereDate('contract_end_date', '<=', $end);
                });
            }
            if (!empty(request()->nationality) && request()->nationality !== 'all') {
               
               $users=$users->where('nationality_id', request()->nationality);
                error_log(request()->nationality);
            }
           $users->select('users.*','users.nationality_id',
           DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
           'contacts.name as contact_name');
            return Datatables::of($users)
               
                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                  
                })
                ->addColumn('residence_permit_expiration', function ($user) {
                    return $this->getDocumentExpirationDate($user, 'residence_permit');
                })
                ->addColumn('residence_permit', function ($user) {
                    return $this->getDocumentnumber($user, 'residence_permit');
                })
                ->addColumn('contract_end_date', function ($user) {
                    return optional($user->contract)->contract_end_date ?? ' ';
                })
           
                ->rawColumns(['nationality','residence_permit_expiration','residence_permit','contract_end_date']) 
                ->make(true);
        }
        return view('followup::workers.index')->with(compact('contacts','nationalities'));
    }




    private function getDocumentExpirationDate($user, $documentType)
    {
            foreach ($user->OfficialDocument as $off) {
                if ($off->type == $documentType) {
                    return $off->expiration_date;
                }
            }
        
            return ' ';
    }
    
    private function getDocumentnumber($user, $documentType)
    {
            foreach ($user->OfficialDocument as $off) {
                if ($off->type == $documentType) {
                    return $off->number;
                }
            }
        
            return ' ';
    }
    

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('followup::create');
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
        if (! auth()->user()->can('user.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $user = User::where('business_id', $business_id)
                    ->with(['contactAccess'])
                    ->find($id);
        $dataArray=[];
        if(!empty($user->bank_details))
         {$dataArray = json_decode($user->bank_details, true)['bank_name'];} 
     
        
        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();          
        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();  
       // dd( $Qualification);
        
        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('profession_id');
      
        if($professionId !== null)   
       { $profession = EssentialsProfession::find($professionId)->name;}
       else{$profession ="";}
        
        $specializationId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->value('specialization_id');
        if ( $specializationId !== null)
        {$specialization = EssentialsSpecialization::find($specializationId)->name;}
        else{$specialization="";}
   
      
        $user->profession = $profession;
        $user->specialization = $specialization;
 
        
        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);
       
        $users = User::forDropdown($business_id, false);

        $activities = Activity::forSubject($user)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $nationality_id=$user->nationality_id;
        $nationality="";
        if(!empty($nationality_id))
        {
            $nationality = EssentialsCountry::select('nationality')->where('id','=',$nationality_id)->first() ;
        }
        
     
      
        return view('followup::workers.show')->with(compact('user',

         'view_partials', 'users', 'activities','bank_name',
        'admissions_to_work','Qualification','Contract','nationalities','nationality'));

    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('followup::edit');
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
