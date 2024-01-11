@extends('layouts.app')
@section('title', __('helpdesk::lang.tickets'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <span>@lang('helpdesk::lang.tickets_of_number', ['number' => $ticket->ticket_number])</span>
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-3">
                    <div class="custom_ticket_container box box-primary">
                        <div class="col-md-12">
                            <h4> @lang('helpdesk::lang.ticket_info') </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-md-12">
                            <p>@lang('helpdesk::lang.requester'): {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}</p>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-10">
                            <hr>
                        </div>
                        <div class="col-md-12">
                            <p>@lang('helpdesk::lang.status'): {{ $ticket->hdTicketStatus->title }}</p>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-10">
                            <hr>
                        </div>
                        <div class="col-md-12">
                            <p>@lang('helpdesk::lang.urgency'): {{ $urgencies[$ticket->urgency] }}</p>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-10">
                            <hr>
                        </div>
                        <div class="col-md-12">
                            <p>@lang('helpdesk::lang.last_update_date'): {{ $ticket->last_update_date ?? $ticket->created_at }}</p>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-10">
                            <hr>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <a href="#" data-toggle="modal" data-target="#addTicketReplyModal"
                                    class="btn btn-success">@lang('helpdesk::lang.add_reply')</a>
                            </div>

                            <div class="col-md-4">
                                <a href="{{ route('tickets.index') }}" class="btn btn-danger">@lang('helpdesk::lang.close_ticket')</a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br>
                        <br>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="custom_ticket_container box box-primary">
                        <br><br>
                        <div class="col-md-12">

                            <table class="table table-bordered table-striped">
                                @foreach ($ticket->replies->reverse() as $key => $reply)
                                    <tr>
                                        <td>
                                            <br>
                                            <p>{{ $reply->created_at }}</p>
                                            <p>{{ $reply->user->first_name }}</p>
                                            <p>{{ $reply->message }}</p>
                                         
                                        
                                            @if (isset($reply->attachments) && count($reply->attachments) > 0)
                                            <p>
                                                <button type="button" class="btn btn-xs btn-primary btn-view-reply_attachment"
                                                data-replay-attachment-id="{{ $reply->id }}">
                                                    @lang('helpdesk::lang.view_attachments')
                                                </button>
                                            </p>
                                        @endif

                                            <br>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>
                                        <br>
                                        <p>{{ $ticket->created_at }}</p>
                                        <p>{{ $ticket->user->first_name }}</p>
                                        <p>{{ $ticket->message }}</p>
                                        @if (isset($ticket->attachments) && count($ticket->attachments) > 0)
                                            <p>
                                                <button type="button" class="btn btn-xs btn-primary btn-view-attachment"
                                                    data-attachment-id="{{ $ticket->id }}">
                                                    @lang('helpdesk::lang.view_attachments')
                                                </button>
                                            </p>
                                        @endif
                                        <br>
                                    </td>
                                </tr>

                            </table>



                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="addTicketReplyModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open([
                            'url' => action([Modules\HelpDesk\Http\Controllers\HdTicketReplyController::class, 'store'], [$ticket->id]),
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('helpdesk::lang.add_reply')</h4>
                        </div>

                        <div class="modal-body">
                            {!! Form::hidden('ticket_id', $ticket->id) !!}
                            <div class="row">
                                <div class="clearfix"></div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('message', __('helpdesk::lang.message') . ':*') !!}
                                    {!! Form::textarea('message', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('brand.note'),
                                        'required',
                                        'rows' => 8,
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
        <div class="modal fade" id="attachModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('helpdesk::lang.view_attachments')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <h4>@lang('followup::lang.attachments')</h4>
                            <ul id="attachments-list">

                            </ul>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
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
                        data: 'title'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'last_update_date'
                    },
                    {
                        data: 'action'
                    },
                ],
            });


            $(document).on('click', '.btn-view-attachment', function() {
                var id = $(this).data('attachment-id');
                console.log(id);

                if (id) {

                    let url =
                        "{{ action([Modules\HelpDesk\Http\Controllers\HdTicketAttachementController::class, 'index']) }}";
                    $.ajax({
                        'url': url,
                        method: 'GET',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            console.log(response);

                            var attachmentsList = $('#attachments-list');
                            attachmentsList.html('');


                            if (Array.isArray(response) && response.length) {
                                for (var j = 0; j < response.length; j++) {
                                    var attachment = '<li>';
                                    attachment += '<p>';


                                    var attachText = 'Attach ';
                                    attachment += '<a href="{{ url('uploads') }}/' + response[
                                            j].path +
                                        '" target="_blank" onclick="openAttachment(\'' +
                                        response[j].path + '\', ' + (j + 1) + ')">' +
                                        attachText + ' ' + (j + 1) + '</a>';
                                    attachment += '</p>';
                                    attachment += '</li>';


                                    attachmentsList.append(attachment);
                                }
                            }


                            $('#attachmentForm input[name="ticketId"]').val(id);


                            $('#attachModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });
            $(document).on('click', '.btn-view-reply_attachment', function() {
                var id = $(this).data('replay-attachment-id');
                console.log(id);

                if (id) {

                    let url =
                        "{{ action([Modules\HelpDesk\Http\Controllers\HdTicketAttachementController::class, 'replyAttachIndex']) }}";
                    $.ajax({
                        'url': url,
                        method: 'GET',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            console.log(response);

                            var attachmentsList = $('#attachments-list');
                            attachmentsList.html('');


                            if (Array.isArray(response) && response.length) {
                                for (var j = 0; j < response.length; j++) {
                                    var attachment = '<li>';
                                    attachment += '<p>';


                                    var attachText = 'Attach ';
                                    attachment += '<a href="{{ url('uploads') }}/' + response[
                                            j].path +
                                        '" target="_blank" onclick="openAttachment(\'' +
                                        response[j].path + '\', ' + (j + 1) + ')">' +
                                        attachText + ' ' + (j + 1) + '</a>';
                                    attachment += '</p>';
                                    attachment += '</li>';


                                    attachmentsList.append(attachment);
                                }
                            }


                            $('#attachmentForm input[name="ticketId"]').val(id);


                            $('#attachModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });

        });
    </script>
@endsection
