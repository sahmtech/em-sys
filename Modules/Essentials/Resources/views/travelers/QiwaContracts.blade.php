@extends('layouts.app')
@section('title', __('essentials::lang.employee_contracts'))

@section('content')
    <section class="content-header">
        <h1>@lang('essentials::lang.employee_contracts')</h1>
    </section>
    <section class="content">
        @include('essentials::layouts.nav_trevelers')


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addEmployeesContractModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employees_contracts_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('essentials::lang.contract_number')</th>
                                    <th>@lang('essentials::lang.contract_start_date')</th>
                                    <th>@lang('essentials::lang.contract_end_date')</th>
                                    <th>@lang('essentials::lang.contract_duration')</th>
                                    <th>@lang('essentials::lang.probation_period')</th>
                                    <th>@lang('essentials::lang.contract_type')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                    <th>@lang('essentials::lang.is_renewable')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="addEmployeesContractModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeEmployeeContract', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_contract')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                    {!! Form::select('employee', $users, null, [
                                        'class' => 'form-control',
                                        'id' => 'employee_select',
                                        'style' => 'height:40px; width:100%',
                                        'placeholder' => __('essentials::lang.select_employee'),
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('contract_start_date', __('essentials::lang.contract_start_date') . ':') !!}
                                    {!! Form::date(
                                        'contract_start_date',
                                        !empty($contract->contract_start_date) ? $contract->contract_start_date : null,
                                        [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'contract_start_date',
                                            'placeholder' => __('essentials::lang.contract_start_date'),
                                        ],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('contract_duration', __('essentials::lang.contract_duration') . ':') !!}

                                    <div class="multi-input">
                                        <div class="input-group">
                                            {!! Form::number(
                                                'contract_duration',
                                                !empty($contract->contract_duration) ? $contract->contract_duration : null,
                                                [
                                                    'class' => 'form-control width-40 pull-left',
                                                    'style' => 'height:40px',
                                                    'id' => 'contract_duration',
                                                    'placeholder' => __('essentials::lang.contract_duration'),
                                                ],
                                            ) !!}
                                            {!! Form::select(
                                                'contract_duration_unit',
                                                ['years' => __('essentials::lang.years'), 'months' => __('essentials::lang.months')],
                                                !empty($contract->contract_per_period) ? $contract->contract_per_period : null,
                                                ['class' => 'form-control width-60 pull-left', 'style' => 'height:40px;', 'id' => 'contract_duration_unit'],
                                            ) !!}
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('contract_end_date', __('essentials::lang.contract_end_date') . ':') !!}
                                    {!! Form::date('contract_end_date', !empty($contract->contract_end_date) ? $contract->contract_end_date : null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'id' => 'contract_end_date',
                                        'placeholder' => __('essentials::lang.contract_end_date'),
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('probation_period', __('essentials::lang.probation_period') . ':*') !!}
                                    {!! Form::number('probation_period', null, [
                                        'class' => 'form-control  pull-left',
                                        'placeholder' => __('essentials::lang.probation_period_in_days'),
                                        'required',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
                                    {!! Form::select(
                                        'is_renewable',
                                        ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')],
                                        null,
                                        ['class' => 'form-control pull-left', 'style' => 'height:40px; width:100%'],
                                    ) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('contract_type', __('essentials::lang.contract_type') . ':*') !!}
                                    {!! Form::select('contract_type', $contract_types, !empty($user->location_id) ? $user->location_id : null, [
                                        'class' => 'form-control  pull-left ',
                                        'style' => 'height:40px; width:100%',
                                        'required',
                                        'placeholder' => __('messages.please_select'),
                                    ]) !!}
                                </div>


                                <div class="form-group col-md-6">
                                    {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                    {!! Form::file('file', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.file'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('javascript')

    <script>
        $(document).ready(function() {


            $('#addEmployeesContractModal').on('shown.bs.modal', function(e) {
                $('#employee_select').select2({
                    dropdownParent: $(
                        '#addEmployeesContractModal'),
                    width: '100%',
                });



            });


            function updateContractEndDate() {
                var startDate = $('#contract_start_date').val();
                var duration = $('#contract_duration').val();
                var unit = $('#contract_duration_unit').val();

                if (startDate && duration && unit) {
                    var newEndDate = calculateEndDate(startDate, duration, unit);
                    $('#contract_end_date').val(newEndDate);
                }
            }

            function calculateEndDate(startDate, duration, unit) {
                var startDateObj = new Date(startDate);
                var endDateObj = new Date(startDateObj);

                if (unit === 'years') {
                    endDateObj.setFullYear(startDateObj.getFullYear() + parseInt(duration));
                } else if (unit === 'months') {
                    endDateObj.setMonth(startDateObj.getMonth() + parseInt(duration));
                }

                return endDateObj.toISOString().split('T')[0]; // Format as YYYY-MM-DD
            }
        });
    </script>


    <script>
        $(document).ready(function() {

            $('#contract_start_date, #contract_end_date').change(function() {
                updateContractDuration();
            });


            $('#contract_duration, #contract_duration_unit').change(function() {
                updateEndDate();
            });

            function updateContractDuration() {
                var startDate = $('#contract_start_date').val();
                var endDate = $('#contract_end_date').val();

                if (startDate && endDate) {
                    var durationObj = calculateDuration(startDate, endDate);
                    $('#contract_duration').val(durationObj.value);
                    $('#contract_duration_unit').val(durationObj.unit);
                }
            }

            function updateEndDate() {
                var startDate = $('#contract_start_date').val();
                var duration = $('#contract_duration').val();
                var unit = $('#contract_duration_unit').val();

                if (startDate && !duration && !unit) {
                    var endDate = $('#contract_end_date').val();
                    if (endDate) {
                        var durationObj = calculateDuration(startDate, endDate);
                        $('#contract_duration').val(durationObj.value);
                        $('#contract_duration_unit').val(durationObj.unit);
                    }
                } else if (startDate && duration && unit) {
                    var newEndDate = calculateEndDate(startDate, duration, unit);
                    $('#contract_end_date').val(newEndDate);
                }
            }

            function calculateDuration(startDate, endDate) {
                var startDateObj = new Date(startDate);
                var endDateObj = new Date(endDate);
                var diffInMonths = (endDateObj.getFullYear() - startDateObj.getFullYear()) * 12 + endDateObj
                    .getMonth() - startDateObj.getMonth();
                var diffInYears = diffInMonths / 12;

                if (Number.isInteger(diffInYears)) {
                    return {
                        value: diffInYears,
                        unit: 'years'
                    };
                } else {
                    return {
                        value: diffInMonths,
                        unit: 'months'
                    };
                }
            }

            function calculateEndDate(startDate, duration, unit) {
                var startDateObj = new Date(startDate);
                var endDateObj = new Date(startDateObj);

                if (unit === 'years') {
                    endDateObj.setFullYear(startDateObj.getFullYear() + parseInt(duration));
                } else if (unit === 'months') {
                    endDateObj.setMonth(startDateObj.getMonth() + parseInt(duration));
                }

                return endDateObj.toISOString().split('T')[0]; // Format as YYYY-MM-DD
            }
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function() {
            var employees_contracts_table;

            function reloadDataTable() {
                employees_contracts_table.ajax.reload();
            }

            employees_contracts_table = $('#employees_contracts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('hrm_QiwaContract') }}",

                },

                columns: [{
                        data: 'user'
                    },
                    {
                        data: 'contract_number'
                    },
                    {
                        data: 'contract_start_date'
                    },
                    {
                        data: 'contract_end_date'
                    },
                    {
                        data: 'contract_duration',
                        render: function(data, type, row) {
                            var unit = row.contract_per_period;
                            if (data !== null && data !== undefined) {
                                var translatedUnit = (unit === 'years') ? '@lang('sales::lang.years')' :
                                    '@lang('sales::lang.months')';
                                return data + ' ' + translatedUnit;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        "data": 'probation_period',
                        "render": function(data, type, row) {
                            return data;

                        }
                    },

                    {
                        data: 'contract_type_id'
                    },

                    {
                        data: 'is_active',
                        render: function(data, type, row) {
                            if (data === 1) {
                                return '@lang('essentials::lang.valid')';
                            } else if (data === 0) {
                                return '@lang('essentials::lang.canceled')';;
                            } else {
                                return " ";
                            }
                        }
                    },

                    {
                        data: 'is_renewable',
                        render: function(data, type, row) {
                            if (data === 1) {
                                return '@lang('essentials::lang.is_renewable')';
                            } else if (data === 0) {
                                return '@lang('essentials::lang.is_unrenewable')';
                            } else {
                                return " ";
                            }
                        }
                    },
                    {
                        data: 'action'
                    },
                ],
            });

            function addDateFiltersToRequest(d) {
                var start_date = $('#start_date_filter').val();
                var end_date = $('#end_date_filter').val();

                if (start_date) {
                    d.start_date = start_date;
                }

                if (end_date) {
                    d.end_date = end_date;
                }
            }



            $(document).on('click', 'button.delete_employeeContract_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_employeeContract,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    employees_contracts_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });


        });
    </script>
@endsection
