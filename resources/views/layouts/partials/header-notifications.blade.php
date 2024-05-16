@php
    // $all_notifications = auth()->user()->notifications;
    // $unread_notifications = $all_notifications->where('read_at', null);
    // $total_unread = count($unread_notifications);
    $all_notifications = App\SentNotificationsUser::where('user_id', auth()->user()->id)->get();
    $unread_notifications = $all_notifications->where('read_at', null);
    $total_unread = count($unread_notifications ?? []);
@endphp
<!-- Notifications: style can be found in dropdown.less -->

<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle load_notifications" data-toggle="dropdown" id="show_unread_notifications"
        data-loaded="false">
        <i class="fas fa-bell"></i>
        @if (!empty($total_unread))
            <span class="label label-warning notifications_count">

                {{ $total_unread }}

            </span>
        @endif
    </a>
    <ul class="dropdown-menu">

        <!-- <li class="header">You have 10 unread notifications</li> -->
        <li>
            <!-- inner menu: contains the actual data -->

            <ul class="menu" id="notifications_list">
            </ul>
        </li>


        @if (count($all_notifications) > 10)
            <li class="footer load_more_li">
                <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
            </li>
        @endif
        <li class="footer load_more_li">
            <a href="{{ route('getMyNotification') }}" class="btn btn-primary">@lang('lang_v1.all_notifications')</a>
        </li>
    </ul>
</li>











{{-- <div class="modal fade" id="notification_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            {!! Form::open([
                'url' => action([\App\Utils\RequestUtil::class, 'changeRequestStatus']),
                'method' => 'post',
                'id' => 'change_status_form',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.change_status')</h4>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <input type="hidden" name="request_id" id="request_id">
                    <label for="status">@lang('sale.status'):*</label>

                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('note', __('followup::lang.note') . ':') !!}
                    {!! Form::textarea('note', null, [
                        'class' => 'form-control',
                        'placeholder' => __('followup::lang.note'),
                        'rows' => 3,
                    ]) !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('reason', __('followup::lang.reason') . ':') !!}
                    {!! Form::textarea('reason', null, [
                        'class' => 'form-control',
                        'placeholder' => __('followup::lang.reason'),
                        'rows' => 3,
                    ]) !!}
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary ladda-button update-offer-status"
                    data-style="expand-right">
                    <span class="ladda-label">@lang('messages.update')</span>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> --}}


<input type="hidden" id="notification_page" value="1">
