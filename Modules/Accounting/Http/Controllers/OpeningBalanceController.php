<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccountType;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Entities\CostCenter;
use Modules\Accounting\Entities\OpeningBalance;
use Yajra\DataTables\Facades\DataTables;

class OpeningBalanceController extends Controller
{
    protected function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            !(auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }
        $sub_types_obj = AccountingAccount::query()->whereIn('account_primary_type', ['asset', 'liability'])
            ->where(function ($q) use ($business_id) {
                $q->whereNull('business_id')
                    ->orWhere('business_id', $business_id);
            })
            ->get();
        foreach ($sub_types_obj as $st) {
            $sub_types[] = [
                'id' => $st->id,
                'name' => $st->name,
                'status' => $st->status
            ];
        }
        if (request()->ajax()) {
            $openingBalances = AccountingAccountsTransaction::query()->where('sub_type', 'opening_balance')
                ->orderBy('id');
            return Datatables::of($openingBalances)
                ->addColumn(
                    'action', function ($row) {
                    $deleteUrl = action('\Modules\Accounting\Http\Controllers\OpeningBalanceController@destroy', [$row->id]);
                    return
                        '
                        <button data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_opening_balance_button"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</button>
                    ';
                })
                ->addColumn('account_name', function ($row) {
                    $acc = $row->account->name;
                    return $acc;
                })
                ->addColumn('account_number', function ($row) {
                    $acc = $row->account->gl_code;
                    return $acc;
                })
                ->addColumn('debtor', function ($row) {
                    if ($row->type == 'debit') {
                        return $row->amount;
                    } else {
                        return 0;
                    }
                })
                ->addColumn('creditor', function ($row) {
                    if ($row->type == 'credit') {
                        return $row->amount;
                    } else {
                        return 0;
                    }
                })
                ->rawColumns([
                    'action',
                    'account_name',
                    'account_number',
                    'debtor',
                    'creditor'
                ])
                ->make(true);
        }

        return view('accounting::opening_balance.index', compact('sub_types'));
    }

    protected function store(Request $request)
    {
        $rules = [
            'year' => 'required|String',
            'accounting_account_id' => 'required|String|exists:accounting_accounts,id',
            'type' => 'required|in:creditor,debtor',
            'value' => 'required|Numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $failedRules = $validator->failed();
//            if (isset($failedRules['ar_name']['min']) || isset($failedRules['ar_name']['max'])) {
//                return response()->json(['fail' => __("messages.something_went_wrong")]);
//            }
            return response()->json(['success' => false,
                'msg' => __("messages.something_went_wrong")
            ]);
        }
        $validated = $validator->validated();
        $validated['created_by'] = auth()->user()->id;
        $validated['business_id'] = $request->session()->get('user.business_id');
        $transaction = AccountingAccountsTransaction::query()->create([
            'accounting_account_id' => $validated['accounting_account_id'],
            'amount' => $validated['value'],
            'type' => $validated['type'] == 'creditor' ? 'credit' : 'debit',
            'sub_type' => 'opening_balance'
        ]);
        $validated['accounts_account_transaction_id'] = $transaction->id;
        OpeningBalance::query()->create([
            'year' => $validated['year'],
            'business_id' => $validated['business_id'],
            'type' => $validated['type'],
            'accounts_account_transaction_id' => $validated['accounts_account_transaction_id']
        ]);
        return response()->json(['success' => true,
            'msg' => __("lang_v1.added_success")
        ]);
    }

    protected function update(Request $request, $id)
    {
        $openingBalance = OpeningBalance::query()->find($id);
        $rules = [
            'year' => 'required|String',
            'accounting_account_id' => 'required|String|exists:accounting_accounts,id',
            'type' => 'required|in:creditor,debtor',
            'value' => 'required|Numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $failedRules = $validator->failed();
//            if (isset($failedRules['ar_name']['min']) || isset($failedRules['ar_name']['max'])) {
//                return response()->json(['fail' => __("messages.something_went_wrong")]);
//            }
            return response()->json(['success' => false,
                'msg' => __("messages.something_went_wrong")
            ]);
        }
        $openingBalance->update([
            'year' => $request->year,
            'value' => $request->value,
            'accounting_account_id' => $request->accounting_account_id,
            'type' => $request->type
        ]);
        return response()->json(['success' => true,
            'msg' => __("lang_v1.updated_success")
        ]);
    }

    protected function destroy($id)
    {
        if (\request()->ajax()) {
            AccountingAccountsTransaction::query()->find($id)->delete();
            OpeningBalance::query()->where('accounts_account_transaction_id', $id)->first()->delete();
            return [
                'success' => true,
                'msg' => __("lang_v1.deleted_success")
            ];
        }
    }

    protected function calcEquation()
    {
        $business_id = \request()->session()->get('user.business_id');
        $credit = AccountingAccountsTransaction::query()->where('sub_type', 'opening_balance')->where('type', 'credit')->sum('amount');
        $debt = AccountingAccountsTransaction::query()->where('sub_type', 'opening_balance')->where('type', 'debit')->sum('amount');
        return response()->json(['credit' => $credit, 'debt' => $debt]);
    }
}

//<button data-id="' . $row->id . '" data-accountid="' . $row->accounting_account_id . '" data-year="' . $row->year . '" data-type="' . $row->type . '" data-value="' . $row->value . '" class="btn btn-xs btn-primary btn-modal edit_opening_balance" data-toggle="modal" data-target="#edit_opening_balance_modal"><i class="glyphicon glyphicon-edit"></i>' . __("messages.edit") . '</button>