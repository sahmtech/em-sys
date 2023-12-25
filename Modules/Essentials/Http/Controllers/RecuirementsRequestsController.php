<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\FollowUp\Entities\followupRecruitmentRequest;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;

use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
class RecuirementsRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $contactUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $productUtil;
    protected $moduleUtil;
    protected $util;

    protected $statuses;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;

        $this->statuses = [
            'approved' => [
                'name' => trans('sales::lang.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name' => trans('sales::lang.cancelled'),
                'class' => 'bg-red',
            ],
            
            'pending' => [
                'name' => trans('sales::lang.under_study'),
                'class' => 'bg-yellow',
            ],
        ];

 
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $specializations=EssentialsSpecialization::all()->pluck('name','id');
        $professions=EssentialsProfession::all()->pluck('name','id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $recruitmentRequests = followupRecruitmentRequest::
        select([
            'id',
            'quantity',
            'nationality_id',
            'profession_id',
            'specialization_id',
            'date',
            'note',
            'status',
            'attachment',

        ])->where('status' ,'pending');
        if (request()->ajax())
         {

            return Datatables::of($recruitmentRequests)

            ->editColumn('status', function ($row) {
                  
                $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                    . $this->statuses[$row->status]['name'] . '</span>';
                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
          
            return $status;
        })


            ->editColumn('nationality_id',function($row)use($nationalities){
                $item = $nationalities[$row->nationality_id]??'';

                return $item;
            })
            ->editColumn('profession_id',function($row)use($professions){
                $item = $professions[$row->profession_id]??'';

                return $item;
            })
            ->editColumn('specialization_id',function($row)use($specializations){
                $item = $specializations[$row->specialization_id]??'';

                return $item;
            })
         
     
            ->addColumn(
                'attachments',
                 function ($row) {
                    $html = ''; 
                if (!empty($row->attachment)) {   
                $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/'.$row->attachment.'\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                    '&nbsp;';
                } else {
                    $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                }

              
                    return $html;
                 }
                )
          
           
            ->rawColumns(['attachments','status'])
            ->make(true);
         }
         $statuses = $this->statuses;
        return view('essentials::requirements_requests.index')
        ->with(compact('specializations','professions','nationalities','statuses'));
    }

    public function changeStatus(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['status', 'request_id','quantity']);

            $reqRequest = followupRecruitmentRequest::find($input['request_id']);
           

            $reqRequest->status = $input['status'];
            $reqRequest->quantity =  $reqRequest->quantity  - $input['quantity'];

            $reqRequest->save();

            $reqRequest->status = $this->statuses[$reqRequest->status]['name'];

           
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return $output;
    }

    public function acceptedRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');
        $specializations=EssentialsSpecialization::all()->pluck('name','id');
        $professions=EssentialsProfession::all()->pluck('name','id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $recruitmentRequests = followupRecruitmentRequest::
        select([
            'id',
            'quantity',
            'nationality_id',
            'profession_id',
            'specialization_id',
            'date',
            'note',
            'status',
            'attachment',
            'quantity',

        ])->where('status' ,'approved');
        if (request()->ajax())
         {

            return Datatables::of($recruitmentRequests)

            ->editColumn('status', function ($row) {
                  
                $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                    . $this->statuses[$row->status]['name'] . '</span>';
                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '"    data-quantity="' . $row->quantity . '"    data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
          
            return $status;
        })


            ->editColumn('nationality_id',function($row)use($nationalities){
                $item = $nationalities[$row->nationality_id]??'';

                return $item;
            })
            ->editColumn('profession_id',function($row)use($professions){
                $item = $professions[$row->profession_id]??'';

                return $item;
            })
            ->editColumn('specialization_id',function($row)use($specializations){
                $item = $specializations[$row->specialization_id]??'';

                return $item;
            })
         
     
            ->addColumn(
                'attachments',
                 function ($row) {
                    $html = ''; 
                if (!empty($row->attachment)) {   
                $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/'.$row->attachment.'\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                    '&nbsp;';
                } else {
                    $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                }

              
                    return $html;
                 }
                )
          
           
            ->rawColumns(['attachments','status'])
            ->make(true);
         }

        return view('essentials::requirements_requests.aproved_requests_index')
        ->with(compact('specializations','professions','nationalities'));
    }

   
   
    public function unacceptedRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');
        $specializations=EssentialsSpecialization::all()->pluck('name','id');
        $professions=EssentialsProfession::all()->pluck('name','id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $recruitmentRequests = followupRecruitmentRequest::
        select([
            'id',
            'quantity',
            'nationality_id',
            'profession_id',
            'specialization_id',
            'date',
            'note',
            'status',
            'attachment',

        ])->where('status' ,'rejected');
        if (request()->ajax())
         {

            return Datatables::of($recruitmentRequests)

            
            ->editColumn('status', function ($row) {
                  
                $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                    . $this->statuses[$row->status]['name'] . '</span>';
                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
          
            return $status;
        })

            ->editColumn('nationality_id',function($row)use($nationalities){
                $item = $nationalities[$row->nationality_id]??'';

                return $item;
            })
            ->editColumn('profession_id',function($row)use($professions){
                $item = $professions[$row->profession_id]??'';

                return $item;
            })
            ->editColumn('specialization_id',function($row)use($specializations){
                $item = $specializations[$row->specialization_id]??'';

                return $item;
            })
         
     
            ->addColumn(
                'attachments',
                 function ($row) {
                    $html = ''; 
                if (!empty($row->attachment)) {   
                $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/'.$row->attachment.'\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                    '&nbsp;';
                } else {
                    $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                }

              
                    return $html;
                 }
                )
          
           
            ->rawColumns(['attachments','status'])
            ->make(true);
         }

        return view('essentials::requirements_requests.rejected_requests_index')
        ->with(compact('specializations','professions','nationalities'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::create');
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
