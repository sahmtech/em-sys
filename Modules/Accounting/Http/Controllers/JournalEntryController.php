<?php

namespace Modules\Accounting\Http\Controllers;

use App\Contact;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\Entities\AccountingAccountsTransactionHistory;
use Modules\Accounting\Entities\AccountingAccTransMappingHistory;
use Modules\Accounting\Utils\AccountingUtil;
use Yajra\DataTables\Facades\DataTables;

class JournalEntryController extends Controller
{
    protected $util;
    protected $moduleUtil;
    protected $accountingUtil;

    public function __construct(Util $util, ModuleUtil $moduleUtil, AccountingUtil $accountingUtil)
    {
        $this->util = $util;
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_journal_entry = auth()->user()->can('accounting.journal_entry');
        if (!($is_admin || $can_journal_entry)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $can_view_journal = auth()->user()->can('accounting.view_journal');
        $can_edit_journal = auth()->user()->can('accounting.edit_journal');
        $can_delete_journal = auth()->user()->can('accounting.delete_journal');
        $can_history_edit = auth()->user()->can('accounting.history_edit');
        

        if (request()->ajax()) {
            $journal = AccountingAccTransMapping::where('accounting_acc_trans_mappings.business_id', $business_id)
                ->where('accounting_acc_trans_mappings.company_id', $company_id)
                ->join('users as u', 'accounting_acc_trans_mappings.created_by', 'u.id')
                ->where('type', 'journal_entry')
                ->select([
                    'accounting_acc_trans_mappings.id', 'ref_no', 'operation_date', 'note',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                ]);

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $journal->whereDate('accounting_acc_trans_mappings.operation_date', '>=', $start)
                    ->whereDate('accounting_acc_trans_mappings.operation_date', '<=', $end);
            }
            return Datatables::of($journal)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_view_journal, $can_edit_journal, $can_delete_journal) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                        // if ($is_admin || $can_view_journal) {
                        //     $html .= '<li>
                        //         <a href="#" data-href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@show', [$row->id]) . '">
                        //             <i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '
                        //         </a>
                        //         </li>';
                        // }

                        if ($is_admin || $can_edit_journal) {
                            $html .= '<li>
                                    <a href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@edit', [$row->id]) . '">
                                        <i class="fas fa-edit"></i>' . __("messages.edit") . '
                                    </a>
                                </li>';
                        }

                        if ($is_admin || $can_history_edit) {
                            $html .= '<li>
                                    <a href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@history_index', [$row->id]) . '">
                                        <i class="fas fa-history" aria-hidden="true"></i>' . __("messages.history_edit") . '
                                    </a>
                                </li>';
                        }

                        if ($is_admin || $can_delete_journal) {
                            $html .= '<li>
                                    <a href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@destroy', [$row->id]) . '" class="delete_journal_button">
                                        <i class="fas fa-trash" aria-hidden="true"></i>' . __("messages.delete") . '
                                    </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::journal_entry.index');
    }
    public function history_index($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_journal_entry = auth()->user()->can('accounting.journal_entry');
        if (!($is_admin || $can_journal_entry)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $can_view_journal = auth()->user()->can('accounting.view_journal');
        $can_edit_journal = auth()->user()->can('accounting.edit_journal');
        $can_delete_journal = auth()->user()->can('accounting.delete_journal');


        if (request()->ajax()) {

            $journal = AccountingAccTransMappingHistory::where('accounting_accounts_transactions_history_id', $id)
                ->where('accounting_acc_trans_mapping_histories.business_id', $business_id)
                ->where('accounting_acc_trans_mapping_histories.company_id', $company_id)
                ->join('users as u', 'accounting_acc_trans_mapping_histories.created_by', 'u.id')
                ->where('type', 'journal_entry')
                ->select([
                    'accounting_acc_trans_mapping_histories.id', 'ref_no', 'operation_date', 'note', 'accounting_acc_trans_mapping_histories.created_at',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                ]);

            return Datatables::of($journal)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_view_journal, $can_edit_journal, $can_delete_journal) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                        if ($is_admin || $can_view_journal) {
                            $html .= '<li>
                                <a href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@history_view', [$row->id]) . '"
                                 data-href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@history_view', [$row->id]) . '">
                                        <i class="fas fa-history" aria-hidden="true"></i>' . __("messages.history_edit") . '
                                   </a>
                                </li>';
                        }



                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::journal_entry.history_index');
    }


    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        // 
        $contacts = Contact::whereNot('id', 1)->whereIn('type', ['converted', 'supplier'])->get();

        $query = User::where('business_id', $business_id)->where('company_id', $company_id)->whereIn('users.user_type', ['employee','department_head','manager'])->where('users.status', '!=', 'inactive');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $employees = $all_users->pluck('full_name', 'id');


        return view('accounting::journal_entry.create', compact(['contacts', 'employees']));
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');





        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $account_ids = $request->get('account_id');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $additional_notes = $request->get('additional_notes');
            $selected_partner_ids = $request->get('selected_partner_id');
            $selected_partner_types = $request->get('selected_partner_type_');
            $journal_date = $request->get('journal_date');

            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id, $company_id);


            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
            
            if (empty($ref_no)) {
                $prefix = !empty($accounting_settings['journal_entry_prefix']) ?
                    $accounting_settings['journal_entry_prefix'] : '';

                //Generate reference number
                $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $company_id, $prefix);
            }

