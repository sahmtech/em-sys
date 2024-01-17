@extends('layouts.app')
@section('title', __('essentials::lang.travel_categories'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.travel_categories')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
     
        @slot('tool')
        <div class="box-tools">
      
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addTravelCategorieModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
    @endslot
       
       
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="travel_categories_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.name')</th>
                            <th>@lang('essentials::lang.employee_ticket_value')</th>                           
                            <th>@lang('essentials::lang.wife_ticket_value')</th>
                            <th>@lang('essentials::lang.children_ticket_value')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
      
    @endcomponent
    <div class="modal fade" id="edit_manage_travel_categories" tabindex="-1" role="dialog">

    <div class="modal fade country_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addTravelCategorieModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeTravel_categorie']) !!}
  
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">@lang( 'essentials::lang.addTravel_categorie' )</h4>
                </div>
            
                <div class="modal-body">
                  <div class="row">
                      <div class="form-group col-md-6">
                          {!! Form::label('name', __('essentials::lang.name') . ':*') !!}
                          {!! Form::text('name',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('employee_ticket_value', __('essentials::lang.employee_ticket_value') . ':*') !!}
                          {!! Form::text('employee_ticket_value', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.employee_ticket_value'), 'required']) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('wife_ticket_value', __('essentials::lang.wife_ticket_value') . ':*') !!}
                          {!! Form::text('wife_ticket_value', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.wife_ticket_value'), 'required']) !!}
                      </div>
                      <div class="form-group col-md-6">
                        {!! Form::label('children_ticket_value', __('essentials::lang.children_ticket_value') . ':*') !!}
                        {!! Form::text('children_ticket_value',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.children_ticket_value'), 'required']) !!}
                    </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                          {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' =>2]) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
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
        var travel_categories_table = $('#travel_categories_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("travel_categories") }}', 
            columns: [
                { data: 'name'},
                { data: 'employee_ticket_value'},
                { data: 'wife_ticket_value'},
                { data: 'children_ticket_value'},
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

        $(document).on('click', 'button.delete_travel_categorie_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_travel_categorie,
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
                                travel_categories_table.ajax.reload();
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
