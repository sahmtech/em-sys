@extends('layouts.app')
@section('title', __('sales::lang.contract_appendics'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.contract_appendics')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('contract_filter', __('sales::lang.contract') . ':') !!}
                            {!! Form::select('contract_filter', $contracts, null, [
                                'class' => 'form-control',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
        @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("sales.add_contract_appendix"))
    
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addAppendixModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
        @endif
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contract_appendics_table">
                    <thead>
                        <tr>
                            <th>@lang('sales::lang.id')</th>
                            <th>@lang('sales::lang.number_of_appendix')</th>
                            <th>@lang('sales::lang.contract_number')</th>
                            <th>@lang('sales::lang.contract_item')</th>
                            <th>@lang('sales::lang.notes')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade appendix_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade" id="addAppendixModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeAppendix', 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('sales::lang.add_contract_appendix')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="form-group col-md-12">
                                {!! Form::label('contract', __('sales::lang.contract_number') . ':*') !!}
                                {!! Form::select('contract', $contracts, null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.contract_number'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('sales::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.notes'),
                                    'rows' => 2,
                                ]) !!}
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::label('file_contract_appendices', __('sales::lang.file_contract_appendices') . '*') !!}
                                    {!! Form::file('file_contract_appendices', ['class' => 'form-control', 'required', 'accept' => 'doc/*']) !!}


                                </div>


                                <div class="form-group col-md-6">
                                    <button type="button" class="btn btn-primary" id="addButton" data-toggle="modal"
                                        data-target="#addItemModal">
                                        <i class="fa fa-plus"></i> @lang('sales::lang.add_contract_item')
                                    </button>
                                </div>
                                <input type="hidden" name="appendixItemId" id="appendixItemId" value="">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog"
                    aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            {!! Form::open(['route' => 'storeAppindexItem', 'name' => 'addAppendixModalForm']) !!} <!-- Add the 'name' attribute to the form -->
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('sales::lang.add_contract_item')</h4>
                            </div>

                            <div class="modal-body">
                                <div class="row">

                                    <div class="form-group col-md-6">
                                        {!! Form::label('number_of_item', __('sales::lang.number_of_item') . ':*') !!}
                                        {!! Form::number('number_of_item', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.number_of_item'),
                                            'required',
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('name_of_item', __('sales::lang.name_of_item') . ':*') !!}
                                        {!! Form::text('name_of_item', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.name_of_item'),
                                            'required',
                                        ]) !!}
                                    </div>


                                    <div class="form-group col-md-12">
                                        {!! Form::label('details', __('sales::lang.details') . ':') !!}
                                        {!! Form::textarea('details', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.details'),
                                            'rows' => 2,
                                        ]) !!}
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default"
                                    data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var appointments_table;

            function reloadDataTable() {
                contract_appendics_table.ajax.reload();
            }
            var contract_appendics_table = $('#contract_appendics_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('contract_appendices') }}",
                    data: function(d) {


                        d.contract = $('#contract_filter').val();


                    }
                },

                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'number_of_appendix'
                    },
                    {
                        data: 'contract_id'
                    },
                    {
                        data: 'contract_item_id'
                    },
                    {
                        data: 'notes'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#contract_filter').on('change', function() {
                reloadDataTable();
            });
            $(document).on('click', 'button.delete_appendix_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_appendix,
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
                                    contract_appendics_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });


        });
        // Add an event listener to your form submit button
        $(document).on('submit', 'form[name="addAppendixModalForm"]', function(e) {
            e.preventDefault();

            var form = $(this);

            $.ajax({
                type: 'POST',


                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {

                        var appendixItemId = response.appendixItem;
                        $('#appendixItemId').val(appendixItemId);
                        $('#addButton').prop('disabled', true)
                        $('#addItemModal').modal('hide');;
                        form.trigger('reset');
                    }
                }
            });
        });
    </script>
@endsection
