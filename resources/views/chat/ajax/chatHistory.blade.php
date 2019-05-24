<div id="js-chat-{{$user->id}}" class="box box-primary direct-chat direct-chat-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{$user->getUserName()}}</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div id="js-chat-list" class="direct-chat-messages">
            @foreach($messages as $message)
                @if($message->sender->id == auth()->user()->id)
                    {{--<a href="#" class="talkDeleteMessage" data-message-id="{{$message->id}}" title="Delete Message"><i class="fa fa-close"></i></a>--}}
                    @include('chat/ajax/senderMessageHtml', ['message' => $message])
                @else
                    @include('chat/ajax/receiverMessageHtml', ['message' => $message])
                @endif
            @endforeach
        </div>
    </div>
    <div class="box-footer">
        <form action="#" method="post" id="js-chat-send-message" data-action="{{ route('message.send', $user) }}">
            <input type="hidden" name="_id" value="{{ $user->id }}">
            <div class="input-group">
                <input type="text" name="message-data" placeholder="Type Message ..." class="form-control">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary btn-flat">Отправить</button>
                </span>
            </div>
        </form>
    </div>
</div>