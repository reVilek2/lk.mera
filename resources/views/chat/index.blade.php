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

    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">All Users</div>

                <div class="box-body">
                    <ul class="chat-list">
                    @foreach($users as $user)
                        <li class="chat-list__item">
                            <a href="{{route('chat.read', $user)}}" class="chat-list__link js-load-user-chat" data-chat="{{$user->id}}">
                                <span class="chat-list__user-icon"><img src="{{ $user->getAvatar('thumb') }}" class="user-image" alt="User Image"></span>
                                <span>{{ $user->getUserName() }}</span>
                            </a>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="js-chat-messages chat-messages"></div>
        </div>
        {{--<div>--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">Chats</div>--}}

                {{--<div class="panel-body">--}}
                    {{--<chat-messages :messages="messages"></chat-messages>--}}
                {{--</div>--}}
                {{--<div class="panel-footer">--}}
                    {{--<chat-form--}}
                        {{--v-on:messagesent="addMessage"--}}
                        {{--:user="{{ Auth::user() }}"--}}
                    {{--></chat-form>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    </div>
@endsection