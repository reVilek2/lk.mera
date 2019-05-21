<div class="direct-chat-msg right" id="message-{{$message->id}}">
    <div class="direct-chat-info clearfix">
        <span class="direct-chat-name pull-right">{{$message->sender->getUserName()}}</span>
        <span class="direct-chat-timestamp pull-left">{{diffForHumans($message->created_at)}}</span>
    </div>
    <img class="direct-chat-img" src="{{$message->sender->getAvatar('thumb')}}" alt="Message User Image"><!-- /.direct-chat-img -->
    <div class="direct-chat-text">
        {{ $message->message }}
    </div>
</div>