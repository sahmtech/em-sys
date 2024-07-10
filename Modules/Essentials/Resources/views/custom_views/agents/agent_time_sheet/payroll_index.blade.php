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
                        @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_timesheet_payroll_groups'))
                            <li class="active">
                                <a href="#payrolls_groups_tab" data-toggle="tab" aria-expanded="true">
                                    <i class="fas fa-coins" aria-hidden="true"></i>
                                    @lang('agent.time_sheet_groups')
                                </a>
                            </li>
                        @endif

                    </ul>
                    <div class="tab-content">
                        <br><br>
                        <div class="tab-pane active" id="payrolls_groups_tab">

                            <br><br><br>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payroll_group_table"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('essentials::lang.name')</th>
                                                <th>@lang('essentials::lang.project')</th>
                                                <th>@lang('sale.status')</th>
                                                <th>@lang('essentials::lang.total')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                                <th>@lang('lang_v1.created_at')</th>
                                                <th>@lang('lang_v1.approved')</th>
                                                <th>@lang('lang_v1.approved_by')</th>
                                                <th>@lang('lang_v1.is_invoice_issued')</th>
                                                <th>@lang('lang_v1.is_payrolls_issued')</th>
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


                payroll_group_table = $('#payroll_group_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('payroll.agentTimeSheetGroups') }}",
                    columns: [{
                            data: 'name',
                            name: 'name'
                        },

                        {
                            data: 'project_id',
                            name: 'project_id'
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
                        },
                        {
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
                        }, {
                            data: 'approved_by',
                            name: 'approved_by'
                        }, {
                            data: 'is_invoice_issued',
                            render: function(data, type, row) {
                                if (data === 1) {
                                    return '@lang('lang_v1.issued')';
                                } else if (data === 0) {
                                    return '@lang('lang_v1.is_not_issued')';
                                } else {
                                    return " ";
                                }
                            }
                        },
                        {
                            data: 'is_payrolls_issued',
                            render: function(data, type, row) {
                                if (data === 1) {
                                    return '@lang('lang_v1.issued')';
                                } else if (data === 0) {
                                    return '@lang('lang_v1.is_not_issued')';
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


            });
        </script>
        <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>



    @endsection
