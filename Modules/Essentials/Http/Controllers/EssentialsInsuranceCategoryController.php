<?php

namespace Modules\Essentials\Http\Controllers;

use App\Contact;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsInsuranceCompany;

class EssentialsInsuranceCategoryController extends Controller
{


    protected $moduleUtil;
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
    

        $can_crud_insurance_companies = auth()->user()->can('essentials.crud_insurance_companies');
        if (! $can_crud_insurance_companies) {
           //temp  abort(403, 'Unauthorized action.');
        }
        $insurance_companies= Contact::where('type','insurance')->pluck('supplier_business_name','id');
        if (request()->ajax()) {
            $insuranceCategories = DB::table('essentials_insurance_classes')->select([
                'id',
                'name',
                'insurance_company_id'
            ]);

    
            
            return Datatables::of($insuranceCategories)
            ->editColumn('insurance_company_id',function($row)use($insurance_companies){
                $item = $insurance_companies[$row->insurance_company_id]??'';

                return $item;
            })
            ->addColumn(
                'action',
                function ($row) {
                    $html = '';
                    //$html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href=""><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>&nbsp;';
                    //$html .= '<a href="'. route('country.edit', ['id' => $row->id]) .  '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>&nbsp;';
                    $html .= '<button class="btn btn-xs btn-danger delete_insurance_category_button" data-href="' . route('insurance_categories.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';

            
                    return $html;
                }
            )
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name',"LIKE", "%{$keyword}%");
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('essentials::settings.partials.insurance_categories.index')->with(compact('insurance_companies'));
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
   

        try {
            $input = $request->only(['insurance_category_name','insurance_company']);
            

            $insurance_category_data['name'] = $input['insurance_category_name'];
            $insurance_category_data['insurance_company_id'] = $input['insurance_company'];


            EssentialsInsuranceClass::create($insurance_category_data);
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('insurance_categories')->with('status', $output);
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

     

        try {
            EssentialsInsuranceClass::where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
       
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
       
        return $output;
    }
}
