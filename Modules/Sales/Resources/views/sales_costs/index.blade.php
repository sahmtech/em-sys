@extends('layouts.app')
@section('title', __('sales::lang.sales_costs'))
@section('content')
    <section class="content-header">
        <h1>@lang('sales::lang.sales_costs')</h1>
    </section>

    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addCostModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="sales_costs">
                            <thead>
                                <tr>
                                    <th>@lang('sales::lang.description')</th>
                                    <th>@lang('sales::lang.amount')</th>
                                    <th>@lang('sales::lang.duration_by_month')</th>
                                    <th>@lang('sales::lang.monthly_cost')</th>
                                    <th>@lang('sales::lang.action')</th>


                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            <div class="modal fade" id="addCostModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'store_cost', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('sales::lang.add_cost')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('description', __('sales::lang.description') . ':*') !!}
                                    {!! Form::text('description', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('amount', __('sales::lang.amount') . ':*') !!}
                                    {!! Form::number('amount', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('duration_by_month', __('sales::lang.duration_by_month') . ':*') !!}
                                    {!! Form::number('duration_by_month', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('monthly_cost', __('sales::lang.monthly_cost') . ':*') !!}
                                    {!! Form::number('monthly_cost', null, ['class' => 'form-control', 'required']) !!}
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



            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['route' => 'cost.update', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <h4 class="modal-title">@lang('sales::lang.edit_cost')</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('description', __('sales::lang.description') . ':*') !!}
                                        {!! Form::text('description2', null, ['class' => 'form-control', 'required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('amount', __('sales::lang.amount') . ':*') !!}
                                        {!! Form::number('amount2', null, ['class' => 'form-control', 'required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('duration_by_month', __('sales::lang.duration_by_month') . ':*') !!}
                                        {!! Form::number('duration_by_month2', null, ['class' => 'form-control', 'required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('monthly_cost', __('sales::lang.monthly_cost') . ':*') !!}
                                        {!! Form::number('monthly_cost2', null, ['class' => 'form-control', 'required']) !!}
                                    </div>
                                </div>

                                {!! Form::hidden('cost_id', null, ['id' => 'editItemId']) !!}
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
        $(document).ready(function() {
            var costs_table = $('#sales_costs').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('sales_costs') }}',
                columns: [
                    {
                        data: 'description'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'duration_by_month'
                    },
                    {
                        data: 'monthly_cost'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', 'button.delete_item_button', function() {
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
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    costs_table.ajax.reload();
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
        $(document).on('click', '.edit-item', function() {
            var itemId = $(this).data('id');
            var descriptionValue = $(this).data('description-value');
            var amountValue = $(this).data('amount-value');
            var duration_by_monthValue = $(this).data('duration_by_month-value');
            var monthly_costValue = $(this).data('monthly_cost-value');

           
            $('#editModal').find('[name="description2"]').val(descriptionValue);
            $('#editModal').find('[name="amount2"]').val(amountValue);
            $('#editModal').find('[name="duration_by_month2"]').val(duration_by_monthValue);
            $('#editModal').find('[name="monthly_cost2"]').val(monthly_costValue);

            $('#editModal').find('[name="cost_id"]').val(itemId);
            $('#editModal').modal('show');
        });
    </script>
@endsection
