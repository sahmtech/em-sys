@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')
    <section class="content-header">
        <h1> @lang('agent.time_sheet') </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.view_timesheet_groups'))
                            <li class="active">
                                <a href="#payrolls_groups_tab" data-toggle="tab" aria-expanded="true">
                                    <i class="fas fa-coins" aria-hidden="true"></i>
                                    @lang('agent.time_sheet')
                                </a>
                            </li>
                        @endif
                        {{-- @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.view_timesheet_users'))
                            <li>
                                <a href="#payrolls_tab" data-toggle="tab" aria-expanded="true">
                                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                                    @lang('agent.time_sheet')
                                </a>
                            </li>
                        @endif --}}
                    </ul>
                    <div class="tab-content">
                        <br><br>
                        <div class="tab-pane active" id="payrolls_groups_tab">
                            <div class="col-md-12">
                                @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.create_timesheet'))
                                    <button type="button" class="btn btn-primary " data-toggle="modal"
                                        data-target="#payroll_modal">
                                        <i class="fa fa-plus"></i>
                                        @lang('messages.add')
                                    </button>
                                @endif
                            </div>
                            <br><br><br>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payroll_group_table"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('essentials::lang.name')</th>
                                                <th>@lang('sale.payment_status')</th>
                                                <th>@lang('sale.status')</th>
                                                <th>@lang('essentials::lang.total')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                                <th>@lang('lang_v1.created_at')</th>
                                                <th>@lang('lang_v1.approved')</th>


                                                <th>@lang('messages.action')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.view_timesheet_users'))
                            <div class="tab-pane" id="payrolls_tab">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payrolls_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('essentials::lang.name')</th>
                                                    <th>@lang('essentials::lang.user')</th>
                                                    <th>@lang('essentials::lang.Identity_proof_name')</th>

                                                    <th>@lang('essentials::lang.final_salary')</th>
                                                    <th>@lang('sale.payment_status')</th>
                                                    <th>@lang('essentials::lang.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endcomponent

        <div class="modal fade" id="payroll_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open([
                        'url' => route('followup.agentTimeSheet.create'),
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
                            <button type="button" class="btn btn-primary btn-xs select-all">@lang('lang_v1.select_all')</button>
                            <button type="button" class="btn btn-primary btn-xs deselect-all">@lang('lang_v1.deselect_all')</button>
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

        <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewModalLabel">Time Sheet Users</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="timesheetUsersTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Total Amount</th>
                                    <th>Bank Name</th>
                                    <th>Branch</th>
                                    <th>IBAN</th>
                                    <th>Account Holder</th>
                                    <th>Account Number</th>
                                    <th>Tax Number</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('href');

                $('#viewModal').modal('show');
                $('#timesheetUsersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: url,
                    destroy: true, // To ensure the table is reinitialized each time
                    columns: [{
                            data: 'employee_name',
                            name: 'employee_name'
                        },
                        {
                            data: 'total_amount',
                            name: 'total_amount'
                        },
                        {
                            data: 'bank_name',
                            name: 'bank_name'
                        },
                        {
                            data: 'branch',
                            name: 'branch'
                        },
                        {
                            data: 'iban_number',
                            name: 'iban_number'
                        },
                        {
                            data: 'account_holder_name',
                            name: 'account_holder_name'
                        },
                        {
                            data: 'account_number',
                            name: 'account_number'
                        },
                        {
                            data: 'tax_number',
                            name: 'tax_number'
                        }
                    ]
                });
            });
        </script>
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

                payrolls_table = $('#payrolls_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('followup.agentTimeSheetUsers') }}",
                    },
                    columnDefs: [{
                        orderable: false,
                        searchable: false,
                    }],

                    columns: [{
                            data: 'name',
                            name: 'name'
                        }, {
                            data: 'user',
                            name: 'user'
                        },
                        {
                            data: 'id_proof_number',
                            name: 'id_proof_number'
                        },

                        {
                            data: 'final_salary',
                            name: 'final_salary'
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                });

                payroll_group_table = $('#payroll_group_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('followup.agentTimeSheetGroups') }}",
                    columns: [{
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'total',
                            name: 'total'
                        },
                        {
                            data: 'created_by',
                            name: 'created_by'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        }, {
                            data: 'is_approved',
                            render: function(data, type, row) {
                                if (data === 1) {
                                    return '@lang('lang_v1.is_approved')';
                                } else if (data === 0) {
                                    return '@lang('lang_v1.is_not_approved')';
                                } else {
                                    return " ";
                                }
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
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
        <script>
            $(document).on('click', '.btn-modal', function(e) {
                // Ensure it does not prevent the default action
                e.preventDefault();
                var url = $(this).attr('href');
                window.location.href = url;
            });
        </script>


    @endsection
