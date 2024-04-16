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
                        <li>
                            <a href="#payrolls_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-layer-group" aria-hidden="true"></i>
                                @lang('agent.payrolls')
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
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

        <div class="modal fade" id="payroll_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open([
                        'url' => route('payrolls.create'),
                        'method' => 'get',
                        'id' => 'add_payroll_step1',
                    ]) !!}

                    <div class="modal-body">
                        <div class="col-md-12">

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('user_type', __('essentials::lang.user_type') . ':*') !!}
                                    {!! Form::select('user_type', $user_types, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%;',
                                        'id' => 'user_type',
                                        'required',
                                        // 'placeholder' => __('lang_v1.all'),
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('projects', __('essentials::lang.sales_projects') . ':*') !!}
                                    {!! Form::select('projects[]', $projects, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%;',
                                        'id' => 'projects',
                                        
                                        'multiple',
                                        // 'placeholder' => __('lang_v1.all'),
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('companies', __('essentials::lang.company') . ':*') !!}
                                    {!! Form::select('companies[]', $companies, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%;',
                                        'id' => 'companies',
                                       
                                        'multiple',
                                        // 'placeholder' => __('lang_v1.all'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        {{-- <div class="form-group">
                            {!! Form::label('employee_ids', __('essentials::lang.employee') . ':*') !!}
                            <button type="button" class="btn btn-primary btn-xs select-all">
                                @lang('lang_v1.select_all')
                            </button>
                            <button type="button" class="btn btn-primary btn-xs deselect-all">
                                @lang('lang_v1.deselect_all')
                            </button>
                            {!! Form::select('employee_ids[]', $employees, null, [
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'width: 100%;',
                                'multiple',
                                'id' => 'employee_ids',
                            ]) !!}
                        </div> --}}


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


            $('#month_year, #month_year_filter').datepicker({
                autoclose: true,
                format: 'mm/yyyy',
                minViewMode: "months"
            });




            payrolls_table = $('#payrolls_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('payrolls.payrolls.index') }}",
                },
                columnDefs: [{

                    orderable: false,
                    searchable: false,
                }, ],

                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'user_type',
                        name: 'user_type'
                    },
                    {
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'project',
                        name: 'project'
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



            $(document).on('change', '#companies', function() {
                let company_id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('payrolls.getEmployeesBasedOnCompany') }}",
                    dataType: 'json',
                    data: {
                        'company_id': company_id
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('#employee_ids').empty();
                            console.log(result.employees);
                            $.each(result.employees, function(id, employee) {
                                $('#employee_ids').append($('<option>', {
                                    value: id,
                                    text: employee
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