            $acc_trans_mapping = new AccountingAccTransMapping();
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->company_id = $company_id;

            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = 'journal_entry';
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $acc_trans_mapping->save();

            //save details in account trnsactions table
            foreach ($account_ids as $index => $account_id) {
                if (!empty($account_id)) {

                    $transaction_row = [];
                    $transaction_row['accounting_account_id'] = $account_id;

                    if (!empty($credits[$index])) {
                        $transaction_row['amount'] = $credits[$index];
                        $transaction_row['type'] = 'credit';
                    }

                    if (!empty($debits[$index])) {
                        $transaction_row['amount'] = $debits[$index];
                        $transaction_row['type'] = 'debit';
                    }



                    $transaction_row['additional_notes'] = $additional_notes[$index];
                    $transaction_row['partner_id'] = $selected_partner_ids[$index];
                    $transaction_row['partner_type'] = $selected_partner_types[$index];
                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                    $transaction_row['sub_type'] = 'journal_entry';
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;

                    $accounts_transactions = new AccountingAccountsTransaction();
                    $accounts_transactions->fill($transaction_row);
                    $accounts_transactions->save();
                }
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');





        return view('accounting::journal_entry.show');
    }

    public function history_view($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');




        $journal = AccountingAccTransMappingHistory::where('business_id', $business_id)
            ->where('company_id', $company_id)
            ->where('type', 'journal_entry')
            ->where('id', $id)
            ->firstOrFail();
        $accounts_transactions = AccountingAccountsTransactionHistory::with('account')
            ->where('acc_trans_mapping_history_id', $id)
            ->get()->toArray();

        $contacts = Contact::whereNot('id', 1)->whereIn('type', ['converted', 'supplier'])->get();

        $query = User::where('business_id', $business_id)->where('company_id', $company_id)->whereIn('users.user_type', ['employee','department_head','manager'])->where('users.status', '!=', 'inactive');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $employees = $all_users->pluck('full_name', 'id');

        return view('accounting::journal_entry.history_view')
            ->with(compact('journal', 'accounts_transactions', 'contacts', 'employees'));
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');




        $journal = AccountingAccTransMapping::where('business_id', $business_id)
            ->where('company_id', $company_id)
            ->where('type', 'journal_entry')
            ->where('id', $id)
            ->firstOrFail();
        $accounts_transactions = AccountingAccountsTransaction::with('account')
            ->where('acc_trans_mapping_id', $id)
            ->get()->toArray();

        $contacts = Contact::whereNot('id', 1)->whereIn('type', ['converted', 'supplier'])->get();

        $query = User::where('business_id', $business_id)->where('company_id', $company_id)->whereIn('users.user_type', ['employee','department_head','manager'])->where('users.status', '!=', 'inactive');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $employees = $all_users->pluck('full_name', 'id');

        return view('accounting::journal_entry.edit')
            ->with(compact('journal', 'accounts_transactions', 'contacts', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');




        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $account_ids = $request->get('account_id');
            $accounts_transactions_id = $request->get('accounts_transactions_id');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $selected_partner_ids = $request->get('selected_partner_id');
            $selected_partner_types = $request->get('selected_partner_type_');

            $journal_date = $request->get('journal_date');

            $acc_trans_mapping = AccountingAccTransMapping::where('business_id', $business_id)
                ->where('company_id', $company_id)
                ->where('type', 'journal_entry')
                ->where('id', $id)
                ->firstOrFail();
            $accountingAccTransMappingHistory = AccountingAccTransMappingHistory::create([
                'accounting_accounts_transactions_history_id' => $id,
                "business_id" => $acc_trans_mapping->business_id,
                "company_id" => $acc_trans_mapping->company_id,
                "ref_no" => $acc_trans_mapping->ref_no,
                "type" => $acc_trans_mapping->type,
                "created_by" => Auth::user()->id,
                "operation_date" => $acc_trans_mapping->operation_date,
                "note" => $acc_trans_mapping->note,
            ]);
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $acc_trans_mapping->update();

            //save details in account trnsactions table
            foreach ($account_ids as $index => $account_id) {
                if (!empty($account_id)) {

                    $transaction_row = [];
                    $transaction_row['accounting_account_id'] = $account_id;

                    if (!empty($credits[$index])) {
                        $transaction_row['amount'] = $credits[$index];
                        $transaction_row['type'] = 'credit';
                    }

                    if (!empty($debits[$index])) {
                        $transaction_row['amount'] = $debits[$index];
                        $transaction_row['type'] = 'debit';
                    }
                    $transaction_row['additional_notes'] = $additional_notes[$index] ?? '';
                    $transaction_row['partner_id'] = $selected_partner_ids[$index];
                    $transaction_row['partner_type'] = $selected_partner_types[$index];

                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                    $transaction_row['sub_type'] = 'journal_entry';
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;

                    if (!empty($accounts_transactions_id[$index])) {
                        $accounts_transactions = AccountingAccountsTransaction::find($accounts_transactions_id[$index]);
                        AccountingAccountsTransactionHistory::create([
                            'acc_trans_mapping_history_id' => $accountingAccTransMappingHistory->id,
                            "accounting_account_id" => $accounts_transactions->accounting_account_id,
                            "acc_trans_mapping_id" => $accounts_transactions->acc_trans_mapping_id,
                            "transaction_id" => $accounts_transactions->transaction_id,
                            "transaction_payment_id" => $accounts_transactions->transaction_payment_id,
                            "amount" => $accounts_transactions->amount,
                            "type" => $accounts_transactions->type,
                            "sub_type" => $accounts_transactions->sub_type,
                            "map_type" => $accounts_transactions->map_type,
                            "created_by" => Auth::user()->id,
                            "operation_date" => $accounts_transactions->operation_date,
                            "note" => $accounts_transactions->note,
                            'additional_notes' => $accounts_transactions->additional_notes,
                            'partner_id' => $accounts_transactions->partner_id,
                            'partner_type' => $accounts_transactions->partner_type,


                        ]);
                        $accounts_transactions->fill($transaction_row);
                        $accounts_transactions->update();
                    } else {
                        $accounts_transactions = new AccountingAccountsTransaction();
                        $accounts_transactions->fill($transaction_row);
                        $accounts_transactions->save();
                    }
                } elseif (!empty($accounts_transactions_id[$index])) {
                    AccountingAccountsTransaction::delete($accounts_transactions_id[$index]);
                }
            }

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_success')
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            print_r($e->getMessage());
            exit;
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');



        $user_id = request()->session()->get('user.id');

        $acc_trans_mapping = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->where('company_id', $company_id)->firstOrFail();

        if (!empty($acc_trans_mapping)) {
            $acc_trans_mapping->delete();
            AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)->delete();
        }

        return [
            'success' => 1,
            'msg' => __('lang_v1.deleted_success')
        ];
    }

    public function map(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');




        if (request()->ajax()) {

            $type = $request->get('type');
            $id = $request->get('id');

            if ($type == 'sell') {
                $transaction = Transaction::where('id', $id)->where('business_id', $business_id)->where('company_id', $company_id)
                    ->firstorFail();

                //setting defaults
                //if paid - Payment account = Sales
                //Deposit to = Account Receivable
                //Get all payment lines and map for each

                //if not paid - Payment account = Sales
                //Deposit to = Account Receivable

                $existing_payment_deposit = AccountingAccountsTransaction::where('transaction_id', $id)
                    ->whereIn('map_type', ['payment_account', 'deposit_to'])
                    ->get();
                //                $default_payment_account = !empty($existing_payment) ? AccountingAccount::find($existing_payment->accounting_account_id) : null;
                //                $default_deposit_to = !empty($existing_deposit) ? AccountingAccount::find($existing_deposit->accounting_account_id) : null;

                return view('accounting::journal_entry.map')
                    ->with(compact('transaction', 'type', 'existing_payment_deposit'));
            } elseif (in_array($type, ['purchase_payment', 'sell_payment'])) {
                $transaction_payment = TransactionPayment::where('id', $id)->where('business_id', $business_id)->where('company_id', $company_id)
                    ->firstorFail();

                $existing_payment = AccountingAccountsTransaction::where('transaction_payment_id', $id)
                    ->where('map_type', 'payment_account')
                    ->first();
                $existing_deposit = AccountingAccountsTransaction::where('transaction_payment_id', $id)
                    ->where('map_type', 'deposit_to')
                    ->first();
                $default_payment_account = !empty($existing_payment) ? AccountingAccount::find($existing_payment->accounting_account_id) : null;
                $default_deposit_to = !empty($existing_deposit) ? AccountingAccount::find($existing_deposit->accounting_account_id) : null;

                return view('accounting::journal_entry.map')
                    ->with(compact('transaction_payment', 'type', 'default_payment_account', 'default_deposit_to'));
            } elseif ($type == 'purchase') {
                $transaction = Transaction::where('id', $id)->where('business_id', $business_id)->where('company_id', $company_id)
                    ->firstorFail();

                //setting defaults
                //if paid - Payment account = Sales
                //Deposit to = Account Receivable
                //Get all payment lines and map for each

                //if not paid - Payment account = Sales
                //Deposit to = Account Receivable

                $existing_payment = AccountingAccountsTransaction::where('transaction_id', $id)
                    ->where('map_type', 'payment_account')
                    ->first();
                $existing_deposit = AccountingAccountsTransaction::where('transaction_id', $id)
                    ->where('map_type', 'deposit_to')
                    ->first();
                $default_payment_account = !empty($existing_payment) ? AccountingAccount::find($existing_payment->accounting_account_id) : null;
                $default_deposit_to = !empty($existing_deposit) ? AccountingAccount::find($existing_deposit->accounting_account_id) : null;

                return view('accounting::journal_entry.map')
                    ->with(compact('transaction', 'type', 'default_payment_account', 'default_deposit_to'));
            }
        }
    }

    public function saveMap(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');




        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $account_ids = $request->get('account_id');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $transaction = Transaction::where('business_id', $business_id)->where('company_id', $company_id)->where('id', $request->id)->firstorFail();
            if (array_sum($credits) != $transaction->final_total || array_sum($debits) != $transaction->final_total) {
                $output = [
                    'success' => 0,
                    'msg' => __('messages.total_debit_credit') . ' ' . $transaction->final_total . ' ريال سعودي '
                ];
                return $output;
            }
            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id, $company_id);
            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
            if (empty($ref_no)) {
                $prefix = !empty($accounting_settings['journal_entry_prefix']) ?
                    $accounting_settings['journal_entry_prefix'] : '';

                //Generate reference number
                $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $company_id, $prefix);
            }
            $acc_trans_mapping = new AccountingAccTransMapping();
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->company_id = $company_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = $request->type;
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = Carbon::now()->toDateTimeString();
            $acc_trans_mapping->save();

