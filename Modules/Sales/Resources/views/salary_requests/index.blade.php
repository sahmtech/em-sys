@extends('layouts.app')
@section('title', __('sales::lang.salary_requests'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.salary_requests')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">

                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.add_sales_salary_request'))
                        @slot('tool')
                            <div class="box-tools">

                                <button type="button" class="btn btn-block btn-primary" data-toggle="modal"
                                    data-target="#addItemModal">
                                    <i class="fa fa-plus"></i> @lang('messages.add')
                                </button>
                            </div>
                        @endslot
                    @endif


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="salary_request_table">
                            <thead>
                                <tr>
                                    <th>@lang('sales::lang.profession')</th>
                                    <th>@lang('sales::lang.specialization')</th>
                                    <th>@lang('sales::lang.nationality')</th>
                                    <th>@lang('sales::lang.quantity')</th>
                                    <th>@lang('sales::lang.note')</th>
                                    <th>@lang('sales::lang.file')</th>
                                    <th>@lang('sales::lang.answered_salary')</th>

                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @endcomponent

        @include('sales::salary_requests.create_modal')

        {{-- @include('sales::salary_requests.edit_modal') --}}

    </section>
    <!-- /.content -->

@endsection
@section('javascript')

    <script type="text/javascript">
        function reload() {
            $('#salary_request_table').DataTable().ajax.reload();

        }
        $(document).ready(function() {

            var salary_request_table = $('#salary_request_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('salary-requests-index') }}',
                columns: [{
                        data: 'profession_id'
                    },
                    {
                        data: 'specialization_id'
                    },
                    {
                        data: 'nationality_id'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'note'
                    },

                    {
                        data: 'file'
                    },
                    {
                        data: 'salary'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });



            $(document).on('click', 'button.delete_salary_request_button', function() {
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
                                    reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

        });


        // $('body').on('click', '.open-edit-modal', function() {
        //     var salaryId = $(this).data('id');
        //     $('#salaryrequestIdInput').val(salaryId);

        //     var editUrl = '{{ route('edit_salay_request', ':salaryId') }}'
        //     editUrl = editUrl.replace(':salaryId', salaryId);
        //     console.log(editUrl);

        //     $.ajax({
        //         url: editUrl,
        //         type: 'GET',
        //         dataType: 'json',
        //         success: function(response) {
        //             var data = response.data;
        //             if (response.data.file) {
        //                 $('#existingFile').val(response.data.file);


        //                 var openFileButton = '<a href="/uploads/' + response.data.file +
        //                     '" target="_blank" class="btn btn-primary">' +
        //                     '{{ __('sales::lang.openfile') }}' + '</a>';
        //                 $('#openFileContainer').html(openFileButton);
        //                 $('#openFileContainer').show();
        //             } else {
        //                 $('#existingFile').val('');
        //                 $('#openFileContainer').hide();
        //             }

        //             console.log(data);
        //             $('#editsalaryRequestModal select[name="workers"]').val(data.worker_id).trigger(
        //                 'change');
        //             $('#editsalaryRequestModal input[name="salary"]').val(data.salary).trigger(
        //                 'change');
        //             $('#editsalaryRequestModal input[name="arrival_period"]').val(data.arrival_period)
        //                 .trigger('change');
        //             $('#editsalaryRequestModal input[name="recruitment_fees"]').val(data
        //                 .recruitment_fees);
        //             $('#editsalaryRequestModal select[name="nationality"]').val(data.nationality)
        //                 .trigger('change');
        //             $('#editsalaryRequestModal select[name="profession"]').val(data.profession).trigger(
        //                 'change');



        //             $('#editsalaryRequestModal').modal('show');
        //         },
        //         error: function(error) {
        //             console.error('Error fetching user data:', error);
        //         }
        //     });
        // });

        $('body').on('submit', '#editsalaryRequestModal form', function(e) {
            e.preventDefault();

            var salaryId = $('#salaryrequestIdInput').val();
            console.log(salaryId);

            var urlWithId = '{{ route('salay_request.update', ':salaryId') }}';
            urlWithId = urlWithId.replace(':salaryId', salaryId);
            console.log(urlWithId);

            $.ajax({
                url: urlWithId,
                type: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        console.log(response);
                        toastr.success(response.msg, 'Success');
                        $('#editsalaryRequestModal').modal('hide');
                    } else {
                        toastr.error(response.msg);
                        console.log(response);
                    }
                },
                error: function(error) {
                    console.error('Error submitting form:', error);

                    toastr.error('An error occurred while submitting the form.', 'Error');
                },
            });
        });


        $('#editsalaryRequestModal').on('hidden.bs.modal', function() {
            reload();
        });
    </script>

    <script>
        $(document).on('shown.bs.modal', '#addItemModal', function() {
            $(this).find('#workers_select').select2({
                dropdownParent: $('#addItemModal')
            });
            $(this).find('#nationality_select').select2({
                dropdownParent: $('#addItemModal')
            });

            init_tinymce('description');
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#workers_select').on('change', function() {
                var workerId = $(this).val();
                $('#worker_id').val(workerId);
                console.log(workerId);

                $.ajax({
                    url: '{{ route('fetch-worker-details', ['workerId' => ':workerId']) }}'.replace(
                        ':workerId', workerId),
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#nationality_select').empty();
                        $('#profession_select').empty();
                        console.log(data);


                        $.each(data.nationalities, function(id, text) {
                            $('#nationality_select').append($('<option>', {
                                value: id,
                                text: text
                            }));
                        });

                        $.each(data.professions, function(id, text) {
                            $('#profession_select').append($('<option>', {
                                value: id,
                                text: text
                            }));
                        });



                        $('#nationality_select').trigger('change');
                        $('#profession_select').trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>






@endsection
