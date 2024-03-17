@if (!empty($notifications_data))
    @foreach ($notifications_data as $notification_data)
        <li style=" word-wrap: break-word; 
        
        display: block;"
            class="@if (empty($notification_data['read_at'])) unread @endif notification-li">
            {{-- <li class=" unread notification-li"> --}}
            <a href="" data-toggle="modal" data-target="#notification_modal"
                data-title="{{ $notification_data['title'] }}" data-msg="{{ $notification_data['msg'] }}">
                <i class="notif-icon {{ $notification_data['icon_class'] ?? '' }}"></i>
                <h4>{!! $notification_data['title'] ?? '' !!}</h4>
                {{-- <span class="notif-info">{!! $notification_data['msg'] ?? '' !!}</span> --}}
                <span class="time">{{ $notification_data['created_at'] }}</span>
            </a>
        </li>
    @endforeach
@else
    <li style="height:50px; display: flex; justify-content: center; align-items: center; text-align: center;"
        class="text-center no-notification notification-li">
        @lang('lang_v1.no_notifications_found')
    </li>
@endif
<script>
    $(document).ready(function() {
        $('#notification_modal').on('show.bs.modal', function(event) {
            // Get the button (or link) that triggered the modal
            var button = $(event.relatedTarget);

            var title = button.data('title');
            var message = button.data('msg');

            // Update the modal's content.
            var modal = $(this);
            modal.find('#notification_modal_title').text(title);
            modal.find('#notification_modal_msg').text(message);
        });
    });
</script>
