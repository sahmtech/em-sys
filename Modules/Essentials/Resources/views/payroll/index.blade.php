@extends('layouts.app')
@section('title', __('essentials::lang.payroll'))

@section('content')

    <section class="content-header">
        <h1> @lang('agent.payroll')
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
                                @lang('agent.payroll_groups')
                            </a>
                        </li>
                        {{-- <li>
                            <a href="#payrolls_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-layer-group" aria-hidden="true"></i>
                                @lang('agent.payrolls')
                            </a>
                        </li> --}}
                    </ul>
                    <div class="tab-content">
                        <br><br>
                        <div class="tab-pane active" id="payrolls_groups_tab">

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payroll_group_table"
                                        style="width: 100%;">
                                        <thead>

                                            <tr>
                                                <th>@lang('essentials::lang.name')</th>
                                                <th>@lang('essentials::lang.project')</th>
                                                <th>@lang('sale.payment_status')</th>
                                                <th>@lang('essentials::lang.total')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                                <th>@lang('lang_v1.created_at')</th>
                                                <th>@lang('lang_v1.accounting_approved_by')</th>
                                                <th>@lang('lang_v1.is_invoice_issued')</th>
                                                <th>@lang('lang_v1.is_payrolls_issued')</th>
                                                <th>@lang('messages.action')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="tab-pane" id="payrolls_tab">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payrolls_table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('essentials::lang.name')</th>
                                                <th>@lang('essentials::lang.user_type')</th>
                                                <th>@lang('essentials::lang.company')</th>
                                                <th>@lang('essentials::lang.project')</th>
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
                        </div> --}}
                    </div>
                </div>
            </div>
        @endcomponent


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
    <script>
        $(document).ready(function() {
            // Initially hide both to manage the correct display on load
            $('#projects').closest('.col-md-12').hide();
            $('#companies').closest('.col-md-12').hide();

            // Function to show/hide projects or companies based on the user type
            function toggleProjectsAndCompanies() {
                var selectedUserType = $('#user_type').val();

                if (selectedUserType === 'worker') {
                    // If the user is a worker, show projects and hide companies
                    $('#projects').closest('.col-md-12').show();
                    $('#projects').attr('required', 'required'); // Add required attribute to projects
                    $('#companies').closest('.col-md-12').hide();
                    $('#companies').removeAttr('required'); // Remove required attribute from companies
                } else {
                    // For any other user type, show companies and hide projects
                    $('#projects').closest('.col-md-12').hide();
                    $('#projects').removeAttr('required'); // Remove required attribute from projects
                    $('#companies').closest('.col-md-12').show();
                    $('#companies').attr('required', 'required'); // Add required attribute to companies
                }
            }

            // Call the function on page load in case there's a pre-selected value
            toggleProjectsAndCompanies();

            // Bind the function to the change event of the user_type select box
            $('#user_type').change(function() {
                toggleProjectsAndCompanies();
            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            //payroll groups

            payroll_group_table = $('#payroll_group_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('payrolls.payrollsGroup.index') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'project_id',
                        name: 'project_id'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
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
                        data: 'accounting_approved_by',
                        name: 'accounting_approved_by'
                    },
                    {
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



            $('#month_year, #month_year_filter').datepicker({
                autoclose: true,
                format: 'mm/yyyy',
                minViewMode: "months"
            });




            // payrolls_table = $('#payrolls_table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: {
            //         url: "{{ route('payrolls.payrolls.index') }}",
            //     },
            //     columnDefs: [{

            //         orderable: false,
            //         searchable: false,
            //     }, ],

            //     columns: [{
            //             data: 'name',
            //             name: 'name'
            //         },
            //         {
            //             data: 'user_type',
            //             name: 'user_type'
            //         },
            //         {
            //             data: 'company',
            //             name: 'company'
            //         },
            //         {
            //             data: 'project',
            //             name: 'project'
            //         },
            //         {
            //             data: 'transaction_date',
            //             name: 'transaction_date'
            //         },
            //         {
            //             data: 'ref_no',
            //             name: 'ref_no'
            //         },
            //         {
            //             data: 'final_total',
            //             name: 'final_total'
            //         },
            //         {
            //             data: 'payment_status',
            //             name: 'payment_status'
            //         },
            //         {
            //             data: 'action',
            //             name: 'action'
            //         },
            //     ],
            // });




        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
