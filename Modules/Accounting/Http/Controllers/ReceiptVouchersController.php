<?php

namespace Modules\Accounting\Http\Controllers;

use App\Contact;
use App\Events\TransactionPaymentAdded;
use App\Exceptions\AdvanceBalanceNotAvailable;
use App\Http\Controllers\Controller;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class ReceiptVouchersController extends Controller
{
    protected $transactionUtil;

    protected $moduleUtil;

    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    protected function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_receipt_vouchers = auth()->user()->can('accounting.receipt_vouchers');
        if (!($is_admin || $can_receipt_vouchers)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $transactions = TransactionPayment::with('transaction')->where('payment_type', 'credit')
            ->orWhereHas('transaction', function ($q) {
                $q->where('type', 'sell')->orWhere('type', 'employee');
            })
            ->orderBy('id');

        $contacts = Contact::whereNot('id', 1)->whereIn('type', ['customer', 'converted'])->get();

        $transactionUtil = new TransactionUtil();
        $moduleUtil = new ModuleUtil();
        $accounts = $moduleUtil->accountsDropdown($business_id, $company_id, true, false, true);

        $payment_types = $transactionUtil->payment_types(null, true, $business_id, $company_id);

        if (request()->ajax()) {
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $transactions->whereDate('created_at', '>=', $start)
                    ->whereDate('created_at', '<=', $end);
            }
            if (\request()->customer_id) {
                $transactions->where('payment_for', request()->customer_id);
            }
            if (\request()->payment_status) {
                $transactions->whereHas('transaction', function ($q) {
                    $q->where('payment_status', request()->payment_status);
                });
            }
            return Datatables::of($transactions)
                ->addColumn(
                    'action',
                    function ($row) {
                        return '<button type="button" class="btn btn-primary btn-xs view_payment" style="width:100%"
                data-href="' . action([\App\Http\Controllers\TransactionPaymentController::class, "view_receipt_vouchers"], [$row->id]) . '"><i class="fa fa-print" style="padding-left: 4px;padding-right: 4px;"></i>طباعة
                    </button>';
                    }
                )
                ->addColumn(
                    'voucher_number',
                    function ($row) {
                        return $row->transaction?->invoice_no;
                    }
                )
                ->addColumn(
                    'contact_id',
                    function ($row) {
                        if ($row->contact) {
                            return $row->contact->supplier_business_name ?: '';
                        } elseif ($row->transaction) {
                            return $row->transaction->contact ? $row->transaction->contact->supplier_business_name ?: '' : '';
                        } elseif (substr($row->payment_ref_no, 0, 2) != "EP") {
                            return Contact::where('id', $row->payment_for)?->first()?->supplier_business_name ?? '';
                        } else {
                            error_log($row);
                            return '';
                        }
                    }
                )
                ->addColumn(
                    'payment_for',
                    function ($row) {
                        if (substr($row->payment_ref_no, 0, 2) === "EP") {
                            $user = User::where('id', $row->payment_for)->first();
                            return $user->first_name . ' ' . $user->last_name;
                        } else {
                            return '';
                        }
                    }
                )
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d g:i A');
                })
                ->rawColumns([
                    'voucher_number',
                    'contact_id',
                    'action',
                ])
                ->make(true);
        }

        $users = User::whereIn('user_type', ['employee', 'manager', 'department_head'])->where('company_id', $company_id)->where('status', '!=', 'inactive')->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        return view('accounting::receipt_vouchers.index', compact('payment_types', 'accounts', 'contacts', 'users'));
    }

    protected function store(Request $request)
    {

        $transaction_id = $request->input('transaction_id');
        if ($transaction_id) {
            try {
                $business_id = $request->session()->get('user.business_id');
                $company_id = Session::get('selectedCompanyId');

                $transaction = Transaction::where('business_id', $business_id)->where('company_id', $company_id)->with(['contact'])->findOrFail($transaction_id);

                $transaction_before = $transaction->replicate();

                if (!(auth()->user()->can('purchase.payments') || auth()->user()->can('sell.payments') || auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
                    //temp  abort(403, 'Unauthorized action.');
                }

                if ($transaction->payment_status != 'paid') {
                    $inputs = $request->only(['amount', 'method', 'note']);
                    $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                    $inputs['transaction_id'] = $transaction->id;
                    $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
                    $inputs['created_by'] = auth()->user()->id;
                    $inputs['payment_for'] = $transaction->contact_id;

                    if ($inputs['method'] == 'custom_pay_1') {
                        $inputs['transaction_no'] = $request->input('transaction_no_1');
                    } elseif ($inputs['method'] == 'custom_pay_2') {
                        $inputs['transaction_no'] = $request->input('transaction_no_2');
                    } elseif ($inputs['method'] == 'custom_pay_3') {
                        $inputs['transaction_no'] = $request->input('transaction_no_3');
                    }

                    $prefix_type = 'purchase_payment';
                    if (in_array($transaction->type, ['sell', 'sell_return'])) {
                        $prefix_type = 'sell_payment';
                    } elseif (in_array($transaction->type, ['expense', 'expense_refund'])) {
                        $prefix_type = 'expense_payment';
                    }

                    DB::beginTransaction();

                    $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
                    //Generate reference number
                    $inputs['payment_ref_no'] = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);

                    $inputs['business_id'] = $request->session()->get('business.id');
                    $inputs['company_id'] = $company_id;

                    $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

                    //Pay from advance balance
                    $payment_amount = $inputs['amount'];
                    $contact_balance = !empty($transaction->contact) ? $transaction->contact->balance : 0;
                    if ($inputs['method'] == 'advance' && $inputs['amount'] > $contact_balance) {
                        throw new AdvanceBalanceNotAvailable(__('lang_v1.required_advance_balance_not_available'));
                    }

                    if (!empty($inputs['amount'])) {
                        $tp = TransactionPayment::create($inputs);
                        $inputs['transaction_type'] = $transaction->type;
                        event(new TransactionPaymentAdded($tp, $inputs));
                    }

                    //update payment status
                    $payment_status = $this->transactionUtil->updatePaymentStatus($transaction_id, $transaction->final_total);
                    $transaction->payment_status = $payment_status;

                    $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);


                    DB::commit();
                }

                $output = [
                    'success' => true,
                    'msg' => __('purchase.payment_added_success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $msg = __('messages.something_went_wrong');

                if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                    $msg = $e->getMessage();
                } else {
                    Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                }

                $output = [
                    'success' => false,
                    'msg' => $msg,
                ];
            }

            return redirect()->back()->with(['status' => $output]);
        } else {
            if (!(auth()->user()->can('sell.payments') || auth()->user()->can('purchase.payments'))) {
                //temp  abort(403, 'Unauthorized action.');
            }
            try {
                DB::beginTransaction();

                $business_id = request()->session()->get('business.id');
                $company_id = Session::get('selectedCompanyId');


                $tp = $this->transactionUtil->payContact($request, true, $company_id);


                $pos_settings = !empty(session()->get('business.pos_settings')) ? json_decode(session()->get('business.pos_settings'), true) : [];
                $enable_cash_denomination_for_payment_methods = !empty($pos_settings['enable_cash_denomination_for_payment_methods']) ? $pos_settings['enable_cash_denomination_for_payment_methods'] : [];
                //add cash denomination
                if (
                    in_array($tp->method, $enable_cash_denomination_for_payment_methods)
                    && !empty($request->input('denominations'))
                    && !empty($pos_settings['enable_cash_denomination_on'])
                    && $pos_settings['enable_cash_denomination_on'] == 'all_screens'
                ) {
                    $denominations = [];

                    foreach ($request->input('denominations') as $key => $value) {
                        if (!empty($value)) {
                            $denominations[] = [
                                'business_id' => $business_id,
                                'company_id' => $company_id,

                                'amount' => $key,
                                'total_count' => $value,
                            ];
                        }
                    }
                    if (!empty($denominations)) {
                        $tp->denominations()->createMany($denominations);
                    }
                }

                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('purchase.payment_added_success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                dd('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                $output = [
                    'success' => false,
                    'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage(),
                ];
            }

            return redirect()->back()->with(['status' => $output]);
        }
    }

    protected function loadNeededData(Request $request)
    {
        if ($request->contact_id) {
            $contact = Contact::query()->where('id', $request->contact_id)->first();
            if ($contact) {
                $trans = $contact->transactions->where('status', 'final')->whereIn('payment_status', ['due', 'partial'])->get();
                return response()->json(['success' => true, 'trans' => $trans], 200);
            }
        }
        if ($request->transaction_id) {
            $transaction = Transaction::query()->where('type', 'sell')->where('id', $request->transaction_id)->first();
            if ($transaction) {
                $transactionUtil = new TransactionUtil();
                $paid_amount = $transactionUtil->getTotalPaid($request->transaction_id);
                $amount = $transaction->final_total - $paid_amount;
                if ($amount < 0) {
                    $amount = 0;
                }
                return response()->json(['success' => true, 'amount' => $amount], 200);
            }
        }
        return response()->json([
            'success' => false,
            'msg' => __("messages.something_went_wrong")
        ]);
    }
}
