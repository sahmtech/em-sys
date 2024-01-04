<?php

namespace Modules\Essentials\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentailsReasonWish;
class EssentialsContractsFinishReasonsController extends Controller
{
    protected $moduleUtil;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }


    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
    
  
    
        $reasons = EssentailsReasonWish::where('type','reason')->select(
            'id',
            'main_reson_id',
            'employee_type as employee_type',
            'reason as reason',
            'reason_type as reason_type',
            'sub_reason as sub_reason',
          
            
        );
        if ($request->has('employee_type_filter') && $request->input('employee_type_filter') != 'all') {
            $reasons->where('employee_type', $request->input('employee_type_filter'));
        }

        if ($request->has('reason_type_filter') && $request->input('reason_type_filter') != 'all') {
            $reasons->where('reason_type', $request->input('reason_type_filter'));
        }
    
        if (request()->ajax()) {

           


            return datatables()->of($reasons)
            ->addColumn('employee_type', function ($row) {
                return trans('essentials::lang.' . $row->employee_type);
            })
            ->addColumn('reason_type', function ($row) {
                return trans('essentials::lang.' . $row->reason_type);
            })

            ->addColumn('reason', function ($row) {
                if ($row->reason_type == 'sub_main') {
                  
                    $mainReason = EssentailsReasonWish::find($row->main_reson_id);
                    return $mainReason ? $mainReason->reason : '';
                } elseif ($row->reason_type == 'main') {
               
                    return $row->reason;
                }
            
                return '';
            })
            ->addColumn('action', function ($row) {
                $html = '';
                
                $html .= '<button class="btn btn-xs btn-danger delete_country_button" data-href="' . route('finish_contract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                return $html;
            })
                ->rawColumns(['action'])
                ->removeColumn('main_reson_id')
              
                ->make(true);
        }
    
        $main_reasons = EssentailsReasonWish::forDropdown();
    
        return view('essentials::reasons_wishes.index_contract_finish_reasons')
            ->with(compact('main_reasons'));
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
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

        try {
            $input = $request->only(
                ['employee_type',
                 'reason',
                'main_reason_select',
                'sub_reason',
                'reason_type'
                 ]);

            $input['employee_type'] =$input['employee_type'];
            
            $input['type'] ='reason';
            $input['reason_type'] = $input['reason_type'];
           
            $input['sub_reason'] = $input['sub_reason'];
            
            $input['reason'] = $input['reason'];
            $input['main_reson_id'] = $input['main_reason_select'];
            
           // dd( $input);
            EssentailsReasonWish::create($input);
 
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];


        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $main_reasons= EssentailsReasonWish::forDropdown();
        return redirect()->route('contracts_finish_reasons')->with(compact('main_reasons'));
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());



        try {
            EssentailsReasonWish::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       
       return $output;

    }
}
