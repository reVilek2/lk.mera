<div class="direct-chat-msg" id="message-{{$message->id}}">
    <div class="direct-chat-info clearfix">
        <span class="direct-chat-timestamp pull-left">{{humanize_date($message->created_at, 'd F, H:i')}}</span>
    </div>
    <img class="direct-chat-img" src="{{$message->sender->getAvatar('thumb')}}" alt="Message User Image"><!-- /.direct-chat-img -->
    <div class="direct-chat-text">
        {{ $message->message }}
    </div>
</div>