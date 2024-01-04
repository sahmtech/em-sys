<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Utils\EssentialsUtil;


class EssentialsAllowanceController extends Controller
{
    protected $moduleUtil;

    protected $essentialsUtil;

   
    public function __construct(ModuleUtil $moduleUtil, EssentialsUtil $essentialsUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');



        if (! auth()->user()->can('essentials.add_allowance_and_deduction') && ! auth()->user()->can('essentials.view_allowance_and_deduction')) {
           //temp  abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $allowances = EssentialsAllowanceAndDeduction::where('business_id', $business_id);
   
            return Datatables::of($allowances)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        if (auth()->user()->can('essentials.add_allowance_and_deduction')) {
                            $html .= '<a href="'.action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'edit'], [$row->id]).  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                            $html .= '&nbsp; <button class="btn btn-xs btn-danger delete_allowances_and_deductions_button" data-href="' .action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'destroy'], [$row->id]). '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';

                        }

                        return $html;
                    }
                )
                ->editColumn('applicable_date', function ($row) {
                    return $this->essentialsUtil->format_date($row->applicable_date);
                })
                ->editColumn('type', '{{__("essentials::lang." . $type)}}')
                ->editColumn('amount', '<span class="display_currency" data-currency_symbol="false">{{$amount}}</span> @if($amount_type =="percent") % @endif')
                ->rawColumns(['action', 'amount'])
                ->make(true);
        }
       return view('essentials::settings.partials.allowances_and_deductions.index');
    }
    // public function index()
    // {
    //    $business_id = request()->session()->get('user.business_id');
 

 
    //     $can_crud_allowances = auth()->user()->can('essentials.crud_allowances');
    //     if (! $can_crud_allowances) {
    //        //temp  abort(403, 'Unauthorized action.');
    //     }
    //     $is_admin = $this->moduleUtil->is_admin(auth()->user());
 
    //     if (request()->ajax()) {
    //         $types= DB::table('essentials_allowance_types')->select(['id','type', 'name','allowance_value','number_of_months','added_to_salary','details', 'is_active']);
                       
 
    //         return Datatables::of($types)
    //         ->addColumn(
    //             'action',
    //             function ($row) use ($is_admin) {
    //                 $html = '';
    //                 if ($is_admin) {
    //                     $html .= '<a href="'. route('Allowance.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>
    //                     &nbsp;';
    //                     $html .= '<button class="btn btn-xs btn-danger delete_allowance_type_button" data-href="' . route('Allowance.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';
    //                 }
        
    //                 return $html;
    //             }
    //         )
    //         ->filterColumn('type', function ($query, $keyword) {
    //             $query->where('type', 'like', "%{$keyword}%");
    //         })
    //         ->removeColumn('id')
    //         ->rawColumns(['action'])
    //         ->make(true);
        
        
    //         }
    //   return view('essentials::settings.partials.allowances.index');
    // }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {   
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user());

    
       
        return view('essentials::settings.partials.allowances.create');
     
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    // public function store(Request $request)
    // {
    //   return $request;
    //     $business_id = $request->session()->get('user.business_id');
    //     $is_admin = $this->moduleUtil->is_admin(auth()->user());

    //     if (! (auth()->user()->can('superadmin') || ($business_id, 'essentials_module')) && ! $is_admin) {
    //        //temp  abort(403, 'Unauthorized action.');
    //     }
 
    //     try {
    //         $input = $request->only(['type', 'name','allowance_value','number_of_months','added_to_salary','details', 'is_active']);
            
    //         $input2['type'] = $input['type'];

    //         $input2['name'] = $input['name'];

    //         $input2['allowance_value'] = $input['allowance_value'];

    //         $input2['number_of_months'] = $input['number_of_months'];

    //         $input2['added_to_salary'] = $input['added_to_salary'];
           
    //         $input2['details'] = $input['details'];
            
    //         $input2['is_active'] = $input['is_active'];
            
    //         essentialsAllowanceType::create($input);
 
    //         $output = ['success' => true,
    //             'msg' => __('lang_v1.added_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

    //         $output = ['success' => false,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }
    //     return redirect()->route('allowances');
    
    // }
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        try {
            $input = $request->only(['description', 'type', 'amount', 'amount_type', 'applicable_date']);
           $input2['description'] = $input['description'];

           $input2['type'] = $input['type'];

            $input2['amount_type'] = $input['amount_type'];

            $input2['business_id'] = $business_id;

            $input2['amount'] = $this->moduleUtil->num_uf($input['amount']);

            $input2['applicable_date'] =  $input['applicable_date'];
          
            $allowance = EssentialsAllowanceAndDeduction::create($input2);
            

            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => 'File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage(),
            ];
        }

        return redirect()->action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'index']);

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
  
     public function edit($id)
     {
         $business_id = request()->session()->get('user.business_id');
         $is_admin = $this->moduleUtil->is_admin(auth()->user());

 
         $allowance = EssentialsAllowanceAndDeduction::findOrFail($id);
 
 
         return view('essentials::settings.partials.allowances_and_deductions.edit')->with(compact('allowance'));
     }
 
  
     public function update(Request $request, $id)
     {
       
         $business_id = $request->session()->get('user.business_id');
         $is_admin = $this->moduleUtil->is_admin(auth()->user());
 

 
         try {
            $input = $request->only(['description', 'type', 'amount', 'amount_type', 'applicable_date']);
            $input2['description'] = $input['description'];
 
            $input2['type'] = $input['type'];
 
             $input2['amount_type'] = $input['amount_type'];
 
             $input2['business_id'] = $business_id;
 
             $input2['amount'] = $this->moduleUtil->num_uf($input['amount']);
 
             $input2['applicable_date'] =  $input['applicable_date'];
           
              EssentialsAllowanceAndDeduction::where('id', $id)->update($input2);
             $output = ['success' => true,
                 'msg' => __('lang_v1.updated_success'),
             ];
         } catch (\Exception $e) {
             \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
 
             $output = ['success' => false,
                 'msg' => __('messages.something_went_wrong'),
             ];
         }
 
         return redirect()->action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'index']);


     }
 
     public function destroy($id)
     {
         $business_id = request()->session()->get('user.business_id');
         $is_admin = $this->moduleUtil->is_admin(auth()->user());
 
     
 
         try {
            EssentialsAllowanceAndDeduction::where('id', $id)
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
