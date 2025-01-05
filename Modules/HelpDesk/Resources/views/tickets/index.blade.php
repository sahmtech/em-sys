@extends('layouts.app')
@section('title', __('helpdesk::lang.tickets'))

@section('content')
    <style>
        .badge-orange {
            background-color: #fd7e14;
            color: white;
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <span>@lang('helpdesk::lang.tickets')</span>
        </h1>
    </section>
    @if (session('status'))
        <script>
            var status = @json(session('status')); // Convert the session data into a JavaScript object

            if (status.success) {
                toastr.success(status.msg); // Show success message using Toastr
            } else {
                toastr.error(status.msg); // Show error message using Toastr (if success is false)
            }
        </script>
    @endif




    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addTicketModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tickets_table">
                            <thead>
                                <tr>
                                    {{-- <th>@lang('helpdesk::lang.ticket_number')</th> --}}
                                    <th style="white-space: nowrap; width: 2%;">@lang('helpdesk::lang.ticket_number')</th>
                                    <th>@lang('helpdesk::lang.title')</th>
                                    <th>@lang('helpdesk::lang.ticket_user_create')</th>
                                    <th>@lang('helpdesk::lang.ticket_department')</th>
                                    <th>@lang('helpdesk::lang.user_reply')</th>

                                    <th>@lang('helpdesk::lang.priority')</th>
                                    <th>@lang('helpdesk::lang.status')</th>
                                    <th>@lang('helpdesk::lang.ticket_created_at')</th>
                                    <th>@lang('helpdesk::lang.last_update_date')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="addTicketModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open([
                            'route' => 'tickets.store',
                            'enctype' => 'multipart/form-data',
                            'method' => 'post',
                        
                            'files' => true,
                        ]) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('helpdesk::lang.add_ticket')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('urgency', __('helpdesk::lang.urgency') . ':') !!}
                                    {!! Form::select('urgency', $urgencies, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'height:40px',
                                        'id' => 'urgency_select',
                                    ]) !!}
                                </div>

                                <div class="clearfix"></div>



                                <div class="form-group col-md-6">
                                    {!! Form::label('title', __('helpdesk::lang.title') . ':*') !!}
                                    {!! Form::text('title', null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('helpdesk::lang.title'),
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('attachments', __('helpdesk::lang.attachments') . ':') !!}
                                    {!! Form::file('attachments[]', [
                                        'class' => 'form-control',
                                        'placeholder' => __('helpdesk::lang.attachments'),
                                        'style' => 'height:40px',
                                        'id' => 'attachments',
                                        'multiple',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-12">
                                    {!! Form::label('message', __('helpdesk::lang.message') . ':*') !!}
                                    {!! Form::textarea('message', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('brand.note'),
                                        'rows' => 8,
                                        'required',
                                    ]) !!}
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
            $('#addTicketModal').on('shown.bs.modal', function(e) {
                $('#urgency_select').select2({
                    dropdownParent: $(
                        '#addTicketModal'),
                    width: '100%',
                });


            });
            var tickets_table;

            function reloadDataTable() {
                tickets_table.ajax.reload();
            }

            tickets_table = $('#tickets_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ route('tickets.index') }}",
                },

                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'user'
                    },

                    {
                        data: 'department'
                    },
                    {
                        data: 'user_reply'
                    },
                    {
                        data: 'priority'
                    },

                    {
                        data: 'status'
                    },

                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    },
                    {
                        data: 'action'
                    },
                ],
            });

            // $(document).on('click', 'button.delete_insurance_company_button', function() {
            //     swal({
            //         title: LANG.sure,
            //         text: LANG.confirm_delete_country,
            //         icon: "warning",
            //         buttons: true,
            //         dangerMode: true,
            //     }).then((willDelete) => {
            //         if (willDelete) {
            //             var href = $(this).data('href');
            //             $.ajax({
            //                 method: "DELETE",
            //                 url: href,
            //                 dataType: "json",
            //                 success: function(result) {
            //                     if (result.success == true) {
            //                         toastr.success(result.msg);
            //                         tickets_table.ajax.reload();
            //                     } else {
            //                         toastr.error(result.msg);
            //                     }
            //                 }
            //             });
            //         }
            //     });
            // });

        });
    </script>
@endsection
