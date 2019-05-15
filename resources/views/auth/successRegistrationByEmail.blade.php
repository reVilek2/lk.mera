@extends('layouts.auth')

@section('content')
<div class="shadow-box">
    <div class="shadow-box__title">Завершение регистрации</div>
    <div class="shadow-box__content">
        <div class="success-block">
            <div class="success-block__item">
                Письмо со ссылкой для подтверждения Email отправлено на <span class="success-block__email">{{ $email }}</span>
                <br>
            </div>
            <div class="success-block__footer">
                Перейдите по ссылке из email для активации аккаунта. <br><br>
                Если этого письма нет во «Входящих», пожалуйста, проверьте «Спам». <br><br>
                Если по какой-то причине вы не получили письмо активации, <a href="#">свяжитесь с нами</a>, и мы сделаем все возможное что бы помочь вам.
            </div>
        </div>
    </div>
</div>
<div class="shadow-box shadow-box_additionals">
    <span class="form-additional-message">
        Уже зарегистрированы? <a href="{{ route('login') }}" class="form-additional-message__link">Войдите</a>
    </span>
</div>
@endsection