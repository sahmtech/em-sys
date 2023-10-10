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
    @component('components.widget',['class' => 'box-primary'])
        @can('city.create')
        @slot('tool')
        <div class="box-tools">
            <!-- Button to trigger the Add New City modal -->
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addInsuranceContractModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
        @endslot
        @endcan
        @can('city.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="insurance_contracts_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.insurance_company')</th>
                            <th>@lang('essentials::lang.insurance_policy_number')</th>
                            <th>@lang('essentials::lang.insurance_policy_value')</th>
                            <th>@lang('essentials::lang.insurance_employees_count')</th>
                            <th>@lang('essentials::lang.insurance_dependents_count')</th>
                            <th>@lang('essentials::lang.insurance_start_date')</th>
                            <th>@lang('essentials::lang.insurance_end_date')</th>
                            <th>@lang('essentials::lang.insurance_attachments')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade insurance_contract_model" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addInsuranceContractModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'insurance_contracts.store','enctype' => 'multipart/form-data']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('essentials::lang.add_insurance_contract')</h4>
                </div>
    
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                            {!! Form::select('insurance_company', $insuramce_companies, null, ['class' => 'form-control select2',  'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('policy_number', __('essentials::lang.insurance_policy_number') . ':*') !!}
                            {!! Form::number('policy_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_policy_number'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('policy_value', __('essentials::lang.insurance_policy_value') . ':*') !!}
                            {!! Form::number('policy_value', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_policy_value'), 'required']) !!}
                        </div>

                        
                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_employees_count', __('essentials::lang.insurance_employees_count') . ':*') !!}
                            {!! Form::number('insurance_employees_count', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_employees_count'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_dependents_count', __('essentials::lang.insurance_dependents_count') . ':*') !!}
                            {!! Form::number('insurance_dependents_count', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_dependents_count'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_start_date', __('essentials::lang.insurance_start_date') . ':*') !!}
                            {!! Form::date('insurance_start_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_start_date'), 'required']) !!}
                        </div>

                  
                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_end_date', __('essentials::lang.insurance_end_date') . ':*') !!}
                            {!! Form::date('insurance_end_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_end_date'), 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_attachments', __('essentials::lang.insurance_attachments') . ':') !!}
                            {!! Form::file('insurance_attachments', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_attachments'), 'required']) !!}
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
    
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Cities table
    $(document).ready(function () {
        var insurance_contracts_table = $('#insurance_contracts_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("insurance_contracts") }}',
            columns: [
                { data: 'insurance_company_id' },
                { data: 'policy_number' },
                { data: 'policy_value' },
                { data: 'employees_count'},
                { data: 'dependents_count'},
                { data: 'insurance_start_date'},
                { data: 'insurance_end_date'},              
                { data: 'attachments' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', 'button.delete_city_button', function () {
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
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                cities_table.ajax.reload();
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
