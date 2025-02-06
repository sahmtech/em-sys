<?php
namespace Modules\Sales\Http\Controllers;

use App\Contact;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\salesService;
use Yajra\DataTables\Facades\DataTables;

class ContractItemController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $contacts        = Contact::whereNotNull('supplier_business_name')
            ->pluck('supplier_business_name', 'id');

        // dd($contacts);

        $professions   = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $business_id = request()->session()->get('user.business_id');

        $can_crud_contract_items = auth()->user()->can('sales.crud_contract_items');
        if (! $can_crud_contract_items) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $is_admin                  = auth()->user()->hasRole('Admin#1') ? true : false;
        $crud_edit_contract_item   = auth()->user()->can('sales.edit_contract_item');
        $crud_delete_contract_item = auth()->user()->can('sales.delete_contract_item');

        if (request()->ajax()) {
            $salesService = SalesService::with('profession', 'nationality', 'specialization')
                ->latest('created_at')
                ->get();

            return Datatables::of($salesService)

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $crud_edit_contract_item, $crud_delete_contract_item) {
                        $html = '';
                        if ($is_admin || $crud_edit_contract_item) {
                            $html .= '<a href="' . route('item.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>
                         &nbsp;';
                        }
                        if ($is_admin || $crud_delete_contract_item) {
                            $html .= '<button class="btn btn-xs btn-danger delete_item_button" data-href="' . route('item.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )
            // ->filterColumn('name_of_item', function ($query, $keyword) {
            //     $query->where('name_of_item', 'like', "%{$keyword}%");
            // })
                ->addColumn('id', function ($row) use ($salesService) {

                    return $row->id;
                })
                ->addColumn('contact_name', function ($row) use ($salesService) {

                    return $row->contact->supplier_business_name ?? '';
                })
                ->addColumn('project_name', function ($row) use ($salesService) {

                    return $row->salesProject->name ?? '';
                })
                ->addColumn('profession', function ($row) use ($salesService) {

                    return $row->profession->name ?? '';
                })

                ->addColumn('nationality', function ($row) use ($salesService) {

                    return $row->nationality->nationality ?? '';
                })

                ->addColumn('gender', function ($row) use ($salesService) {
                    return $row->gender === 'male' ? 'ذكر' : ($row->gender === 'female' ? 'أنثى' : '');
                })

                ->addColumn('monthly_cost_for_one', function ($row) use ($salesService) {

                    return $row->monthly_cost_for_one ?? '';
                })

                ->addColumn('details', function ($row) use ($salesService) {

                    return $row->details ?? '';
                })

                ->rawColumns(['action'])
                ->make(true);

        }
        return view('sales::contract_items.index')->with(compact('specializations', 'professions', 'nationalities', 'contacts'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function storeAppindexItem(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin    = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $input = $request->only(['contacts', 'project_name', 'profession', 'nationality', 'gender', 'essentials_salary', 'details']);

            $input['contacts'] = $input['contacts'];

            $input['project_name'] = $input['project_name'];

            $input['profession']        = $input['profession'];
            $input['nationality']       = $input['nationality'];
            $input['gender']            = $input['gender'];
            $input['essentials_salary'] = $input['essentials_salary'];
            $input['details']           = $input['details'];

            $appendixItem = salesService::create($input);

            $output = ['success' => true,
                'msg'                => __('lang_v1.added_success'),
                'appendixItem'       => $appendixItem->id,
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => false,
                'msg'                => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $business_id = $request->session()->get('user.business_id');
        $is_admin    = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $input = $request->only(['contacts', 'project_name', 'profession', 'nationality', 'gender', 'essentials_salary', 'details']);

            $input['contact_id'] = $input['contacts'];

            $input['sales_project_id'] = $input['project_name'];

            $input['profession_id']        = $input['profession'];
            $input['specialization_id']    = $input['nationality'];
            $input['nationality_id']       = $input['nationality'];
            $input['gender']               = $input['gender'];
            $input['monthly_cost_for_one'] = $input['essentials_salary'];
            $input['details']              = $input['details'];

            $appendixItem = salesService::create($input);

            $output = ['success' => true,
                'msg'                => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => false,
                'msg'                => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('contract_itmes')->with($output);

    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin    = auth()->user()->hasRole('Admin#1') ? true : false;

        $item = salesService::findOrFail($id);

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $contacts        = Contact::whereNotNull('supplier_business_name')
            ->pluck('supplier_business_name', 'id');

        // dd($contacts);

        $professions   = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        return view('sales::contract_items.edit')->with(compact('item', 'specializations', 'contacts', 'professions', 'nationalities'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        // dd($id);

        $business_id = $request->session()->get('user.business_id');
        $is_admin    = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $input = $request->only(['contacts', 'project_name', 'profession', 'nationality', 'gender', 'essentials_salary', 'details']);

            $input['contact_id'] = $input['contacts'];

            $input['sales_project_id'] = $input['project_name'];

            $input['profession_id']        = $input['profession'];
            $input['specialization_id']    = $input['nationality'];
            $input['nationality_id']       = $input['nationality'];
            $input['gender']               = $input['gender'];
            $input['monthly_cost_for_one'] = $input['essentials_salary'];
            $input['details']              = $input['details'];

            $salesService = salesService::where('id', $id)->first();

            $salesService->update($input);

            $output = ['success' => true,
                'msg'                => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => false,
                'msg'                => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('contract_itmes')->with($output);
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin    = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            salesService::where('id', $id)
                ->delete();

            $output = ['success' => true,
                'msg'                => __('lang_v1.deleted_success'),
            ];

        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => false,
                'msg'                => __('messages.something_went_wrong'),
            ];
        }

        return $output;

    }
}
