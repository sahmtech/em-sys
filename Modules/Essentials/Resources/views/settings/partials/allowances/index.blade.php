@extends('layouts.app')
@section('title', __('essentials::lang.allowances'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.allowances')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('allowances_type.create')
        @slot('tool')
        <div class="box-tools">
      
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addAllowanceTypeModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
    @endslot
        @endcan
        @can('allowances_type.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="allowances_types_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.name')</th>
                            <th>@lang('essentials::lang.type')</th>                           
                            <th>@lang('essentials::lang.allowance_value')</th>
                            <th>@lang('essentials::lang.number_of_months')</th>
                            <th>@lang('essentials::lang.added_to_salary')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade allowance_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addAllowanceTypeModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeAllowance']) !!}

                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">@lang( 'essentials::lang.add_allowance' )</h4>
                </div>
            
            
                <div class="modal-body">
                  <div class="row">
                      <div class="form-group col-md-6">
                          {!! Form::label('name', __('essentials::lang.name') . ':*') !!}
                          {!! Form::text('name',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                          {!! Form::text('type',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.type'), 'required']) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('allowance_value', __('essentials::lang.allowance_value') . ':*') !!}
                          {!! Form::text('allowance_value',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.allowance_value'), 'required']) !!}
                      </div>
                      <div class="form-group col-md-6">
                        {!! Form::label('number_of_months', __('essentials::lang.number_of_months') .':*') !!}
                        {!! Form::text('number_of_months', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.number_of_months'), 'required']) !!}
                    </div>
                    <div class="form-group col-md-6">
                      {!! Form::label('added_to_salary', __('essentials::lang.added_to_salary') . ':*') !!}
                
                      {!! Form::select('added_to_salary', ['1' => __('essentials::lang.added_to_salary'), '0' => __('essentials::lang.not_added_to_salary')],null, ['class' => 'form-control']) !!}
                  </div>
                      <div class="form-group col-md-6">
                          {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                          {!! Form::textarea('details',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('is_active', __('essentials::lang.is_active') .':*') !!}
                          {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')],null, ['class' => 'form-control']) !!}
                      </div>
                  </div>
                  
                </div>
            
            
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
                  <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
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

    $(document).ready(function () {
        var allowances_types_table = $('#allowances_types_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("allowances") }}', 
            columns: [
                { data: 'name'},
                { data: 'type'},
                { data: 'allowance_value'},
                { data: 'number_of_months'},
                { data: 'added_to_salary'},
                { data: 'details' },
                { 
        data: 'is_active',
        render: function (data, type, row) {
            if (data === 1) {
                return '<span style="color: green;">Active</span>';
            } else {
                return '<span style="color: red;">Inactive</span>';
            }
        }
    },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', 'button.delete_allowance_type_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_allowance_type,
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
                                allowances_types_table.ajax.reload();
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
