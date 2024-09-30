@extends('layouts.app')
@section('title', __('essentials::lang.insurance_contracts'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.insurance_contracts')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('insurance_company_filter', __('essentials::lang.insurance_company') . ':') !!}
                            {!! Form::select('insurance_company_filter', $insuramce_companies, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('insurance_policy_number_filter', __('essentials::lang.insurance_policy_number') . ':') !!}
                            {!! Form::text('insurance_policy_number_filter', null, [
                                'class' => 'form-control',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('essentials::lang.insurance_end_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>


        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">
                    <!-- Button to trigger the Add New City modal -->
                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal"
                        data-target="#addInsuranceContractModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="insurance_contracts_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.insurance_company')</th>
                            <th>@lang('essentials::lang.company')</th>
                            <th>@lang('essentials::lang.insurance_policy_number')</th>
                            <th>@lang('essentials::lang.insurance_start_date')</th>
                            <th>@lang('essentials::lang.insurance_end_date')</th>
                            <th>@lang('essentials::lang.status')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent


        <div class="modal fade" id="addInsuranceContractModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'insurance_contracts.store', 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_insurance_contract')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                                {!! Form::select('insurance_company', $insuramce_companies, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%;padding:2px;',
                                    'placeholder' => __('essentials::lang.insurance_company'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('company_ids', __('essentials::lang.business') . ':') !!}
                                {!! Form::select('company_ids[]', $companies, null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'style' => 'width:100%;padding:2px;',
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('policy_number', __('essentials::lang.insurance_policy_number') . ':*') !!}
                                {!! Form::number('policy_number', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.insurance_policy_number'),
                                    'required',
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('insurance_start_date', __('essentials::lang.insurance_start_date') . ':*') !!}
                                {!! Form::date('insurance_start_date', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.insurance_start_date'),
                                    'required',
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('insurance_end_date', __('essentials::lang.insurance_end_date') . ':*') !!}
                                {!! Form::date('insurance_end_date', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.insurance_end_date'),
                                    'required',
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
        <div class="modal fade" id="editInsuranceContractModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => ['updateInsuranceContract', ''], 'method' => 'put', 'id' => 'edit_contract_form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('essentials::lang.edit_contract')</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('edit_insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                                {!! Form::select('insurance_company', $insuramce_companies, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%;padding:2px;',
                                    'id' => 'edit_insurance_company', // Add unique id
                                    'placeholder' => __('essentials::lang.insurance_company'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('edit_company_ids', __('essentials::lang.business') . ':') !!}
                                {!! Form::select('company_ids[]', $companies, null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'style' => 'width:100%;padding:2px;',
                                    'id' => 'edit_company_ids', // Add unique id
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('edit_policy_number', __('essentials::lang.insurance_policy_number') . ':*') !!}
                                {!! Form::number('policy_number', null, [
                                    'class' => 'form-control',
                                    'id' => 'edit_policy_number', // Add unique id
                                    'placeholder' => __('essentials::lang.insurance_policy_number'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('edit_insurance_start_date', __('essentials::lang.insurance_start_date') . ':*') !!}
                                {!! Form::date('insurance_start_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'edit_insurance_start_date', // Add unique id
                                    'placeholder' => __('essentials::lang.insurance_start_date'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('edit_insurance_end_date', __('essentials::lang.insurance_end_date') . ':*') !!}
                                {!! Form::date('insurance_end_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'edit_insurance_end_date', // Add unique id
                                    'placeholder' => __('essentials::lang.insurance_end_date'),
                                    'required',
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
        </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Handle the click event on the edit button
            $(document).on('click', '.edit_button', function() {
                var id = $(this).data('id');
                var insurance_company = $(this).data('insurance_company');
                var policy_number = $(this).data('policy_number');
                var start_date = $(this).data('start_date');
                var end_date = $(this).data('end_date');
                var companies = $(this).data('companies');

                // Populate the modal fields
                $('#edit_insurance_company').val(insurance_company).trigger('change');
                $('#edit_policy_number').val(policy_number);
                $('#edit_insurance_start_date').val(start_date);
                $('#edit_insurance_end_date').val(end_date);

                $('#edit_company_ids').val(null).trigger('change'); // Clear previous selections
                if (companies.length > 0) {
                    $('#edit_company_ids').val(companies).trigger('change'); // Set new selections
                }

                // Update the form action URL with the correct contract ID
                var formAction = "{{ route('updateInsuranceContract', '') }}/" + id;
                $('#edit_contract_form').attr('action', formAction);

                // Fetch company ids dynamically if needed (you can add more data fetching logic here)
            });
        });


        $(document).ready(function() {
            var insurance_contracts_table;

            function reloadDataTable() {
                insurance_contracts_table.ajax.reload();
            }
            insurance_contracts_table = $('#insurance_contracts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ route('insurance_contracts') }}",
                    "data": function(d) {
                        d.insurance_company_filter = $('#insurance_company_filter').val();
                        d.insurance_policy_number_filter = $('#insurance_policy_number_filter').val();
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columns: [{
                        data: 'insurance_company_id'
                    },
                    {
                        data: 'company'
                    },
                    {
                        data: 'policy_number'
                    },

                    {
                        data: 'insurance_start_date'
                    },
                    {
                        data: 'insurance_end_date'
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
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });
            $('#insurance_company_filter, #insurance_policy_number_filter, #doc_filter_date_range').on('change',
                function() {
                    reloadDataTable();
                });


            $(document).on('click', 'button.delete_insurance_contract_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_city,
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
                                    insurance_contracts_table.ajax.reload();
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
