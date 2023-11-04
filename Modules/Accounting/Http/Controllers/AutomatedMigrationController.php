<?php

namespace Modules\Accounting\Http\Controllers;

use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Utils\AccountingUtil;
use Illuminate\Support\Facades\Log;

class AutomatedMigrationController extends Controller
{

    protected $util;

    public function __construct(Util $util, ModuleUtil $moduleUtil, AccountingUtil $accountingUtil)
    {
        $this->util = $util;
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('accounting::AutomatedMigration.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('accounting::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (
            !(auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            !(auth()->user()->can('accounting.add_journal'))
        ) {
            abort(403, 'Unauthorized action.');
        }
        // try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $account_ids_1 = $request->get('account_id1');
            $account_ids_2 = $request->get('account_id2');

            $type_1 = $request->get('type1');
            $type_2 = $request->get('type2');
            $amount_type_1 = $request->get('amount_type1');
            $amount_type_2 = $request->get('amount_type2');

            $journal_date = $request->get('journal_date');

            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);

            $transaction = Transaction::create([
                'business_id' => $business_id,
                'type' => $request->get('type'),
                'status' => 'final',
                'payment_status' => $request->get('payment_status'),
                'created_by' => $user_id,
            ]);
            $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
            $prefix = !empty($accounting_settings['journal_entry_prefix']) ?
                $accounting_settings['journal_entry_prefix'] : '';


                $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
                $_ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
            $acc_trans_mapping = new AccountingAccTransMapping();
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->type = 'journal_entry';
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $acc_trans_mapping->save();

            $_acc_trans_mapping = new AccountingAccTransMapping();
            $_acc_trans_mapping->business_id = $business_id;
            $_acc_trans_mapping->ref_no = $_ref_no;
            $_acc_trans_mapping->type = 'journal_entry';
            $_acc_trans_mapping->created_by = $user_id;
            $_acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $_acc_trans_mapping->save();
            
            foreach ($account_ids_1 as $index => $account_id) {
                if (!empty($account_id)) {

                    $transaction_row = [];
                    $transaction_row['accounting_account_id'] = $account_id;


                    $transaction_row['type'] =  $type_1[$index];




                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                    $transaction_row['sub_type'] = 'journal_entry';
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;
                    $transaction_row['transaction_id'] =$transaction->id;
                   

                    $accounts_transactions = new AccountingAccountsTransaction();
                    $accounts_transactions->fill($transaction_row);
                    $accounts_transactions->save();
                }
            }

            //save details in account trnsactions table
            // if ($account_ids_2[1] != null) {
                foreach ($account_ids_2 as $index => $account_id_) {
                    if (!empty($account_id_)) {

                        $transaction_row_ = [];
                        $transaction_row_['accounting_account_id'] = $account_id_;


                        $transaction_row_['type'] =  $type_2[$index];




                        $transaction_row_['created_by'] = $user_id;
                        $transaction_row_['operation_date'] = $this->util->uf_date($journal_date, true);
                        $transaction_row_['sub_type'] = 'journal_entry';
                        $transaction_row_['acc_trans_mapping_id'] = $_acc_trans_mapping->id;
                        $transaction_row_['transaction_id'] =$transaction->id;

                        $accounts_transactions_ = new AccountingAccountsTransaction();
                        $accounts_transactions_->fill($transaction_row_);
                        $accounts_transactions_->save();
                    }
                }
            // }






            DB::commit();


            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success')
            ];
        // } catch (\Exception $e) {
        //     // DB::rollBack();
        //     Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

        //     $output = [
        //         'success' => 0,
        //         'msg' => __('messages.something_went_wrong')
        //     ];
        // }


        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('accounting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('accounting::edit');
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