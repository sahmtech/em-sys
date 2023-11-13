@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

    {{-- @include('accounting::layouts.nav') --}}

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'accounting::lang.journal_entry' )</h1>
    </section>
    <section class="content">

        {!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\JournalEntryController@store'),
            'method' => 'post', 'id' => 'journal_add_form']) !!}

        @component('components.widget', ['class' => 'box-primary'])

            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                        @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                        {!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('journal_date', __('accounting::lang.journal_date') . ':*') !!}
                        <div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
                            {!! Form::text('journal_date', @format_datetime('now'), ['class' => 'form-control datetimepicker', 'readonly', 'required']); !!}
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', __('lang_v1.additional_notes')) !!}
                        {!! Form::textarea('note', null, ['class' => 'form-control', 'rows' => 3]); !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">

                    <table class="table table-bordered table-striped hide-footer" id="journal_table">
                        <thead>
                        <tr>
                            <th class="col-md-1">#</th>
                            <th class="col-md-5">@lang( 'accounting::lang.account' )</th>
                            <th class="col-md-3">@lang( 'accounting::lang.debit' )</th>
                            <th class="col-md-3">@lang( 'accounting::lang.credit' )</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><i class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="1"></i></td>
                            <td>
                                {!! Form::select('account_id[' . 1 . ']', [], null,
                                            ['class' => 'form-control accounts-dropdown account_id', 
                                            'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                            </td>

                            <td>
                                {!! Form::text('debit[' . 1 . ']', null, ['class' => 'form-control input_number debit']); !!}
                            </td>

                            <td>
                                {!! Form::text('credit[' . 1 . ']', null, ['class' => 'form-control input_number credit']); !!}
                            </td>
                        </tr>
                        </tbody>

                        <tfoot>
                        <tr>
                            <th></th>
                            <th class="text-center">@lang( 'accounting::lang.total' )</th>
                            <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                            <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span></th>
                        </tr>
                        </tfoot>
                    </table>

                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <button type="button"
                            class="btn btn-primary pull-right btn-flat journal_add_btn">@lang('messages.save')</button>
                </div>
            </div>

        @endcomponent

        {!! Form::close() !!}
    </section>

@stop

@section('javascript')
    @include('accounting::accounting.common_js')

    <script type="text/javascript">
        $(document).ready(function () {
            $('.journal_add_btn').click(function (e) {
                //e.preventDefault();
                calculate_total();

                var is_valid = true;

                //check if same or not
                if ($('.total_credit_hidden').val() != $('.total_debit_hidden').val()) {
                    is_valid = false;
                    alert("@lang('accounting::lang.credit_debit_equal')");
                }

                //check if all account selected or not
                $('table > tbody  > tr').each(function (index, tr) {
                    var credit = __read_number($(tr).find('.credit'));
                    var debit = __read_number($(tr).find('.debit'));

                    if (credit != 0 || debit != 0) {
                        if ($(tr).find('.account_id').val() == '') {
                            is_valid = false;
                            alert("@lang('accounting::lang.select_all_accounts')");
                        }
                    }
                });

                if (is_valid) {
                    $('form#journal_add_form').submit();
                }

                return is_valid;
            });

            $('.credit').change(function () {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.debit').val('');
                }
                calculate_total();
            });
            $('.debit').change(function () {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.credit').val('');
                }
                calculate_total();
            });
        });

        function calculate_total() {
            var total_credit = 0;
            var total_debit = 0;
            $('table > tbody  > tr').each(function (index, tr) {
                var credit = __read_number($(tr).find('.credit'));
                total_credit += credit;

                var debit = __read_number($(tr).find('.debit'));
                total_debit += debit;
            });

            $('.total_credit_hidden').val(total_credit);
            $('.total_debit_hidden').val(total_debit);

            $('.total_credit').text(__currency_trans_from_en(total_credit));
            $('.total_debit').text(__currency_trans_from_en(total_debit));
        }
        $(document).on('click', '.fa-plus-square', function(){

            let counter = $('#journal_table tr').length - 1;
            $('tbody').append('<tr><td><i class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="'+counter+'"></i></td><td><select class="form-control accounts-dropdown account_id" style="width: 100%;" name="account_id['+counter+']"><option selected="selected" value="">يرجى الاختيار</option></select> </td> <td> <input class="form-control input_number debit" name="debit['+counter+']" type="text"> </td> <td> <input class="form-control input_number credit" name="credit['+counter+']" type="text"> </td> </tr>')
            $('select[name="account_id['+counter+']"]').select2({
                ajax: {
                    url: '{{route("accounts-dropdown")}}',
                    dataType: 'json',
                    processResults: function (data) {
                        return {
                            results: data
                        }
                    },
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.html;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });
            $('.credit').change(function () {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.debit').val('');
                }
                calculate_total();
            });
            $('.debit').change(function () {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.credit').val('');
                }
                calculate_total();
            });

        })

    </script>
@endsection