            //save details in account trnsactions table
            foreach ($account_ids as $index => $account_id) {
                if (!empty($account_id)) {
                    $transaction_row = [];
                    $transaction_row['accounting_account_id'] = $account_id;
                    $transaction_row['transaction_id'] = $request->id;
                    $transaction_row['transaction_payment_id'] = null;
                    if (!empty($credits[$index])) {
                        $transaction_row['amount'] = $credits[$index];
                        $transaction_row['type'] = 'credit';
                        $transaction_row['map_type'] = 'payment_account';
                    }

                    if (!empty($debits[$index])) {
                        $transaction_row['amount'] = $debits[$index];
                        $transaction_row['type'] = 'debit';
                        $transaction_row['map_type'] = 'deposit_to';
                    }
                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = Carbon::now()->toDateTimeString();
                    $transaction_row['sub_type'] = $request->type;
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;

                    //                    $accounts_transactions = new AccountingAccountsTransaction();
                    AccountingAccountsTransaction::query()->updateOrCreate(
                        [
                            'transaction_id' => $transaction_row['transaction_id'],
                            'map_type' => $transaction_row['map_type'],
                            'transaction_payment_id' => $transaction_row['transaction_payment_id'],
                            'accounting_account_id' => $transaction_row['accounting_account_id']
                        ],
                        [
                            'amount' => $transaction_row['amount'],
                            'type' =>  $transaction_row['type'],
                            'sub_type' => $transaction_row['sub_type'],
                            'created_by' => $transaction_row['created_by'],
                            'operation_date' => $transaction_row['operation_date']
                        ]
                    );
                    //                    $accounts_transactions->fill($transaction_row);
                    //                    $accounts_transactions->save();
                }
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }
}