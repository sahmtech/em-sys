@extends('layouts.app')
@section('title', __('essentials::lang.organizations'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_organizations')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
      
        @slot('tool')
        <div class="box-tools">
      
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addOrganizationModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
    @endslot
     
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="organizations_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.organization_name')</th>
                            <th>@lang('essentials::lang.organization_code')</th>                           
                            <th>@lang('essentials::lang.organization_level_type')</th>
                            <th>@lang('essentials::lang.organization_parent_level')</th>
                            <th>@lang('essentials::lang.organization_account_number')</th>
                            <th>@lang('essentials::lang.organization_bank_name')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
     
    @endcomponent
    <div class="modal fade organization_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addOrganizationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeOrganization']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('essentials::lang.add_organization')</h4>
                </div>
    
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('name', __('essentials::lang.organization_name') . ':*') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_name'), 'required']) !!}
                        </div>
                
                        <div class="form-group col-md-6">
                            {!! Form::label('code', __('essentials::lang.organization_code') . ':*') !!}
                            {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_code'), 'required']) !!}
                        </div>
                
                        <div class="form-group col-md-6">
                            {!! Form::label('level_type', __('essentials::lang.organization_level_type') . ':*') !!}
                            {!! Form::select('level_type', ['one_level' => __('essentials::lang.one_level'), 'other' => __('essentials::lang.other_level')], null, ['class' => 'form-control']) !!}
                        </div>
                
                        <div class="form-group col-md-6">
                            {!! Form::label('parent_level', __('essentials::lang.organization_parent_level') . ':*') !!}
                            {!! Form::text('parent_level', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_parent_level'), 'required']) !!}
                        </div>
                
                        <div class="form-group col-md-6">
                            {!! Form::label('account_number', __('essentials::lang.organization_account_number') . ':*') !!}
                            {!! Form::text('account_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_account_number'), 'required']) !!}
                        </div>
                
                        <div class="form-group col-md-6">
                            {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                            {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], null, ['class' => 'form-control']) !!}
                        </div>
                
                        <div class="form-group col-md-12">
                            {!! Form::label('bank', __('essentials::lang.organization_bank') . ':*') !!}
                            {!! Form::select('bank', $banks, null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.bank'), 'required']) !!}
                        </div>
                
                        <div class="form-group col-md-6">
                            {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                            {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
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
   
    $(document).ready(function () {
        var organizations_table = $('#organizations_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("organizations") }}', 
            columns: [
                { data: 'name'},
                { data: 'code'},
                { data: 'level_type'},
                { data: 'parent_level'},
                { data: 'account_number'},
                { data: 'bank_name'},
                { data: 'details' },
                { data: 'is_active' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', 'button.delete_organization_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_organization,
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
                                organizations_table.ajax.reload();
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
