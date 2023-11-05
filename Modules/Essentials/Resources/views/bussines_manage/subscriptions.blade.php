@extends('layouts.app')
@section('title', __('essentials::lang.subscriptions'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.subscriptions')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('business.create')
            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addBusinessSubscriptionsModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
        
        @endcan
       
        @can('business.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="business_subscriptions_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.subscription_type')</th>
                            <th>@lang('essentials::lang.subscription_number')</th>
                            <th>@lang('essentials::lang.subscription_date')</th>                           
                            <th>@lang('essentials::lang.renew_date')</th>
                            <th>@lang('essentials::lang.expiration_date')</th>
                      
                            <th>@lang('essentials::lang.action')</th>

                            

            
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade business_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addBusinessSubscriptionsModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeBusinessSubscription','method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('essentials::lang.add_subscriptions')</h4>
                </div>
    
                <div class="modal-body">

                    <div class="row">
                        <input type="hidden" name="business_id" value="{{ $business_id }}">
                        <div class="form-group col-md-6">
                            {!! Form::label('subscription_type', __('essentials::lang.subscription_type') . ':*') !!}
                            {!! Form::select('subscription_type', [
                                'Muqeem' => __('essentials::lang.Muqeem'),
                                'Qiwa' => __('essentials::lang.Qiwa'),
                                'Tamm' =>__('essentials::lang.Tamm')

                            ], null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_subscription_type'), 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('subscription_number', __('essentials::lang.subscription_number') . ':*') !!}
                            {!! Form::text('subscription_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.subscription_number'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('subscription_date', __('essentials::lang.subscription_date') . ':*') !!}
                            {!! Form::date('subscription_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.subscription_date'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('renew_date', __('essentials::lang.renew_date') . ':*') !!}
                            {!! Form::date('renew_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.renew_date'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                            {!! Form::date('expiration_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.expiration_date'), 'requires']) !!}
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
        var id = "{{ $business_id }}";
        var business_subscriptions_table = $('#business_subscriptions_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business_subscriptions.view', ['id' => ':id']) }}".replace(':id', id),
                type: 'GET',
            },
             
            columns: [
          
                {
                            data: 'subscription_type',
                            render: function (data, type, row) {
                                if (data === 'Muqeem'){
                                    return  '@lang('essentials::lang.Muqeem')';
                                }
                                else if (data === 'Qiwa'){
                                    return  '@lang('essentials::lang.Qiwa')';
                                }
                                else if (data === 'Tamm'){
                                    return  '@lang('essentials::lang.Tamm')';
                                }
                             
                                else {
                                    return  data;
                                }
                            }
                        },
                { data: 'subscription_number' },
                { data: 'subscription_date' },
                { data: 'renew_date' },
                { data: 'expiration_date' },
          
                { data: 'action' },

             
            ]
        });
        $(document).on('click', 'button.delete_subscription_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_doc,
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
                                business_subscriptions_table.ajax.reload();
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
