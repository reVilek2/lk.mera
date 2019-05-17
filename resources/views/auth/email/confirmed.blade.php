@extends('layouts.auth')

@section('content')
<div class="shadow-box">
    <div class="shadow-box__title">Email подтвержден</div>
    <div class="shadow-box__content">
        <div class="success-block">
            <div class="success-block__item">Вы подтвердили что являетесь владельцем адреса: {{ $email }}</div>
            @auth()
            @else
            <div class="success-block__footer">
                Для доступа в личный кабинет необходимо авторизоваться с использованием логина: {{ $email }}
            </div>
            @endauth
        </div>
        <div class="form-btn">
            @auth()
                <a href="{{route('profile')}}" class="btn btn-info btn-block">
                    Перейти в личный кабинет
                </a>
            @else
                <a href="{{route('login')}}" class="btn btn-info btn-block">
                    Перейти на страницу авторизации
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection