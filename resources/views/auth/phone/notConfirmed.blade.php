@extends('layouts.auth')

@section('content')
<div class="shadow-box">
    <div class="shadow-box__title">Телефон не подтвержден</div>
    <div class="shadow-box__content">
        <div class="error-block">
            <div class="error-block__item">
                Ожидается подтверждение что вы являетесь владельцем телефона: {{ $phone }}
            </div>
            <div class="error-block__footer">
                Если по какой-то причине вы не получили sms активации, <a href="#">свяжитесь с нами</a>, и мы сделаем все возможное что бы помочь вам.
            </div>
        </div>
    </div>
</div>
<div class="shadow-box shadow-box_additionals">
    <span class="form-additional-message">
        Уже зарегистрированы? <a href="{{route('login')}}" class="form-additional-message__link">Войдите</a>
    </span>
</div>
@endsection