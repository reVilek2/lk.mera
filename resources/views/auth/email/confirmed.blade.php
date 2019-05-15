@extends('layouts.auth')

@section('content')
<div class="shadow-box">
    <div class="shadow-box__title">Email подтвержден</div>
    <div class="shadow-box__content">
        <div class="success-block">
            <div class="success-block__item">Регистрация успешно завершена! Вы подтвердили что являетесь владельцем адреса: {{ $email }}</div>
            <div class="success-block__footer">
                Для доступа в личный кабинет необходимо авторизоваться с использованием логина: {{ $email }}
            </div>
        </div>
        <div class="form-btn">
            <a href="{{route('login')}}" class="btn btn-info btn-block">
                Перейти на страницу авторизации
            </a>
        </div>
    </div>
</div>
@endsection