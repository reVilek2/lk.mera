@extends('layouts.auth')

@section('content')
    <div class="shadow-box">
        <div class="shadow-box__title">Подтверждение телефона</div>
        <div class="shadow-box__content">
            <div class="error-block">
                <div class="error-block__item">Подтверждение телефона временно недоступно</div>
            </div>
        </div>
    </div>
    <div class="shadow-box shadow-box_additionals">
            <span class="form-additional-message">
                <a href="{{route('login')}}" class="form-additional-message__link">Войти</a> или <a href="{{route('register')}}" class="form-additional-message__link">зарегистрироваться</a>
            </span>
    </div>
@endsection
