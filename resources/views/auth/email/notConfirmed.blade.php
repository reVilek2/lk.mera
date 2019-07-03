@extends('layouts.auth')

@section('content')
<div class="shadow-box">
    <div class="shadow-box__title">Email не подтвержден</div>
    <div class="shadow-box__content">
        <div class="error-block">
            <div class="error-block__item">
                Ожидается подтверждение что вы являетесь владельцем адреса: {{ $email }}
            </div>
            <div class="error-block__footer">
                Перейдите по ссылке из email для активации аккаунта. <br><br>
                Если этого письма нет во «Входящих», пожалуйста, проверьте «Спам». <br><br>
                При наличии нескольких писем, используйте то которое пришло последним.
            </div>
            <div id="resend-link">
                <a href="{{route('email.code.resend', $email)}}" class="form-resend-link js-resend-email-code">
                    Отправить код повторно.
                </a>
                <div class="preloader preloader-sm js-preloader" style="display: none"></div>
                <div class="invalid-feedback"></div>
                <div class="valid-feedback"></div>
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