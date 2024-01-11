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
                                <a href="#" class="btn btn-danger">@lang('helpdesk::lang.close_ticket')</a>
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
                                                <p><a href="#" class="btn btn-xs btn-info ">@lang('helpdesk::lang.view_attachments')</a></p>
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
                                            <p><a href="#" class="btn btn-xs btn-info ">@lang('helpdesk::lang.view_attachments')</a></p>
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


        });
    </script>
@endsection
