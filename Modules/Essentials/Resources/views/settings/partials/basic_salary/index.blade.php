@extends('layouts.app')
@section('title', __('essentials::lang.basic_salary_types'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_basic_salary_types')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('basic_salary_type.create')
        @slot('tool')
        <div class="box-tools">
      
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addJBasicSalaryModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
    @endslot
        @endcan
        @can('basic_salary_type.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="basic_salary_types_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.basic_salary_type')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade basic_salary_type_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addJBasicSalaryModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeBasicSalary']) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_basic_salary_type' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
          
      <div class="modal-header">
        <div class="modal-body">
          <div class="row">
              <div class="form-group col-md-6">
                  {!! Form::label('type', __('essentials::lang.basic_salary_type') . ':*') !!}
                  {!! Form::text('type',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.basic_salary_type'), 'required']) !!}
              </div>
          
              <div class="form-group col-md-6">
                  {!! Form::label('details', __('essentials::lang.contry_details') . ':') !!}
                  {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' =>2]) !!}
              </div>
          
              <div class="form-group col-md-6">
                  {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                  {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], null, ['class' => 'form-control']) !!}
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
        var basic_salary_types_table = $('#basic_salary_types_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("basic_salary_types") }}', 
            columns: [
              
                { data: 'type'},
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

        $(document).on('click', 'button.delete_basic_salary_type_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_basic_salary_type,
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
                                basic_salary_types_table.ajax.reload();
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
