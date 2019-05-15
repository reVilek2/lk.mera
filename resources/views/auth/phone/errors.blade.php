@extends('layouts.auth')

@section('content')
<div class="shadow-box">
    <div class="shadow-box__title">Подтверждение телефона</div>
    <div class="shadow-box__content">
        <div class="error-block">
            <div class="error-block__item">Ошибка: {{ $errors->first() }}</div>
            <div class="error-block__footer">
                Если по какой-то причине у вас не получилось подтвердить телефон, <a href="#">свяжитесь с нами</a>, и мы сделаем все возможное что бы помочь вам.
            </div>
        </div>
    </div>
</div>
<div class="shadow-box shadow-box_additionals">
    <span class="form-additional-message">
        <a href="{{route('login')}}" class="form-additional-message__link">Войти</a> или <a href="{{route('register')}}" class="form-additional-message__link">зарегистрироваться</a>
    </span>
</div>
@endsection