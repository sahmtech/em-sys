@extends('layouts.app')
@section('title', __('sales::lang.sale_sources'))
@section('content')
<section class="content-header">
    <h1>@lang('sales::lang.sale_sources')</h1>
</section>

<section class="content">
   

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addsourceModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="sources_table">
                        <thead>
                            <tr>
                            <th>@lang('sales::lang.id' )</th>
                                <th>@lang('sales::lang.source' )</th>
                                <th>@lang('sales::lang.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>

        <div class="modal fade" id="addsourceModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'store_source' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('sales::lang.add_source')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('source', __('sales::lang.source') . ':*') !!}
                                {!! Form::text('source', null,
                                     ['class' => 'form-control',
                                     
                                       'required']) !!}
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



<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => 'source.update', 'enctype' => 'multipart/form-data']) !!}
            <div class="modal-header">
                <h4 class="modal-title">@lang('sales::lang.edit_source')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('source', __('sales::lang.source') . ':*') !!}
                        {!! Form::text('source2', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                 
                    {!! Form::hidden('source_id', null, ['id' => 'editItemId']) !!}
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

    </div>
</section>
@endsection
@section('javascript')
<script type="text/javascript">
 
 $(document).ready(function () {
        var sources_table = $('#sources_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("sales_sources") }}', 
            columns: [
                { data: 'id'},
                { data: 'source'},
               
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
                            sources_table.ajax.reload();
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
<script>
    $(document).on('click', '.edit-item', function () {
        var itemId = $(this).data('id');
        var sourceValue = $(this).data('orig-value');
        console.log(sourceValue);
        $('#editModal').find('[name="source2"]').val(sourceValue);
        $('#editModal').find('[name="source_id"]').val(itemId);
        $('#editModal').modal('show');
    });
</script>
@endsection