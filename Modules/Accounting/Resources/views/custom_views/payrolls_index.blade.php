@extends('layouts.app')
@section('title', __('essentials::lang.payroll'))

@section('content')

    <section class="content-header">
        <h1> @lang('agent.payroll')
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="department_filter">@lang('essentials::lang.department'):</label>
                    {!! Form::select('select_department_id', $departments, null, [
                        'class' => 'form-control select2',
                        'id' => 'select_department_id',
                        'style' => 'height:40px; width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">


                <br><br><br>
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#payrolls_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-coins" aria-hidden="true"></i>
                                @lang('agent.payroll_groups')
                            </a>
                        </li>
                        <li>
                            <a href="#payrolls_groups_tab" data-toggle="tab" aria-expanded="false">
                                <i class="fas fa-coins" aria-hidden="true"></i>
                                @lang('essentials::lang.hrm_payrolls')
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <br><br>
                        <div class="tab-pane active" id="payrolls_tab">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payroll_table" style="width: 100%;">
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
                                                <th>@lang('essentials::lang.status')</th>
                                                <th>@lang('essentials::lang.action')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="payrolls_groups_tab">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payroll_group_table"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('essentials::lang.name')</th>
                                                <th>@lang('essentials::lang.eqama')</th>
                                                <th>@lang('essentials::lang.department')</th>
                                                <th>@lang('essentials::lang.company')</th>
                                                <th>@lang('essentials::lang.project')</th>
                                                <th>@lang('essentials::lang.date')</th>
                                                <th>@lang('essentials::lang.the_total')</th>
                                                <th>@lang('essentials::lang.status')</th>
                                                <th>@lang('messages.actions')</th>
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


        <!-- Create Payment Modal -->
        <div class="modal fade" id="createPaymentModal" tabindex="-1" role="dialog"
            aria-labelledby="createPaymentModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['url' => '', 'method' => 'post', 'id' => 'create_payment_form']) !!}
                    <div class="form-group col-md-12">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="createPaymentModalLabel">@lang('purchase.add_payment')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('payment_amount', __('purchase.amount') . ':*') !!}
                                        {!! Form::number('payment_amount', null, [
                                            'class' => 'form-control',
                                            'required',
                                            'readonly' => true,
                                            'id' => 'payment_amount',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('payment_date', __('lang_v1.date') . ':*') !!}
                                        {!! Form::date('payment_date', \Carbon\Carbon::now(), ['class' => 'form-control', 'required']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('payment_method', __('purchase.payment_method') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-money-bill-alt"></i>
                                            </span>
                                            {!! Form::select('payment_method', $payment_types, null, [
                                                'class' => 'form-control select2 payment_types input-sm',
                                                'id' => 'payment_method',
                                                'style' => 'width:100%;',
                                            
                                                'placeholder' => __('messages.please_select'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('attachments', __('assetmanagement::lang.attachments') . ':') !!}
                                        {!! Form::file('attachments[]', [
                                            'id' => 'attachments',
                                            'multiple',
                                            'class' => 'form-control',
                                        ]) !!}

                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::label('note', __('accounting::lang.additional_notes') . ':') !!}
                                    {!! Form::textarea('note', null, ['class' => 'form-control', 'rows' => 3]) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('lang_v1.pay')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>




        {{-- <div class="modal fade" id="createPaymentModel" tabindex="-1" role="dialog" aria-labelledby="editbuildingModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => ['updateBuilding', 'buildingId'], 'method' => 'post', 'id' => 'edit_building_form']) !!}

                    <input type="hidden" id="buildingIdInput" name="buildingIdInput">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.edit_building')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('name', __('housingmovements::lang.building_name') . ':*') !!}
                                {!! Form::text('name', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.building_name'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('address', __('housingmovements::lang.address') . ':') !!}
                                {!! Form::text('address', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.address'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('city', __('housingmovements::lang.city') . ':*') !!}
                                {!! Form::select('city', $cities, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'height:40px; width:100%',
                                    'placeholder' => __('housingmovements::lang.city'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                {!! Form::label('guard', __('housingmovements::lang.building_guard') . ':') !!}
                                {!! Form::select('guard[]', $users2, null, [
                                    'class' => 'form-control select2',
                                    'multiple' => 'multiple',
                                    'style' => 'height:40px; width:100%',
                                    'placeholder' => __('housingmovements::lang.building_guard'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('supervisor', __('housingmovements::lang.building_supervisor') . ':') !!}
                                {!! Form::select('supervisor[]', $users2, null, [
                                    'class' => 'form-control select2',
                                    'multiple' => 'multiple',
                                    'style' => 'height:40px; width:100%',
                                    'placeholder' => __('housingmovements::lang.building_supervisor'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('cleaner', __('housingmovements::lang.building_cleaner') . ':') !!}
                                {!! Form::select('cleaner[]', $users2, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'height:40px; width:100%',
                                    'multiple' => 'multiple',
                                    'placeholder' => __('housingmovements::lang.building_cleaner'),
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('building_end_date', __('housingmovements::lang.building_end_date') . ':') !!}
                                {!! Form::date('building_end_date', null, [
                                    'class' => 'form-control ',
                                    'style' => 'width:100%;height:36px;',
                                    'placeholder' => __('housingmovements::lang.building_end_date'),
                                ]) !!}
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>

        </div> --}}

    </section>
    <!-- /.content -->



    {{-- 
        $(document).ready(function() {
            $('#createPaymentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var payrollId = button.data('id'); // Extract info from data-* attributes
                var amount = button.data('amount'); // Extract amount

                // Update the form action with the correct payroll ID
                var actionUrl = "{{ route('accounting.payrolls.create_single_payment', ['id' => ':id']) }}";
                actionUrl = actionUrl.replace(':id', payrollId);
                $('#create_payment_form').attr('action', actionUrl);

                // Set the payment amount
                $('#payment_amount').val(amount);
            });
        });

--}}






@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('#createPaymentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var payrollId = button.data('id'); // Extract info from data-* attributes
                var amount = button.data('amount'); // Extract amount

                // Update the form action with the correct payroll ID
                var actionUrl = "{{ route('accounting.payrolls.create_single_payment', ['id' => ':id']) }}";
                actionUrl = actionUrl.replace(':id', payrollId);
                $('#create_payment_form').attr('action', actionUrl);

                // Set the payment amount
                $('#payment_amount').val(amount);
            });
        });




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


            payroll_group_table = $('#payroll_group_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('payrolls_list_index') }}",
                    data: function(d) {
                        d.status = $('#select_department_id').val();
                        d.select_department_id = $('#select_department_id').val();
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                    },

                    {
                        data: 'eqama',
                        name: 'eqama',
                    },
                    {
                        data: 'department',
                        name: 'department',
                    },
                    {
                        data: 'company',
                        name: 'company',
                    },

                    {
                        data: 'project',
                        name: 'project',
                    },
                    {
                        data: 'date',
                        name: 'date',
                    },

                    {
                        data: 'the_total',
                        name: 'the_total',
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });


            // Initialize payroll group table
            payroll_group_table = $('#payroll_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('hrm.payrolls_checkpoint', ['from' => 'accountant']) }}",
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
                        data: 'status',
                        name: 'status',
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
