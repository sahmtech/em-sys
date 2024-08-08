<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title no-print">
                {!! __('essentials::lang.payroll_of_employee', [
                    'employee' => $payroll->name,
                    'date' => $month_name . ' ' . $year,
                ]) !!}
            </h4>
        </div>

        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="payroll-view">
                    <tr>
                        <td colspan="3">
                            @if (!empty(Session::get('business.logo')))
                                <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                    alt="Logo" style="width: auto; max-height: 50px; margin: auto;">
                            @endif
                            <div class="pull-right text-center">
                                <strong class="font-23">
                                    {{ Session::get('business.name') ?? '' }}
                                </strong>
                                <br>
                                {!! Session::get('business.business_address') ?? '' !!}
                            </div>
                            <br>
                            <div style="text-align: center;padding-top: 40px;">
                                @lang('essentials::lang.payrollslip_for_the_month', ['month' => $month_name, 'year' => $year])
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div class="pull-left" style="width: 50% !important;">
                                <strong>@lang('essentials::lang.name'):</strong>
                                {{ $payroll->name }}
                                <br>
                                <strong>@lang('essentials::lang.company'):</strong>
                                {{ $payroll->company }}
                                <br>

                                @if ($payroll?->project_name ?? false)
                                    <strong>@lang('essentials::lang.project'):</strong>
                                    {{ $payroll->project_name }}
                                    <br>
                                @else
                                    <strong>@lang('essentials::lang.department'):</strong>
                                    {{ $department->name ?? '' }}
                                    <br>
                                @endif

                                {{-- <strong>@lang('essentials::lang.designation'):</strong>
                                {{ $designation->name ?? '' }} --}}

                                {{-- <br>
                                <strong>@lang('lang_v1.primary_work_location'):</strong>
                                @if (!empty($location))
                                    {{ $location->name }}
                                @else
                                    {{ __('report.all_locations') }}
                                @endif
                                <br>

                                @if (!empty($payroll->transaction_for->id_proof_name) && !empty($payroll->transaction_for->id_proof_number))
                                    <strong>
                                        {{ ucfirst($payroll->transaction_for->id_proof_name) }}:
                                    </strong>
                                    {{ $payroll->transaction_for->id_proof_number }}
                                    <br>
                                @endif

                                <strong>@lang('lang_v1.tax_payer_id'):</strong>
                                {{ $bank_details['tax_payer_id'] ?? '' }}
                                <br> --}}
                            </div>
                            <div class="pull-right" style="width: 50% !important;">
                                <strong>@lang('lang_v1.bank_name'):</strong>
                                {{ $bank_details['bank_name'] ?? '' }}
                                <br>

                                {{-- <strong>@lang('lang_v1.branch'):</strong>
                                {{ $bank_details['branch'] ?? '' }}
                                <br> --}}

                                <strong>@lang('lang_v1.bank_code'):</strong>
                                {{ $bank_details['bank_code'] ?? '' }}
                                <br>

                                <strong>@lang('lang_v1.account_holder_name'):</strong>
                                {{ $bank_details['account_holder_name'] ?? '' }}
                                <br>

                                {{-- <strong>@lang('lang_v1.bank_account_no'):</strong>
                                {{ $bank_details['account_number'] ?? '' }}
                                <br> --}}
                            </div>
                        </td>
                    </tr>


                    <tr>

                        <td colspan="1" style="width: 25% !important;">
                            <strong>@lang('essentials::lang.work_days'):</strong>
                            {{ (int) $total_work_duration }}
                        </td>
                        <td colspan="1" style="width: 25% !important;">
                            <strong>@lang('essentials::lang.total_work_duration'):</strong>
                            {{ $total_days_present }}
                            <br>
                            <strong>@lang('essentials::lang.over_time_hours'):</strong>
                            {{ $payroll->over_time_hours }}
                        </td>
                        <td colspan="1" style="width: 25% !important;">
                            <strong>@lang('essentials::lang.absent'):</strong>
                            {{ $total_leaves }}
                            <br>
                            <strong>@lang('essentials::lang.late_hours'):</strong>
                            {{ $payroll->late }}
                        </td>

                    </tr>


                    <tr>
                        <td colspan="1" style="width: 33% !important;">
                            <strong>@lang('essentials::lang.salary'):</strong>
                            {{ $payroll->salary }}
                            <br>
                            <strong>@lang('essentials::lang.housing_allowance'):</strong>
                            {{ $payroll->housing_allowance }}
                            <br>
                            <strong>@lang('essentials::lang.transportation_allowance'):</strong>
                            {{ $payroll->transportation_allowance }}
                            <br>
                            <strong>@lang('essentials::lang.other_allowance'):</strong>
                            {{ $payroll->other_allowance }}
                            <br>


                        </td>
                        <td colspan="1" style="width: 33% !important;">
                            <strong>@lang('essentials::lang.over_time_hours_addition'):</strong>
                            {{ $payroll->over_time_hours }}
                            <br>
                            <strong>@lang('essentials::lang.additional_addition'):</strong>
                            {{ $payroll->additional_addition }}
                            <br>


                        </td>
                        <td colspan="1" style="width: 33% !important;">
                            <strong>@lang('essentials::lang.violations'):</strong>
                            {{ $payroll->violations }}
                            <br>
                            <strong>@lang('essentials::lang.absence_deduction'):</strong>
                            {{ $payroll->absence_deduction }}
                            <br>
                            <strong>@lang('essentials::lang.late_deduction'):</strong>
                            {{ $payroll->late_deduction }}
                            <br>
                            <strong>@lang('essentials::lang.other_deductions'):</strong>
                            {{ $payroll->other_deductions }}
                            <br>
                            <strong>@lang('essentials::lang.loan'):</strong>
                            {{ $payroll->loan }}
                            <br>


                        </td>
                    </tr>


                    <tr>
                        <td colspan="1" style="width: 33% !important;">
                            <strong>@lang('essentials::lang.total'):</strong>
                            {{ $payroll->total }}
                        </td>
                        <td colspan="1" style="width: 33% !important;">
                            <strong>@lang('essentials::lang.total_additions'):</strong>
                            {{ $payroll->total_additions }}

                        </td>
                        <td colspan="1" style="width: 33% !important;">
                            <strong>@lang('essentials::lang.total_deduction'):</strong>
                            {{ $payroll->total_deduction }}
                        </td>

                    </tr>
                    <tr>
                        <td colspan="3">
                            <strong>@lang('essentials::lang.final_salary'):</strong>
                            {{ $payroll->final_salary }}
                        </td>

                    </tr>

                    <tr>
                        <td colspan="3">
                            <br>
                            <br>

                        </td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            <strong>{{ __('sale.payment_info') }}:</strong>
                            <table class="table bg-gray table-slim">
                                <tr class="bg-green">
                                    <th>#</th>
                                    <th>{{ __('messages.date') }}</th>
                                    <th>{{ __('purchase.ref_no') }}</th>
                                    <th>{{ __('sale.amount') }}</th>
                                    <th>{{ __('sale.payment_mode') }}</th>
                                    <th>{{ __('sale.payment_note') }}</th>
                                </tr>
                                @php
                                    $total_paid = 0;
                                @endphp
                                @if ($payroll->payment_lines && !empty($payroll->payment_lines))
                                    @forelse($payroll->payment_lines as $payment_line)
                                        @php
                                            if ($payment_line->is_return == 1) {
                                                $total_paid -= $payment_line->amount;
                                            } else {
                                                $total_paid += $payment_line->amount;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ @format_date($payment_line->paid_on) }}</td>
                                            <td>{{ $payment_line->payment_ref_no }}</td>
                                            <td><span class="display_currency"
                                                    data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                                            <td>
                                                {{ $payment_types[$payment_line->method] }}
                                            </td>
                                            <td>
                                                @if ($payment_line->note)
                                                    {{ ucfirst($payment_line->note) }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">@lang('purchase.no_records_found')</td>
                                        </tr>
                                    @endforelse
                                @endif
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <strong>@lang('brand.note'):</strong><br>
                            {{ $payroll->staff_note ?? '' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="modal-footer no-print">
            <button type="button" class="btn btn-primary" aria-label="Print"
                onclick="$(this).closest('div.modal-content').find('.modal-body').printThis();">
                <i class="fa fa-print"></i> @lang('messages.print')
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<style type="text/css">
    #payroll-view>thead>tr>th,
    #payroll-view>tbody>tr>th,
    #payroll-view>tfoot>tr>th,
    #payroll-view>thead>tr>td,
    #payroll-view>tbody>tr>td,
    #payroll-view>tfoot>tr>td {
        border: 1px solid #1d1a1a;
    }
</style>
