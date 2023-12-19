@extends('layouts.app')
@section('title', __('followup::lang.document_delivery'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.document_delivery')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="row">
                        <div class="col-sm-4">
                            {!! Form::label('worker', __('followup::lang.worker')) !!}

                            <select class="form-control" name="worker_filtter" id='worker_filtter' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}">
                                        {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        <div class="col-sm-4" style="margin-top: 0px;">
                            {!! Form::label('documents', __('followup::lang.documents')) !!}

                            <select class="form-control" name="documents_filtter" id='documents_filtter' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($documents as $document)
                                    <option value="{{ $document->id }}">
                                        {{ $document->name_ar . ' - ' . $document->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endcomponent
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-primary'])
                        @slot('tool')
                            <div class="box-tools">
                                <a class="btn btn-primary pull-right m-5 btn-modal"
                                    href="{{ action('Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController@create') }}"
                                    data-href="{{ action('Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController@create') }}"
                                    data-container="#add_document_delivery_model">
                                    <i class="fas fa-plus"></i> @lang('messages.add')</a>
                            </div>
                        @endslot

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="document_delivery_table"
                                style="margin-bottom: 100px;">
                                <thead>
                                    <tr>

                                        <th>@lang('followup::lang.worker')</th>
                                        <th>@lang('followup::lang.documents')</th>
                                        <th>@lang('followup::lang.nots')</th>
                                        <th>@lang('messages.action')</th>
                                    </tr>
                                </thead>

                            </table>
                            {{-- <center class="mt-5">
                            {{ $Cars->links() }}
                        </center> --}}
                        </div>


                        <div class="modal fade" id="add_document_delivery_model" tabindex="-1" role="dialog"></div>
                        <div class="modal fade" id="edit_document_delivery_model" tabindex="-1" role="dialog">
                        </div>
                    @endcomponent
                </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {


            $('#documents_filtter').select2();
            $('#worker_filtter').select2();


            document_delivery_table = $('#document_delivery_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('documents-delivery') }}',
                    data: function(d) {
                        if ($('#worker_filtter').val()) {
                            d.worker_id = $('#worker_filtter').val();

                        }
                        if ($('#documents_filtter').val()) {
                            d.document_id = $('#documents_filtter').val();

                        }
                    }
                },
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": "worker",
                        render: function(data, type, row) {
                            var link = '<a href="' + '{{ route('showWorker', ['id' => ':id']) }}'
                                .replace(':id', row.user_id) + '">' + data + '</a>';
                            return link;
                        }
                    },
                    {
                        "data": "doc_name"
                    },
                    {
                        "data": "nots"
                    },
                    {
                        data: 'action'
                    }
                ]
            });
            $(document).on('click', 'button.delete_document_delivery_button', function() {

                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            document_delivery_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_document_delivery_button', function() {

                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            document_delivery_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $('#worker_filtter,#documents_filtter').on('change',
                function() {
                    document_delivery_table.ajax.reload();
                });

        });
    </script>
@endsection
