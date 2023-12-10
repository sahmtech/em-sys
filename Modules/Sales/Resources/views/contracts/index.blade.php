@extends('layouts.app')
@section('title', __('sales::lang.contracts'))

@section('content')

<section class="content-header">
    <h1>@lang('sales::lang.contracts')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
      
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status_filter', __('essentials::lang.status') . ':') !!}
                    <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="valid">@lang('sales::lang.valid')</option>
                        <option value="finished">@lang('sales::lang.finished')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('contract_form_filter', __('sales::lang.contract_form') . ':') !!}
                    <select class="form-control select2" name="contract_form_filter" required id="contract_form_filter" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="monthly_cost">@lang('sales::lang.monthly_cost')</option>
                        <option value="operating_fees">@lang('sales::lang.operating_fees')</option>
                    </select>
                </div>
            </div>
        @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addContractModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contracts_table">
                        <thead>
                            <tr>
                                <th>@lang('sales::lang.id' )</th>
                                <th>@lang('sales::lang.number_of_contract' )</th>
                                <th>@lang('sales::lang.customer_name' )</th>
                                <th>@lang('sales::lang.contract_status' )</th>
                                <th>@lang('sales::lang.start_date' )</th>
                                <th>@lang('sales::lang.end_date' )</th>
                                <th>@lang('sales::lang.contract_form' )</th>
                            
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addContractModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeContract' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_contract')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('offer_price', __('sales::lang.offer_price') . ':*') !!}
                                {!! Form::select('offer_price',$offer_prices, null, ['class' => 'form-control','id'=>'offer_price', 'placeholder' => __('sales::lang.select_offer_price'), 'required']) !!}
                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('contract_signer', __('sales::lang.contract_signer') . ':*') !!}
                                {!! Form::text('contract_signer', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.contract_signer'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('contract_follower', __('sales::lang.contract_follower') . ':*') !!}
                                {!! Form::text('contract_follower', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.contract_follower'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('start_date', __('essentials::lang.contract_start_date') . ':*') !!}
                                {!! Form::date('start_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contract_start_date'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('end_date', __('essentials::lang.contract_end_date') . ':*') !!}
                                {!! Form::date('end_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contract_end_date'), 'required']) !!}
                            </div>
                             <div class="form-group col-md-6">
                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                {!! Form::select('status', ['valid' => __('sales::lang.valid'), 'finished' => __('sales::lang.finished')] ,null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.status'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-8">
                                {!! Form::label('contract_items', __('sales::lang.contract_items') . ':*') !!}
                                {!! Form::select('contract_items[]', $items, null, [
                                    'class' => 'form-control select2', // Add the 'select2' class for styling
                                    'multiple' => 'multiple', // Enable multiselect
                                    'placeholder' => __('sales::lang.select_contract_items'),
                                    'required'
                                ]) !!}
                            </div>

                          
                            <div class="form-group col-md-6">
                                {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
                                {!! Form::select('is_renewable', ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')], null, ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                                {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('sales::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.notes'), 'rows' => 2]) !!}
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
    <script type="text/javascript">
        $(document).ready(function() {
            var contracts_table;

            function reloadDataTable() {
                contracts_table.ajax.reload();
            }

            contracts_table  = $('#contracts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('saleContracts') }}",
                    data: function(d) {
                        
                         d.status = $('#status_filter').val();
                         d.contract_form = $('#contract_form_filter').val();
                        
                     
                    }
                },
                

                columns: [
                        { data: 'id' },
                        { data: 'number_of_contract' },
                        { data: 'sales_project_id' },
                        
                        { data: 'status',render: function (data, type, row) {
                                if (data === 'valid') {
                                    return  '@lang('sales::lang.valid')';
                                    
                                } else {
                                    return  '@lang('sales::lang.finished')';
                                }
                            } 
                        },


                        { data: 'start_date' },
                        { data: 'end_date' },
                       
                        {
                            data: 'contract_form',
                            render: function (data, type, row) {
                                if (data === 'monthly_cost') {
                                    return  '@lang('sales::lang.monthly_cost')';
                                    
                                } else if (data === 'operating_fees') {
                                    return  '@lang('sales::lang.operating_fees')';
                                } else {
                                    return  ' ';
                                }
                            }
                        },
                     
                        { data: 'action' },
                    ],
             });

        
            
          
            $('#status_filter, #contract_form_filter').on('change', function() {
                reloadDataTable();
            });


            $(document).on('click', 'button.delete_contract_button', function () {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_contract,
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
                                    contracts_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
            $(document).ready(function () {
            $('#offer_price').on('change', function () {
                var offerPrice = $(this).val();
              
                $.ajax({
                    type: 'GET',
                    url: '/sale/getContractValues',
                    data: { 'offer_price': offerPrice },
                    success: function (data) {
                        $('#contract_follower').val(data.contract_follower.first_name + ' ' + data.contract_follower.last_name);
                        $('#contract_signer').val(data.contract_signer.first_name + ' ' + data.contract_signer.last_name)
                        $('#contract_follower').prop('disabled', true);
                        $('#contract_signer').prop('disabled', true);
                    }
                });
            });
        });

            
        });
    
    </script>
  
@endsection