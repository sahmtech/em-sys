@extends('layouts.app')
@section('title', __('home.outside_communication'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('home.outside_communication')</span>
        </h1>
    </section>

    <!-- Main content -->
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @else
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    @endif

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" data-toggle="modal"
                        data-target="#addRequestModal">
                        <i class="fa fa-plus"></i> @lang('home.send message')
                    </button>
                </div>
            @endslot

            <div class="table-responsive">
                <h3>@lang('home.sent_messages')</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('home.Sender')</th>
                            <th>@lang('home.Receiver Department')</th>
                            <th>@lang('home.title')</th>
                            <th>@lang('home.Message')</th>
                            <th>@lang('home.urgency')</th>
                            <th>@lang('home.attachments')</th>
                            <th>@lang('home.reply')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sentMessages as $message)
                            <tr>
                                <td>{{ $users[$message->sender_id] ?? '' }}</td>
                                <td>{{ $departments[$message->reciever_department_id] ?? '' }}</td>
                                <td>{{ $message->title }}</td>
                                <td>{{ $message->message }}</td>
                                <td>{{ __('helpdesk::lang.' . $message->urgency) }}</td>
                                <td>
                                    @if ($message->attachments->isEmpty())
                                        {{ __('home.no_attachments') }}
                                    @else
                                        <ul>
                                            @foreach ($message->attachments as $attachment)
                                                <li><a href="{{ asset('uploads/' . $attachment->path) }}"
                                                        target="_blank">@lang('home.view_attach')</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    @if ($message->replies->isEmpty())
                                        {{ __('home.no_replay') }}
                                    @else
                                        @foreach ($message->replies as $reply)
                                            <li>{{ $reply->replay }} - {{ $users[$reply->replied_by] ?? '' }}</li>
                                            @foreach ($reply->attachments as $attachment)
                                                <a href="{{ asset('uploads/' . $attachment->path) }}"
                                                    target="_blank">@lang('home.view_attach')</a>,
                                            @endforeach
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive">
                <h3>@lang('home.received_messages')</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('home.Sender')</th>
                            <th>@lang('home.Sender Department')</th>
                            <th>@lang('home.title')</th>
                            <th>@lang('home.Message')</th>
                            <th>@lang('home.urgency')</th>
                            <th>@lang('home.attachments')</th>
                            <th>@lang('home.reply')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receivedMessages as $message)
                            <tr>
                                <td>{{ $users[$message->sender_id] ?? '' }}</td>
                                <td>{{ $departments[$message->sender_department_id] ?? '' }}</td>
                                <td>{{ $message->title }}</td>
                                <td>{{ $message->message }}</td>
                                <td>{{ __('helpdesk::lang.' . $message->urgency) }}</td>
                                <td>
                                    @if ($message->attachments->isEmpty())
                                        {{ __('home.no_attachments') }}
                                    @else
                                        <ul>
                                            @foreach ($message->attachments as $attachment)
                                                <li><a href="{{ asset('uploads/' . $attachment->path) }}"
                                                        target="_blank">@lang('home.view_attach')</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    @if ($message->replies->isEmpty())
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#replyModal-{{ $message->id }}">
                                            @lang('home.add reply')
                                        </button>

                                        <!-- Reply Modal -->
                                        <div class="modal fade" id="replyModal-{{ $message->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="replyModalLabel-{{ $message->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="replyModalLabel-{{ $message->id }}">
                                                            @lang('home.reply_to_message')</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('communication.reply', $message->id) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="reply">@lang('home.reply')</label>
                                                                <textarea class="form-control" id="reply" name="reply" rows="3" required></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="attachments">@lang('home.attachments')</label>
                                                                <input type="file" name="attachments[]" multiple>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">@lang('home.close')</button>
                                                            <button type="submit"
                                                                class="btn btn-primary">@lang('home.send')</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <ul>
                                            @foreach ($message->replies as $reply)
                                                <li>{{ $reply->replay }} - {{ $users[$reply->replied_by] ?? '' }}</li>
                                                @foreach ($reply->attachments as $attachment)
                                                    <a href="{{ asset('uploads/' . $attachment->path) }}"
                                                        target="_blank">@lang('home.view_attach')</a>,
                                                @endforeach
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcomponent
    </section>

    <!-- Add Request Modal -->
    <div class="modal fade" id="addRequestModal" tabindex="-1" role="dialog" aria-labelledby="addRequestModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRequestModalLabel">@lang('home.send message')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('send_communication_message') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="from" value="{{ $from }}">
                     <input type="hidden"   name="type" value="outside">
                        

                        <div class="form-group">
                            <label for="department">@lang('home.department')</label>
                            <select style="height:40px;" class="form-control" id="department" name="department">
                                @foreach ($departments as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">@lang('home.title')</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="message">@lang('home.message')</label>
                            <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="urgency">@lang('home.urgency')</label>
                            <select class="form-control" id="urgency" name="urgency">
                                @foreach ($urgencies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="attachments">@lang('home.attachments')</label>
                            <input type="file" name="attachments[]" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('home.close')</button>
                        <button type="submit" class="btn btn-primary">@lang('home.send')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var from = '{{ $from }}';
            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('Communication') }}/" + from,
                },
                columns: [{
                        data: 'sender_id',
                        name: 'sender_id'
                    },
                    {
                        data: 'reciever_department_id',
                        name: 'reciever_department_id'
                    },
                    {
                        data: 'message',
                        name: 'message'
                    }
                ]
            });
        });
    </script>
@endsection
