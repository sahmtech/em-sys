<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentailsReasonWish;

class EssentialsWishesController extends Controller
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
    
      
        $reasons = EssentailsReasonWish::where('type','wish')->select(
            'id',
          
            'employee_type as employee_type',
            'reason as reason', 
        );
        if ($request->has('employee_type_filter') && $request->input('employee_type_filter') != 'all') {
            $reasons->where('employee_type', $request->input('employee_type_filter'));
        }
        if (request()->ajax()) {

           


            return datatables()->of($reasons)
            ->addColumn('employee_type', function ($row) {
                return trans('essentials::lang.' . $row->employee_type);
            })
          
         
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<button class="btn btn-xs btn-primary edit_button" 
                    data-toggle="modal" 
                    data-target="#editModal" 
                    data-id="' . $row->id . '" 
                    data-employee-type="' . $row->employee_type . '"
                    data-wish="' . $row->reason . '">
                    <i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</button>';
         
                    $html .= '&nbsp;';
    
                    $html .= '<button class="btn btn-xs btn-danger delete_country_button" data-href="' . route('wish.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
                    return $html;
                })
                ->rawColumns(['action'])
            
              
                ->make(true);
        }
    
        return view('essentials::reasons_wishes.index_wishes');
       
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

       
        try {
            $input = $request->only(
                ['employee_type',
                 'wish',
                  'file'
                 ]);

            $input['employee_type'] =$input['employee_type'];
            
            $input['type'] ='wish';
         
            
            $input['reason'] = $input['wish'];
           

            EssentailsReasonWish::create($input);
 
           
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];


        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

       // return $output;
        return redirect()->route('wishes');
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
  // In EssentialsWishesController
public function update(Request $request, $id)
{
    $business_id = $request->session()->get('user.business_id');
    $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

 

 
    $employeeType = $request->input('employee_type');
    $wish = $request->input('wish');

    try {
     
        $input2['employee_type'] = $employeeType;
       
        $input2['reason'] = $wish;
        
        
        EssentailsReasonWish::where('id', $id)
        ->where('type','wish')
        ->update($input2);

        $output = ['success' => true,
            'msg' => __('lang_v1.updated_success'),
        ];
    } catch (\Exception $e) {
        \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

        $output = ['success' => false,
            'msg' => __('messages.something_went_wrong'),
        ];
    }


    return redirect()->route('wishes');
   
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
