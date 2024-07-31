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
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.create_payroll'))
                        <button type="button" class="btn btn-primary " data-toggle="modal" data-target="#payroll_modal">
                            <i class="fa fa-plus"></i>
                            @lang('messages.add')
                        </button>
                    @endif
                </div>
                <br><br><br>
                <div class="col-md-12">

                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#payrolls_groups_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-coins" aria-hidden="true"></i>
                                @lang('agent.payroll_groups')
                            </a>
                        </li>
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
                    </div>
                </div>
            </div>
        @endcomponent
    </section>
    <!-- /.content -->

    <div class="modal fade" id="payroll_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                {!! Form::open([
                    'url' => route('payrolls.create'),
                    'method' => 'get',
                    'id' => 'add_payroll_step1',
                ]) !!}
                <div class="modal-header">
                    <div class="col-md-12">
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
                                <button type="button" class="btn btn-primary btn-xs select-all">@lang('lang_v1.select_all')</button>
                                <button type="button"
                                    class="btn btn-primary btn-xs deselect-all">@lang('lang_v1.deselect_all')</button>
                            </div>
                        </div>
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
                        <div class="col-md-12" id="department_container">
                            <div class="form-group">

                                {!! Form::label('departments', __('essentials::lang.department') . ':') !!}

                                {!! Form::select('departments[]', $departments, null, [
                                    'class' => 'form-control select2',
                                    'id' => 'select_department_id',
                                    'style' => 'height:40px; width:100%',
                                    'multiple',
                                ]) !!}
                                <button type="button" class="btn btn-primary btn-xs select-all">@lang('lang_v1.select_all')</button>
                                <button type="button"
                                    class="btn btn-primary btn-xs deselect-all">@lang('lang_v1.deselect_all')</button>
                            </div>
                        </div>
                        <div class="col-md-12 ">
                            <div class="form-group" id="projects_container">
                                {!! Form::label('projects', __('essentials::lang.sales_projects') . ':*') !!}

                                {!! Form::select('projects[]', $projects, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%;',
                                    'id' => 'projects',
                                    'multiple',
                                    // 'placeholder' => __('lang_v1.all'),
                                ]) !!}
                                <button type="button" class="btn btn-primary btn-xs select-all">@lang('lang_v1.select_all')</button>
                                <button type="button"
                                    class="btn btn-primary btn-xs deselect-all">@lang('lang_v1.deselect_all')</button>
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
                    <button type="submit" class="btn btn-primary">@lang('essentials::lang.add_payroll')</button>
                </div>

                {!! Form::close() !!}

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>


    <!-- Add Payroll Modal -->
    {{-- <div class="modal fade" id="addPayrollModal" tabindex="-1" role="dialog" aria-labelledby="addPayrollModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="addPayrollModalLabel">@lang('essentials::lang.add_payroll')</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="addPayrollForm" action="{{ route('payrolls.create') }}" method="GET">
                            <div class="form-group">
                                <label for="user_type">@lang('essentials::lang.user_type')</label>
                                <select class="form-control" id="user_type" name="user_type">
                                    <option value="worker">@lang('essentials::lang.worker')</option>
                                    <option value="employee">@lang('essentials::lang.employee')</option>
                                </select>
                            </div>
                            <div class="form-group" id="projects_container">
                                <label for="projects">@lang('essentials::lang.project')</label>
                                <select class="form-control select2" id="projects" name="projects[]" multiple>
                                    @foreach ($projects as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" id="companies_container">
                                <label for="companies">@lang('essentials::lang.company')</label>
                                <select class="form-control select2" id="companies" name="companies[]" multiple>
                                    @foreach ($companies as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
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
                            <button type="submit" class="btn btn-primary">@lang('essentials::lang.add_payroll')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div> --}}

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            // Initially hide both containers
            $('#projects_container').hide();

            // Function to toggle visibility of project and company inputs
            function toggleProjectsAndCompanies() {
                var selectedUserType = $('#user_type').val();

                if (selectedUserType === 'worker') {
                    $('#projects_container').show();
                    $('#department_container').hide();
                } else if (selectedUserType === 'employee') {
                    $('#projects_container').hide();
                    $('#department_container').show();
                }
            }

            // Call function on page load and when user type changes
            toggleProjectsAndCompanies();
            $('#user_type').change(function() {
                toggleProjectsAndCompanies();
            });

            // Initialize datepicker for month input
            $('#month_year').datepicker({
                autoclose: true,
                format: 'mm/yyyy',
                minViewMode: "months"
            });

            // Initialize payroll group table
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
                        render: function(data) {
                            return data === 1 ? '@lang('lang_v1.issued')' : '@lang('lang_v1.is_not_issued')';
                        }
                    },
                    {
                        data: 'is_payrolls_issued',
                        render: function(data) {
                            return data === 1 ? '@lang('lang_v1.issued')' : '@lang('lang_v1.is_not_issued')';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });
        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
