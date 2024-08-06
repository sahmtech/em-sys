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
                                                <th>@lang('essentials::lang.company')</th>
                                                <th>@lang('essentials::lang.project')</th>
                                                <th>@lang('essentials::lang.hr_management_cleared')</th>
                                                <th class="table-td-width-300px">@lang('essentials::lang.hr_management_cleared_by')</th>
                                                <th>@lang('essentials::lang.accountant_cleared')</th>
                                                <th class="table-td-width-300px">@lang('essentials::lang.accountant_cleared_by')</th>
                                                <th>@lang('essentials::lang.financial_management_cleared')</th>
                                                <th class="table-td-width-300px">@lang('essentials::lang.financial_management_cleared_by')</th>
                                                <th>@lang('essentials::lang.ceo_cleared')</th>
                                                <th class="table-td-width-300px">@lang('essentials::lang.ceo_cleared_by')</th>
                                                <th>@lang('essentials::lang.action')</th>
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










@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            // Initially hide both containers
            $('#projects_container').hide();


            if ($('#add_payroll_step1').length) {
                $('#add_payroll_step1').validate();
                $('#employee_id').select2({
                    dropdownParent: $('#payroll_modal')
                });
            }


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
                ajax: "{{ route('hrm.payrolls_checkpoint', ['from' => 'ceo']) }}",
                columns: [{
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'company',
                        name: 'company',
                    },
                    {
                        data: 'projects',
                        name: 'projects',
                    },

                    {
                        data: 'hr_management_cleared',
                        name: 'hr_management_cleared',
                    },
                    {
                        data: 'hr_management_cleared_by',
                        name: 'hr_management_cleared_by',
                    },
                    {
                        data: 'accountant_cleared',
                        name: 'accountant_cleared',
                    },

                    {
                        data: 'accountant_cleared_by',
                        name: 'accountant_cleared_by',
                    },
                    {
                        data: 'financial_management_cleared',
                        name: 'financial_management_cleared',
                    },

                    {
                        data: 'financial_management_cleared_by',
                        name: 'financial_management_cleared_by',
                    },
                    {
                        data: 'ceo_cleared',
                        name: 'ceo_cleared',
                    },

                    {
                        data: 'ceo_cleared_by',
                        name: 'ceo_cleared_by',
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
