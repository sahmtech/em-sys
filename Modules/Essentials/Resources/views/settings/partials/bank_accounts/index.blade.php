@extends('layouts.app')
@section('title', __('essentials::lang.bank_accounts'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.bank_accounts')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('bank.create')
                @slot('tool')
                <div class="box-tools">
            
                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addBankModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
        @endcan
        @can('bank.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="bank_accounts_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.name')</th>
                            <th>@lang('essentials::lang.phone_number')</th>                           
                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('essentials::lang.address')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade bank_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addBankModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeBank_account']) !!}
  
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">@lang( 'essentials::lang.add_bank_account' )</h4>
                </div>
              
                <div class="modal-body">
                  <div class="row">
                      <div class="form-group col-md-6">
                          {!! Form::label('name', __('essentials::lang.bank_name') . ':*') !!}
                          {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.bank_name'), 'required']) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('phone_number', __('essentials::lang.phone_number') . ':*') !!}
                          {!! Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.phone_number'), 'required']) !!}
                      </div>
                  
                      <div class="form-group col-md-6">
                          {!! Form::label('mobile_number', __('essentials::lang.mobile_number') . ':*') !!}
                          {!! Form::text('mobile_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.mobile_number'), 'required']) !!}
                      </div>
                      <div class="form-group col-md-6">
                          {!! Form::label('address', __('essentials::lang.address') . ':*') !!}
                          {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.address'), 'required']) !!}
                      </div>
                      <div class="form-group col-md-6">
                          {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                          {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
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
        var bank_accounts_table = $('#bank_accounts_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("bank_accounts") }}', 
            columns: [
                { data: 'name'},
                { data: 'phone_number'},
                { data: 'mobile_number'},
                { data: 'address'},
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

        $(document).on('click', 'button.delete_bank_account_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_bank,
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
                                bank_accounts_table.ajax.reload();
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
