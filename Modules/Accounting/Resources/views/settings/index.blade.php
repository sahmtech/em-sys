@extends('layouts.app')

@section('title', __('messages.settings'))

@section('content')

    {{-- @include('accounting::layouts.nav') --}}

    <!-- Content Header (Page header) -->
    <section class="content-header">
        @if (isset($breadcrumbs))
            <nav>
                <ol class="breadcrumb">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @if ($breadcrumb['url'])
                            <li class="breadcrumb-item">
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                            </li>
                        @else
                            <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1>@lang('messages.settings')</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#account_setting" data-toggle="tab" aria-expanded="true">
                                @lang('accounting::lang.account_setting')
                            </a>
                        </li>
                        <li>
                            <a href="#sub_type_tab" data-toggle="tab" aria-expanded="true">
                                @lang('accounting::lang.account_sub_type')
                            </a>
                        </li>
                        <li>
                            <a href="#detail_type_tab" data-toggle="tab" aria-expanded="true">
                                @lang('accounting::lang.detail_type')
                            </a>
                        </li>
                        <li>
                            <a href="#auto_journal_tab" data-toggle="tab" aria-expanded="true">
                                @lang('accounting::lang.auto_journal')
                            </a>
                        </li>
                        <li>
                            <a href="#auto_mapping_tab" data-toggle="tab" aria-expanded="true">
                                @lang('accounting::lang.auto_mapping')
                            </a>
                        </li>
                        <li>
                            <a href="#bank_accounts" data-toggle="tab" aria-expanded="true">
                                @lang('lang_v1.bank_accounts')
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="account_setting">
                            <div class="row mb-12">
                                <div class="col-md-4">
                                    <a class="btn btn-danger accounting_reset_data"
                                        href="{{ action('\Modules\Accounting\Http\Controllers\SettingsController@resetData') }}"
                                        data-href="{{ action('\Modules\Accounting\Http\Controllers\SettingsController@resetData') }}">
                                        @lang('accounting::lang.reset_data')
                                    </a>
                                </div>
                            </div>
                            <br>
                            {!! Form::open([
                                'action' => '\Modules\Accounting\Http\Controllers\SettingsController@saveSettings',
                                'method' => 'post',
                            ]) !!}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('journal_entry_prefix', __('accounting::lang.journal_entry_prefix') . ':') !!}
                                        {!! Form::text(
                                            'journal_entry_prefix',
                                            !empty($accounting_settings['journal_entry_prefix']) ? $accounting_settings['journal_entry_prefix'] : '',
                                            ['class' => 'form-control ', 'id' => 'journal_entry_prefix'],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('transfer_prefix', __('accounting::lang.transfer_prefix') . ':') !!}
                                        {!! Form::text(
                                            'transfer_prefix',
                                            !empty($accounting_settings['transfer_prefix']) ? $accounting_settings['transfer_prefix'] : '',
                                            ['class' => 'form-control ', 'id' => 'transfer_prefix'],
                                        ) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group pull-right">
                                        {{ Form::submit('update', ['class' => 'btn btn-danger']) }}
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>

                        <div class="tab-pane" id="sub_type_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-primary pull-right" id="add_account_sub_type">
                                        <i class="fas fa-plus"></i> @lang('messages.add')
                                    </button>
                                </div>
                                <div class="col-md-12">
                                    <br>
                                    <table class="table table-bordered table-striped" id="account_sub_type_table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    @lang('accounting::lang.account_sub_type')
                                                </th>
                                                <th>
                                                    @lang('accounting::lang.account_type')
                                                </th>
                                                <th>
                                                    @lang('messages.action')
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <div class="tab-pane" id="detail_type_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-primary pull-right" id="add_detail_type">
                                        <i class="fas fa-plus"></i> @lang('messages.add')
                                    </button>
                                </div>
                                <div class="col-md-12">
                                    <br>
                                    <table class="table table-striped" id="detail_type_table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>
                                                    @lang('accounting::lang.detail_type')
                                                </th>
                                                <th>
                                                    @lang('accounting::lang.parent_type')
                                                </th>
                                                <th>
                                                    @lang('lang_v1.description')
                                                </th>
                                                <th>
                                                    @lang('messages.action')
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="auto_journal_tab">
                            <div class="row">

                                <div class="col-md-12">
                                    <br>
                                    <table class="table table-bordered table-striped" id="auto_journal_table">
                                        <thead>
                                            <tr>
                                                <th>@lang('messages.action')</th>
                                                <th>@lang('messages.date')</th>
                                                <th>@lang('sale.invoice_no')</th>
                                                <th>@lang('sale.customer_name')</th>
                                                <th>@lang('lang_v1.contact_no')</th>
                                                <th>@lang('sale.location')</th>
                                                <th>@lang('sale.payment_status')</th>
                                                <th>@lang('lang_v1.payment_method')</th>
                                                <th>@lang('sale.total_amount')</th>
                                                <th>@lang('sale.total_paid')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                                <th>@lang('sale.sell_note')</th>
                                                <th>@lang('sale.staff_note')</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="auto_mapping_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <br>
                                    <table class="table table-striped" id="auto_mapping_table"
                                        style="width: 100% !important;">
                                        <thead>
                                            <tr>
                                                <th>@lang('messages.action')</th>
                                                <th>@lang('lang_v1.sub_type')</th>
                                                <th>@lang('lang_v1.method')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="bank_accounts">
                            @include('business.partials.settings_bank_accounts')
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="add_BankAccounts" tabindex="-1" role="dialog"></div>
        <div class="modal fade" id="edit_BankAccounts" tabindex="-1" role="dialog"></div>
    </section>
    @include('accounting::account_type.create')
    <div class="modal fade" id="edit_account_type_modal" tabindex="-1" role="dialog">
    </div>
@stop
@push('script')
    <script>
        $(document).ready(function() {
            account_sub_type_table = $('#account_sub_type_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action('\Modules\Accounting\Http\Controllers\AccountTypeController@index') }}?account_type=sub_type",
                columnDefs: [{
                    targets: [2],
                    orderable: false,
                    searchable: false,
                }, ],
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'account_primary_type',
                        name: 'account_primary_type'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
            });

            detail_type_table = $('#detail_type_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action('\Modules\Accounting\Http\Controllers\AccountTypeController@index') }}?account_type=detail_type",
                columnDefs: [{
                    targets: 3,
                    orderable: false,
                    searchable: false,
                }, ],
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'parent_type',
                        name: 'parent_type'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
            });
            let journal_url = "{{ url('accounting/transactions') }}" + "?type=sell&datatable=sell&from=journal";
            journal_auto = $('#auto_journal_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": journal_url,
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'payment_methods',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid',
                        "searchable": false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                    {
                        data: 'additional_notes',
                        name: 'additional_notes'
                    },
                    {
                        data: 'staff_note',
                        name: 'staff_note'
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#auto_journal_table'));
                }
            });
            mapping_settings = $('#auto_mapping_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": "{{ route('settings.auto_mapping') }}",
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'sub_type',
                        name: 'sub_type'
                    },
                    {
                        data: 'method',
                        name: 'method'
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#auto_mapping_table'));
                }
            })

            $('#add_account_sub_type').click(function() {
                $('#account_type').val('sub_type')
                $('#account_type_title').text("{{ __('accounting::lang.add_account_sub_type') }}");
                $('#description_div').addClass('hide');
                $('#parent_id_div').addClass('hide');
                $('#account_type_div').removeClass('hide');
                $('#create_account_type_modal').modal('show');
            });

            $('#add_detail_type').click(function() {
                $('#account_type').val('detail_type')
                $('#account_type_title').text("{{ __('accounting::lang.add_detail_type') }}");
                $('#description_div').removeClass('hide');
                $('#parent_id_div').removeClass('hide');
                $('#account_type_div').addClass('hide');
                $('#create_account_type_modal').modal('show');
            })
        });
        $(document).on('hidden.bs.modal', '#create_account_type_modal', function(e) {
            $('#create_account_type_form')[0].reset();
        })
        $(document).on('submit', 'form#create_account_type_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('#create_account_type_modal').modal('hide');
                        toastr.success(result.msg);
                        if (result.data.account_type == 'sub_type') {
                            account_sub_type_table.ajax.reload();
                        } else {
                            detail_type_table.ajax.reload();
                        }
                        $('#create_account_type_form').find('button[type="submit"]').attr('disabled',
                            false);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('submit', 'form#edit_account_type_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: 'PUT',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('#edit_account_type_modal').modal('hide');
                        toastr.success(result.msg);
                        if (result.data.account_type == 'sub_type') {
                            account_sub_type_table.ajax.reload();
                        } else {
                            detail_type_table.ajax.reload();
                        }

                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('click', 'button.delete_account_type_button', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                account_sub_type_table.ajax.reload();
                                detail_type_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(document).on('click', 'button.accounting_reset_data', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                text: "@lang('accounting::lang.reset_help_txt')",
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    window.location.href = href;
                }
            });
        });
        $(document).on('shown.bs.modal', '.contains_select2, .view_modal', function() {
            $('select').select2({
                ajax: {
                    url: '{{ route('accounts-dropdown') }}',
                    dataType: 'json',
                    processResults: function(data) {
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

        });

        $(document).on('click', '#journal_table_form .fa-plus-square', function() {

            let counter = $('#journal_table_form tr').length - 1;
            $('#journal_table_form tbody').append(
                '<tr><td><i class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="' + counter +
                '"></i></td><td><select class="form-control accounts-dropdown account_id" style="width: 100%;" name="account_id[' +
                counter +
                ']"><option selected="selected" value="">يرجى الاختيار</option></select> </td> <td> <input class="form-control input_number credit" name="credit[' +
                counter +
                ']" type="text"> </td> <td> <input class="form-control input_number debit" name="debit[' +
                counter + ']" type="text"></td>   </tr>')
            $('select[name="account_id[' + counter + ']"]').select2({
                ajax: {
                    url: '{{ route('accounts-dropdown') }}',
                    dataType: 'json',
                    processResults: function(data) {
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

        })

        $(document).on('click', '#auto_mapping_form .fa-plus-square', function() {
            let counter = $('#auto_mapping_form tr').length - 1;
            $('#auto_mapping_form tbody').append(
                '<tr><td><i class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="' + counter +
                '"></i></td><td><select class="form-control accounts-dropdown account_id" style="width: 100%;" name="account_id[' +
                counter +
                ']"><option selected="selected" value="">يرجى الاختيار</option></select> </td> <td> <label class="radio-inline"><input value="credit" type="radio" name="type[' +
                counter +
                ']">@lang('accounting::lang.credit')</label><label class="radio-inline"><input value="debit" type="radio" name="type[' +
                counter + ']" checked>@lang('accounting::lang.debit')</label> </td> </tr>')
            $('select[name="account_id[' + counter + ']"]').select2({
                ajax: {
                    url: '{{ route('accounts-dropdown') }}',
                    dataType: 'json',
                    processResults: function(data) {
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

        })

        $(document).on('submit', "form#save_accounting_map", function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            transaction_type = $('#transaction_type').val();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        if (transaction_type == 'sell') {
                            journal_auto.ajax.reload();
                        } else if (transaction_type == 'sell_payment') {
                            sell_payment_table.ajax.reload();
                        } else if (transaction_type == 'purchase') {
                            purchase_table.ajax.reload();
                        } else if (transaction_type == 'purchase_payment') {
                            purchase_payment_table.ajax.reload();
                        }
                    } else {
                        swal(
                            'opps!',
                            result.msg,
                            'error'
                        )
                    }
                },
            });


        });


        {{-- $(document).on('click', '.fa-plus-square', function(){ --}}
        {{--    let counter = $('.fa-plus-square').last().data('id') + 1; --}}
        {{--    let html = '<div class="row"><div class="col-md-2"> <div class="form-group"><i class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="'+counter+'" style="margin-top: 28px"></i></div></div><div class="col-md-5"><div class="form-group">{!! Form::label('payment_account', __('accounting::lang.payment_account') . ':*' ) !!}{!! Form::select('payment_account[' . 'counter' . ']', [],  null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}</div> </div> <div class="col-md-5"> <div class="form-group">{!! Form::label('deposit_to', __('accounting::lang.deposit_to') . ':*' ) !!}{!! Form::select('deposit_to[' . 'counter' . ']', [], null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.deposit_to'), 'required' => 'required']); !!}</div> </div> </div>'; --}}
        {{--    html = html.replace('counter', ''+counter+'') --}}
        {{--    html = html.replace('counter', ''+counter+'') --}}
        {{--    $('.modal-body').append(html) --}}
        {{--    $('select[name="payment_account['+counter+']"]').select2({ --}}
        {{--        ajax: { --}}
        {{--            url: '{{route("accounts-dropdown")}}', --}}
        {{--            dataType: 'json', --}}
        {{--            processResults: function (data) { --}}
        {{--                return { --}}
        {{--                    results: data --}}
        {{--                } --}}
        {{--            }, --}}
        {{--        }, --}}
        {{--        escapeMarkup: function(markup) { --}}
        {{--            return markup; --}}
        {{--        }, --}}
        {{--        templateResult: function(data) { --}}
        {{--            return data.html; --}}
        {{--        }, --}}
        {{--        templateSelection: function(data) { --}}
        {{--            return data.text; --}}
        {{--        } --}}
        {{--    }); --}}
        {{--    // $('.credit').change(function () { --}}
        {{--    //     if ($(this).val() > 0) { --}}
        {{--    //         $(this).parents('tr').find('.debit').val(''); --}}
        {{--    //     } --}}
        {{--    //     calculate_total(); --}}
        {{--    // }); --}}
        {{--    // $('.debit').change(function () { --}}
        {{--    //     if ($(this).val() > 0) { --}}
        {{--    //         $(this).parents('tr').find('.credit').val(''); --}}
        {{--    //     } --}}
        {{--    //     calculate_total(); --}}
        {{--    // }); --}}

        {{-- }) --}}
    </script>
@endpush
