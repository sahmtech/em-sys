<?php

namespace Modules\Sales\Http\Controllers;

use App\Contact;
use App\Transaction;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Modules\Sales\Entities\salesContract;
use Modules\Sales\Entities\salesContractItem;
use Modules\Sales\Entities\salesContractAppendic;
use Modules\Sales\Entities\SalesProject;
use PhpOffice\PhpWord\PhpWord;

class ContractsController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {

        $business_id = request()->session()->get('user.business_id');




        $can_crud_contracts = auth()->user()->can('sales.crud_contract');
        if (!$can_crud_contracts) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $contacts = Contact::all()->pluck('supplier_business_name', 'id');
        $offer_prices = Transaction::where([['transactions.type', '=', 'sell'], ['transactions.status', '=', 'approved']])
            ->leftJoin('sales_contracts', 'transactions.id', '=', 'sales_contracts.offer_price_id')
            ->whereNull('sales_contracts.offer_price_id')->pluck('transactions.ref_no', 'transactions.id');
        $items = salesContractItem::pluck('name_of_item', 'id');
        if (request()->ajax()) {


            $contracts = salesContract::join('transactions', 'transactions.id', '=', 'sales_contracts.offer_price_id')->select([
                'sales_contracts.number_of_contract', 'sales_contracts.id', 'sales_contracts.offer_price_id', 'sales_contracts.start_date',
                'sales_contracts.end_date', 'sales_contracts.status', 'sales_contracts.file', 'sales_contracts.contract_duration',
                'sales_contracts.contract_per_period',
                'transactions.contract_form as contract_form', 'transactions.contact_id', 'transactions.id as tra'
            ]);

            if (!empty(request()->input('status')) && request()->input('status') !== 'all') {
                $contracts->where('sales_contracts.status', request()->input('status'));
            }
            if (!empty(request()->input('contract_form')) && request()->input('contract_form') !== 'all') {
                $contracts->where('transactions.contract_form', request()->input('contract_form'));
            }

            return Datatables::of($contracts)


                ->editColumn('sales_project_id', function ($row) use ($contacts) {
                    $item = $contacts[$row->contact_id] ?? '';

                    return $item;
                })


                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';
                        //  $html .=  '  <a href="#" data-href="' . action([\Modules\Sales\Http\Controllers\ContractsController::class, 'showOfferPrice'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __('sales::lang.offer_price_view') . '</a>';
                        $html .= '  <a href="#" data-href="' . route('download.contract', ['id' => $row->id]) . '" class="btn btn-xs btn-success btn-download">
                        <i class="fas fa-download" aria-hidden="true"></i>   ' . __('messages.print') . '   </a>';
                        $html .= '&nbsp;';

                        if (!empty($row->file)) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->file . '\'"><i class="fa fa-eye"></i> ' . __('sales::lang.contract_view') . '</button>';
                        } else {
                            $html .= '<span class="text-warning">' . __('sales::lang.no_file_to_show') . '</span>';
                        }
                        $html .= '&nbsp;';
                        $html .= '<button class="btn btn-xs btn-danger delete_contract_button" data-href="' . route('contract.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                        return $html;
                    }
                )



                ->filterColumn('number_of_contract', function ($query, $keyword) {
                    $query->whereRaw("number_of_contract like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $query = User::where('business_id', $business_id)->where('users.user_type', 'employee');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $contracts = DB::table('sales_contracts')

            ->select('sales_contracts.number_of_contract as contract_number', 'sales_contracts.id')
            ->get();


        return view('sales::contracts.index')->with(compact('offer_prices', 'items', 'users', 'contracts'));
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }

    public function get_projects(Request $request)
    {

        $contact = Transaction::findOrFail($request->id)->contact_id;
        $projects = SalesProject::where('contact_id', $contact)->pluck('name', 'id');


        return $projects;
    }
    public function getContractValues(Request $request)
    {

        $offerPrice = $request->input('offer_price');
        $contact = Transaction::whereId($offerPrice)->first()->contact_id;
       
        $transaction=Transaction::where('id','=',$offerPrice )->select('contract_duration')->first();
        $contract_duration= $transaction->contract_duration ;
       
        //dd( $contract_duration);


        $contract_signer = User::where([
            ['crm_contact_id', $contact],
            ['contact_user_type', 'contact_signer']
        ])->first();

        $contract_follower = User::where([
            ['crm_contact_id', $contact],
            ['contact_user_type', 'contract_follower']
        ])->first();




        return response()->json([
            'contract_follower' => $contract_follower,
            'contract_signer' => $contract_signer,
            'contract_duration' => $contract_duration
        ]);
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

            $input = $request->only([
                'offer_price', 'contract-select',
                'start_date', 'contract_duration', 'contract_duration_unit',
                'end_date', 'status',  'is_renewable', 'notes', 'file'
            ]);


            $input2['offer_price_id'] = $input['offer_price'];
            $input2['start_date'] = $input['start_date'];
            $input2['end_date'] = $input['end_date'];

            $input2['contract_duration'] = $input['contract_duration'];
            $input2['contract_duration_unit'] = $input['contract_duration_unit'];

            $input2['status'] = 'valid';
            $input2['is_renwable'] = $input['is_renewable'];
            $input2['notes'] = $input['notes'];





            //  if ($request->contract_type == 'new') {


            $latestRecord = salesContract::orderBy('number_of_contract', 'desc')->first();
            if ($latestRecord) {

                $latestRefNo = $latestRecord->number_of_contract;
                $numericPart = (int)substr($latestRefNo, 3);
                $numericPart++;
                $input2['number_of_contract'] = 'CR' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            } else {

                $input2['number_of_contract'] = 'CR0001';
            }
            // $selectedItems = $request->input('contract_items');
            // $selectedItems = array_filter($selectedItems, function ($item) {

            //     return $item !== null;
            // });
            // $input2['items_ids'] = json_encode(array_values($selectedItems));

            if ($request->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('/salesContracts');

                $input2['file'] = $filePath;
            }

            $contact_id = Transaction::whereId($input['offer_price'])->first()->contact_id;
            $sale_project['contact_id'] = $contact_id;
            $sale_project['name'] = $request->project_name;
            $assignedTo = $request->input('assigned_to');

            $assignedToJson = json_encode($assignedTo);
            $sale_project['assigned_to'] = $assignedToJson;
            $sale_project = SalesProject::create($sale_project);
            $input2['sales_project_id'] = $sale_project->id;


            salesContract::create($input2);
            //     }



            // if ($request->contract_type == 'appendix')
            //      {





            //             $input2['contract_id'] =$request->input('contract-select');
            //            // dd($input2['contract_id']);


            //             $latestRecord = salesContractAppendic::orderBy('number_of_appendix', 'desc')->first();

            //             if ($latestRecord) {
            //                 $latestRefNo = $latestRecord->number_of_appendix;
            //                 $numericPart = (int)substr($latestRefNo, 3);
            //                 $numericPart++;
            //                 $input2['number_of_appendix'] = 'CAP' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            //             } else {
            //                 $input2['number_of_appendix'] = 'CAP0001';
            //             }

            //             salesContractAppendic::create($input2);



            //     }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),

            ];
        }


        $contacts = Contact::all()->pluck('supplier_business_name', 'id');
        $offer_prices = Transaction::where([['type', '=', 'sell'], ['status', '=', 'approved']])->pluck('ref_no', 'id');
        $items = salesContractItem::pluck('name_of_item', 'id');

        return redirect()->back()->with(['output']);
        // return $output;

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    public function fetchContractDuration($offerPrice)
    {
        $contractDuration = Transaction::where('id', '=', $offerPrice)
            ->select('contract_duration', 'id')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'contract_duration' => $contractDuration,

            ],
            'msg' => __('lang_v1.fetched_success'),
        ]);
    }

    public function show($id)
    {

        if (!auth()->user()->can('user.view')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $offer = Transaction::findOrFail($id)
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->select(
                'transactions.id as id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'transactions.final_total as final_total',
                'transactions.is_direct_sale',
                'contacts.supplier_business_name as name',
                'contacts.mobile as mobile',
                'transactions.status as status',

            )
            ->get()[0];


        return view('sales::price_offer.show')->with(compact('offer'));
    }

    public function print($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $query = salesContract::where('id', $id)->With(['transaction.contact', 'transaction.sales_person', 'transaction.sell_lines', 'transaction.sell_lines.service'])->get()[0];



            $phpWord = new PhpWord();
            $type = "";
            $type_en = "";
            $contract_form = "";
            if ($query->transaction->contract_form == 'monthly_cost') {
                $contract_form = "word_templates/cost_plus_contract.docx";
                $type = __('sales::lang.monthly_cost');
                $type_en = __('sales::lang.monthly_cost', [], 'en');
            } else if ($query->transaction->contract_form == 'operating_fees') {
                $contract_form = "word_templates/fixed_contract.docx";
                $type = __('sales::lang.operating_fees');
                $type_en = __('sales::lang.operating_fees', [], 'en');
            }
            $contact_id = $query->transaction->contact->id;
            $signer = $query->transaction->contact->signer($contact_id);
            $follower = $query->transaction->contact->follower($contact_id);
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(public_path($contract_form));
            $templateProcessor->cloneRow('R', $query->transaction->sell_lines->count());
            $dateToday = Carbon::now("Asia/Riyadh");
            $templateProcessor->setValue('nationality',   'سعودي');
            $templateProcessor->setValue('nationality_en', 'Saudi');
            $templateProcessor->setValue('type', $type);
            $templateProcessor->setValue('type_en', $type_en);
            $templateProcessor->setValue('DATE', $dateToday->format('Y-m-d'));
            $templateProcessor->setValue('contract_no', $query->number_of_contract);
            $templateProcessor->setValue('contacts',    $query->contact->supplier_business_name ?? '');
            $templateProcessor->setValue('contacts_en',  $query->contact->english_name ?? '');
            $templateProcessor->setValue('contract_duration',  $query->contract_duration);
            $templateProcessor->setValue('c_dur',  $query->contract_duration);

            $templateProcessor->setValue('DATE_EN', $dateToday->format('d-m-Y'));
            $templateProcessor->setValue('d_nm_en', $dateToday->translatedFormat('l', 'en'));
            $templateProcessor->setValue('d_nm', $dateToday->translatedFormat('l', 'ar'));
            $templateProcessor->setValue('contacts',    $query->transaction->contact->supplier_business_name ?? '');
            $templateProcessor->setValue('contacts_en',  $query->transaction->contact->english_name ?? '');
            $templateProcessor->setValue('c_r_n',  $query->transaction->contact->commercial_register_no ?? '');
            $templateProcessor->setValue('c_r_n_en',  $query->transaction->contact->commercial_register_no ?? '');
            $templateProcessor->setValue('address',  $query->transaction->contact->address_line_1 ?? '');
            $templateProcessor->setValue('address_en',  $query->transaction->contact->address_line_1 ?? '');
            $templateProcessor->setValue('post_code',  $query->transaction->contact->zip_code ?? '');
            $templateProcessor->setValue('s_nm', $signer?->first_name ?? '' . ' ' . $signer?->last_name ?? '');
            $templateProcessor->setValue('s_nm_en',  $signer?->english_name ?? '');
            $templateProcessor->setValue('ID_num',  $signer?->id_proof_number ?? '');
            $templateProcessor->setValue('acting_as',  $signer?->signer_acting_as);
            $templateProcessor->setValue('acting_as_en',  $signer?->signer_acting_as_en);
            $templateProcessor->setValue('phone', $signer?->contact_number ?? '');
            $templateProcessor->setValue('email', $signer?->email ?? '');
            $templateProcessor->setValue('c_nm',  $follower?->first_name ?? '' . ' ' . $follower?->last_name ?? '');
            $templateProcessor->setValue('c_nm_en',  $follower?->english_name ?? '');
            $templateProcessor->setValue('c_phone',  $follower?->contact_number ?? '');
            $templateProcessor->setValue('c_email',  $follower?->email ?? '');
            $i = 1;
            $food = 0;
            $housing = 0;
            $transportaions = 0;
            $others = 0;
            $uniform = 0;
            $recruit = 0;

            foreach ($query->transaction->sell_lines as $sell_line) {
                $templateProcessor->setValue('R#' . $i, $i);
                $templateProcessor->setValue('A#' . $i, $sell_line['service']['profession']['name'] ?? '');
                $templateProcessor->setValue('B#' . $i,   number_format($sell_line['service']['service_price'] ?? 0, 0, '.', ''));

                foreach (json_decode($sell_line->additional_allwances) as $allwance) {
                    if (is_object($allwance) && property_exists($allwance, 'salaryType') && property_exists($allwance, 'amount')) {
                        if ($allwance->salaryType == 'food_allowance') {
                            $food = $allwance->amount;
                        }
                        if ($allwance->salaryType == 'housing_allowance') {
                            $housing = $allwance->amount;
                        }
                        if ($allwance->salaryType == 'transportation_allowance') {
                            $transportaions = $allwance->amount;
                        }
                        if ($allwance->salaryType == 'other_allowances') {
                            $others = $allwance->amount;
                        }
                        if ($allwance->salaryType == 'uniform_allowance') {
                            $uniform = $allwance->amount;
                        }
                        if ($allwance->salaryType == 'recruit_allowance') {
                            $recruit = $allwance->amount;
                        }
                    }
                }
                $templateProcessor->setValue('C#' . $i, $food);
                $templateProcessor->setValue('D#' . $i,  $transportaions);
                $templateProcessor->setValue('E#' . $i, $housing);
                $templateProcessor->setValue('F#' . $i, $others);
                $templateProcessor->setValue('G#' . $i, __('sales::lang.' . $sell_line['service']['gender']) ?? '');
                $templateProcessor->setValue('H#' . $i, $sell_line->quantity ?? 0);
                $templateProcessor->setValue('I#' . $i, number_format($sell_line['service']['monthly_cost_for_one'] ?? 0, 0, '.', ''));
                $templateProcessor->setValue('J#' . $i, $sell_line['service']['nationality']['nationality'] ?? '');
                $templateProcessor->setValue('K#' . $i,  $query->transaction->contract_duration ?? 0);
                $templateProcessor->setValue('L#' . $i, $sell_line['service']['monthly_cost_for_one'] * $sell_line->quantity);
                $templateProcessor->setValue('M#' . $i, ($sell_line['service']['monthly_cost_for_one'] * $sell_line->quantity ?? 0) * 15 / 100 ?? '');
                $templateProcessor->setValue('N#' . $i, $sell_line['service']['monthly_cost_for_one'] * $sell_line->quantity ?? 0 +  ($sell_line['service']['monthly_cost_for_one'] * $sell_line->quantity ?? 0) * 15 / 100 ?? 0);

                $i++;
            }


            $templateProcessor->setValue('down_payment', $query->down_payment ?? '');
            $templateProcessor->setValue('down_payment', $query->down_payment ?? '');
            $templateProcessor->setValue('value', '' ?? '');
            $templateProcessor->setValue('bank_gurantee', '' ?? '');
            $templateProcessor->setValue('bank_gurantee_en', '' ?? '');


            $outputPath = public_path('uploads/contracts/' . $query->number_of_contract . '.docx');
            $templateProcessor->saveAs($outputPath);

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $query->number_of_contract . '.docx"',
            ];

            // Return the response with the file content
            return response()->download($outputPath, $query->number_of_contract . '.docx', $headers);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function showOfferPrice($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $offer_price = salesContract::where('id', $id)->first()->offer_price_id;
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $offer_price)->with(['contact:id,supplier_business_name,mobile', 'sell_lines', 'sell_lines.service'])

            ->select(
                'id',
                'business_id',
                'location_id',
                'status',
                'contact_id',
                'ref_no',
                'final_total',
                'down_payment',
                'contract_form',
                'transaction_date'

            )->get()[0];


        return view('sales::price_offer.show')
            ->with(compact('query'));
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
            salesContract::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
