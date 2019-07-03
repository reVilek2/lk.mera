@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Чат'
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('profile') }}
@endsection

@section('content')
    @if (session('status'))
        @include('components/noty', [
            'type' => session('status'),
            'text' => session('statusMessage'),
        ])
    @endif

    <div class="chat-wrapper">
        <div class="chat-sidebar">
            <div class="chat-sidebar__header">
                <div class="chat-sidebar__header-title">Список чатов</div>
            </div>
            <div class="chat-sidebar__item">
                <ul class="chat-list"><li class="chat-list__item active"><a href="/chat/11" data-chat="11" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/chats/group.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Администраторы</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/4" data-chat="4" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Денис Городчанинов</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/3" data-chat="3" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Ольга Першина</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/2" data-chat="2" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Андрей Гусев</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/1" data-chat="1" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Антон Окрема</span> <!----></span></a></li><li class="chat-list__item active"><a href="/chat/11" data-chat="11" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/chats/group.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Администраторы</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/4" data-chat="4" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Денис Городчанинов</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/3" data-chat="3" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Ольга Першина</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/2" data-chat="2" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Андрей Гусев</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/1" data-chat="1" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Антон Окрема</span> <!----></span></a></li><li class="chat-list__item active"><a href="/chat/11" data-chat="11" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/chats/group.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Администраторы</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/4" data-chat="4" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Денис Городчанинов</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/3" data-chat="3" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Ольга Першина</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/2" data-chat="2" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Андрей Гусев</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/1" data-chat="1" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Антон Окрема</span> <!----></span></a></li><li class="chat-list__item active"><a href="/chat/11" data-chat="11" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/chats/group.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Администраторы</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/4" data-chat="4" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Денис Городчанинов</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/3" data-chat="3" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Ольга Першина</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/2" data-chat="2" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Андрей Гусев</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/1" data-chat="1" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Антон Окрема</span> <!----></span></a></li><li class="chat-list__item active"><a href="/chat/11" data-chat="11" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/chats/group.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Администраторы</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/4" data-chat="4" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Денис Городчанинов</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/3" data-chat="3" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Ольга Першина</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/2" data-chat="2" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Андрей Гусев</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/1" data-chat="1" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Антон Окрема</span> <!----></span></a></li><li class="chat-list__item active"><a href="/chat/11" data-chat="11" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/chats/group.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Администраторы</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/4" data-chat="4" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Денис Городчанинов</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/3" data-chat="3" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Ольга Першина</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/2" data-chat="2" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Андрей Гусев</span> <!----></span></a></li><li class="chat-list__item"><a href="/chat/1" data-chat="1" class="chat-list__link js-load-user-chat"><span class="chat-list__user-icon"><img src="/images/profile/avatar_thumb.jpg" alt="Chat logo" class="user-image"> <!----></span> <span class="chat-list__user-box"><span class="chat-list__user-name">Антон Окрема</span> <!----></span></a></li></ul>
            </div>
        </div>
        <div class="chat-content">
            <div class="chat-content__header">
                <div class="chat-content__header-btn"></div>
                <div class="chat-content__header-title">Заголовок</div>
            </div>
            <div class="chat-content__message">
                <div>
                    <div id="message-1" class="chat-msg right">
                        <div class="chat-msg__info">
                            <span class="chat-msg__info-name pull-right">Игорь Зубарев</span>
                        </div>
                            <img src="/storage/media/1/conversions/Image-thumb.jpg" alt="Message User Image" class="chat-msg__img">
                        <div class="chat-msg__text">
                            <span class="chat-msg__text-item">sss</span>
                            <span class="chat-msg__timestamp pull-right">03 июля, 17:20</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div id="message-3" class="chat-msg">
                        <div class="chat-msg__info">
                            <span class="chat-msg__info-name pull-left">Игорь Зубарев</span>
                        </div>
                        <img src="/images/profile/avatar_thumb.jpg" alt="Message User Image" class="chat-msg__img">
                        <div class="chat-msg__text">
                            <span class="chat-msg__text-item">sss</span>
                            <span class="chat-msg__timestamp pull-left">03 июля, 17:22</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chat-content__message-input">
                <div class="chat-input-group">
                    <input type="hidden" name="_id" value="">
                    <textarea placeholder="сообщение ..." rows="1" class="form-control" style="resize: none; overflow: hidden; height: 34px;"></textarea>
                    <span class="chat-bth">
                        <button class="btn btn-primary btn-flat">Отправить</button>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <chats-list :userid="{{auth()->id()}}" :chats="{{$chats}}"></chats-list>
@endsection