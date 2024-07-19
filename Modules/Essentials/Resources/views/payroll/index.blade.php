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
                        <button type="button" class="btn btn-primary " data-toggle="modal" data-target="#addPayrollModal">
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

    <!-- Add Payroll Modal -->
    <div class="modal fade" id="addPayrollModal" tabindex="-1" role="dialog" aria-labelledby="addPayrollModalLabel">
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
                        {{-- <div class="form-group" id="projects_container" style="display: none;">
                            {!! Form::label('project_name', __('request.project_name') . ':*') !!}
                            {!! Form::select('project_name', $projects, null, [
                                'class' => 'form-control select2',
                                'style' => 'height: 40px',
                                'multiple' => 'multiple',
                                'name' => 'projects[]',
                                'placeholder' => __('request.select_project'),
                            ]) !!}
                        </div>
                        <div class="form-group" id="companies_container" style="display: none;">
                            {!! Form::label('companies', __('essentials::lang.companies') . ':*') !!}
                            {!! Form::select('companies', $companies, null, [
                                'class' => 'form-control select2',
                                'style' => 'height: 40px',
                                'multiple' => 'multiple',
                                'name' => 'companies[]',
                                'placeholder' => __('essentials::lang.companies'),
                            ]) !!}
                        </div> --}}

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
    </div>

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            // Initially hide both containers
            $('#projects_container').hide();
            $('#companies_container').hide();

            // Function to toggle visibility of project and company inputs
            function toggleProjectsAndCompanies() {
                var selectedUserType = $('#user_type').val();

                if (selectedUserType === 'worker') {
                    $('#projects_container').show();
                    $('#companies_container').hide();
                } else if (selectedUserType === 'employee') {
                    $('#projects_container').hide();
                    $('#companies_container').show();
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
