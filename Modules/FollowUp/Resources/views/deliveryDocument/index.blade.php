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
            {{-- <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                  
                @endcomponent
            </div> --}}
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


            $('#carTypeSelect').select2();
            $('#driver_select').select2();


            document_delivery_table = $('#document_delivery_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('documents-delivery') }}',
                    data: function(d) {
                        if ($('#carTypeSelect').val()) {
                            d.carTypeSelect = $('#carTypeSelect').val();
                            // console.log(d.project_name_filter);
                        }
                        if ($('#driver_select').val()) {
                            d.driver_select = $('#driver_select').val();
                            // console.log(d.project_name_filter);
                        }
                    }
                },
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": "worker",
                        render: function(data, type, row) {
                            var link = '<a href="' + '{{ route('showWorker', ['id' => ':id']) }}'
                                .replace(':id', row.worker_id) + '">' + data + '</a>';
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


            $('#carTypeSelect,#driver_select').on('change',
                function() {
                    carDrivers_table.ajax.reload();
                });

        });
    </script>
@endsection
