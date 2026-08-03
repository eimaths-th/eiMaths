@forelse (Auth::user()->unreadNotifications as $notification)
    <div class="single_notify d-flex align-items-top"
         id="menu_notification_show_{{$notification->id}}">
        <div class="notify_thumb">
            <i class="fa fa-bell"></i>
        </div>
        @if ($notification->type === \App\Notifications\BookShipmentNotification::class)
            <form method="POST" action="{{ route('book-shipment-notifications.read', $notification->id) }}">
                @csrf
                <button type="submit" class="btn btn-link text-left p-0"
                    aria-label="Open book shipment notification">
                    <div class="notify_content">
                        <h5>{{ $notification->data['title'] ?? 'Book shipment notification' }}</h5>
                        <p>{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </button>
            </form>
        @else
            <a href="#" class="unread_notification" title="Mark As Read"
               data-notification_id="{{$notification->id}}">
                <div class="notify_content">
                    <h5>{!!  strip_tags(\Illuminate\Support\Str::limit(@$notification->data['title'], 30, $end='...')) !!}</h5>
                    {{--                                                    <p>{!!  strip_tags(\Illuminate\Support\Str::limit(@$notification->data['body'], 70, $end='...')) !!}</p>--}}
                    <p>{{$notification->created_at->diffForHumans()}}</p>
                </div>
            </a>
        @endif
    </div>
@empty
    <span class="text-center">{{__('common.No Unread Notification')}}</span>
@endforelse
