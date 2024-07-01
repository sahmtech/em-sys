@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')

    <section class="content-header">
        <h1> @lang('agent.time_sheet')
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#payrolls_groups_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-coins" aria-hidden="true"></i>
                                @lang('agent.time_sheet_groups')
                            </a>
                        </li>
                        <li>
                            <a href="#payrolls_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-layer-group" aria-hidden="true"></i>
                                @lang('agent.time_sheet')
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <br><br>
                        <div class="tab-pane active" id="payrolls_groups_tab">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary " data-toggle="modal"
                                    data-target="#payroll_modal">
                                    <i class="fa fa-plus"></i>
                                    @lang('messages.add')
                                </button>
                            </div>
                            <br><br><br>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payroll_group_table"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('essentials::lang.name')</th>
                                                <th>@lang('sale.status')</th>
                                                <th>@lang('sale.payment_status')</th>
                                                <th>@lang('essentials::lang.total_gross_amount')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                                <th>@lang('lang_v1.created_at')</th>
                                                <th>@lang('messages.action')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="payrolls_tab">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payrolls_table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('essentials::lang.employee')</th>
                                                <th>@lang('essentials::lang.month_year')</th>
                                                <th>@lang('purchase.ref_no')</th>
                                                <th>@lang('sale.total_amount')</th>
                                                <th>@lang('sale.payment_status')</th>
                                                <th>@lang('messages.action')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

        <div class="modal fade" id="payroll_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open([
                        'url' => route('agentTimeSheet.create'),
                        'method' => 'get',
                        'id' => 'add_payroll_step1',
                    ]) !!}

                    <div class="modal-body">
                        <div class="form-group">
                            {!! Form::label('projects', __('agent.projects') . ':*') !!}
                            {!! Form::select('projects', $projects, null, [
                                'class' => 'form-control select2',
                                'style' => 'width: 100%;',
                                'id' => 'projects',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('employee_ids', __('essentials::lang.employee') . ':*') !!}
                            <button type="button" class="btn btn-primary btn-xs select-all">
                                @lang('lang_v1.select_all')
                            </button>
                            <button type="button" class="btn btn-primary btn-xs deselect-all">
                                @lang('lang_v1.deselect_all')
                            </button>
                            {!! Form::select('employee_ids[]', $workers, null, [
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'width: 100%;',
                                'multiple',
                                'id' => 'employee_ids',
                            ]) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('month_year', __('essentials::lang.month_year') . ':*') !!}
                            <div class="input-group">
                                {!! Form::text('month_year', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.month_year'),
                                    'required',
                                    'readonly',
                                ]) !!}
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('essentials::lang.proceed')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>

                    {!! Form::close() !!}

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

        <div class="modal fade" id="add_allowance_deduction_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel"></div>
    </section>
    <!-- /.content -->
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {


            $(document).on('change',
                '#user_id_filter, #month_year_filter, #department_id, #designation_id, #location_id_filter',
                function() {
                    payrolls_table.ajax.reload();
                });

            if ($('#add_payroll_step1').length) {
                $('#add_payroll_step1').validate();
                $('#employee_id').select2({
                    dropdownParent: $('#payroll_modal')
                });
            }

            $('div.view_modal').on('shown.bs.modal', function(e) {
                __currency_convert_recursively($('.view_modal'));
            });

            $('#month_year, #month_year_filter').datepicker({
                autoclose: true,
                format: 'mm/yyyy',
                minViewMode: "months"
            });

            //pay components

            $('#add_allowance_deduction_modal').on('shown.bs.modal', function(e) {
                var $p = $(this);
                $('#add_allowance_deduction_modal .select2').select2({
                    dropdownParent: $p
                });
                $('#add_allowance_deduction_modal #applicable_date').datepicker();

            });

            $(document).on('submit', 'form#add_allowance_form', function(e) {
                e.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                var data = $(this).serialize();

                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div#add_allowance_deduction_modal').modal('hide');
                            toastr.success(result.msg);
                            ad_pc_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

            ad_pc_table = $('#ad_pc_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'index']) }}",
                columns: [{
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'applicable_date',
                        name: 'applicable_date'
                    },
                    {
                        data: 'employees',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#ad_pc_table'));
                },
            });

            $(document).on('click', '.delete-allowance', function(e) {
                e.preventDefault();
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
                                    ad_pc_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });



            $(document).on('click', '.delete-payroll', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    payroll_group_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });

            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

            payrolls_table = $('#payrolls_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('agentTimeSheet.payrolls') }}",
                },
                columnDefs: [{

                    orderable: false,
                    searchable: false,
                }, ],
                aaSorting: [
                    [4, 'desc']
                ],
                columns: [{
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
            });


            //payroll groups

            payroll_group_table = $('#payroll_group_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('agentTimeSheet.payrollGroupDatatable') }}",
                aaSorting: [
                    [6, 'desc']
                ],
                columns: [{
                        data: 'name',
                        name: 'essentials_payroll_groups.name'
                    },
                    {
                        data: 'status',
                        name: 'essentials_payroll_groups.status'
                    },
                    {
                        data: 'payment_status',
                        name: 'essentials_payroll_groups.payment_status'
                    },
                    {
                        data: 'gross_total',
                        name: 'essentials_payroll_groups.gross_total'
                    },
                    {
                        data: 'added_by',
                        name: 'added_by'
                    },
                    {
                        data: 'created_at',
                        name: 'essentials_payroll_groups.created_at',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    }
                ]
            });
            $(document).on('change', '#projects', function() {
                let project_id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('agentTimeSheet.getWorkersBasedOnProject') }}",
                    dataType: 'json',
                    data: {
                        'project_id': project_id
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('#employee_ids').empty();
                            $.each(result.workers, function(id, worker) {
                                $('#employee_ids').append($('<option>', {
                                    value: id,
                                    text: worker
                                }));
                            });
                            $('#employee_ids').select2();
                        }
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
