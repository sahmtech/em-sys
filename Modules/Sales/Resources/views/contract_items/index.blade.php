@extends('layouts.app')
@section('title', __('sales::lang.contract_itmes'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('sales::lang.contract_itmes')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
     @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("sales.add_contract_item"))
            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addItemModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
            @endif
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contract_itmes_table">
                    <thead>
                        <tr>
                            <th>@lang('sales::lang.id')</th>
                            <th>@lang('sales::lang.number_of_item')</th>
                            <th>@lang('sales::lang.name_of_item')</th> 
                            <th>@lang('sales::lang.details')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent

    <div class="modal fade item_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
 <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => 'storeItem']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('sales::lang.add_contract_item')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    
                    <div class="form-group col-md-6">
                        {!! Form::label('number_of_item', __('sales::lang.number_of_item') . ':*') !!}
                        {!! Form::number('number_of_item', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.number_of_item'), 'required']) !!}
                    </div>
                    
                    <div class="form-group col-md-6">
                        {!! Form::label('name_of_item', __('sales::lang.name_of_item') . ':*') !!}
                        {!! Form::text('name_of_item', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.name_of_item'), 'required']) !!}
                    </div>


                    <div class="form-group col-md-12">
                        {!! Form::label('details', __('sales::lang.details') . ':') !!}
                        {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.details'), 'rows' => 2]) !!}
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
    // Countries table
    $(document).ready(function () {
        var contract_itmes_table = $('#contract_itmes_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("contract_itmes") }}', 
            columns: [
                { data: 'id'},
                { data: 'number_of_item'},
                { data: 'name_of_item'},
                { data: 'details' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
    });

    $(document).on('click', 'button.delete_item_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_item,
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
                                contract_itmes_table.ajax.reload();
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
