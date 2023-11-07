<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\BusinessLocation;
use App\Utils\Util;
use DB;

class WorkCardsController extends Controller
{
    protected $commonUtil;

    protected $contactUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $notificationUtil;
    
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
    public function index()
    {
       
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        
        $work_cards = DB::table('essentials_work_cards')
        ->leftjoin('essentials_documents', 'essentials_work_cards.document_id', '=', 'essentials_documents.id')
       ->leftjoin('users as u', 'u.id', '=', 'essentials_documents.user_id')->where('u.business_id', $business_id)
        ->select(
        DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
        'essentials_documents.id',
        'essentials_documents.document_end_date',
        'essentials_work_cards.project',
        'essentials_work_cards.workcard_duration',
        'essentials_work_cards.Payment_number',
        'essentials_work_cards.fees',
        'essentials_work_cards.fixnumber',
        'essentials_work_cards.company_name',
      );
         
      
        if (request()->ajax()) {
         
        
            return Datatables::of($work_cards)
          
        
            ->addColumn('action', function ($row) {
                $html = '';
                $html .= '<button class="btn btn-xs btn-success btn-modal" data-container=".view_modal" data-href="' . route('sale.operation.edit', ['id' => $row->id]) . '"><i class="fa fa-edit"></i> ' . __('messages.edit') . '</button>';
                

                     return $html;
           
                })
                ->rawColumns( ['action']) 
                
                ->make(true);
        }

        return view('essentials::work_cards.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $employees = User::forDropdown($business_id, false, false, false, true);
        $business_id = request()->session()->get('user.business_id');
        return view('essentials::work_cards.create')
        ->with(compact('employees'));
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
