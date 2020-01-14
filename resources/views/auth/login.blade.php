@extends('layouts.auth')

@section('content')
<div class="shadow-box no-shadow jsAjaxForm">
    <div class="shadow-box__title">Авторизация</div>
    <div class="shadow-box__content">
        @if ($errors->has('authentication_failed'))
            <div id='ajaxErrorMessage' class="error-block d-block">
                <div class="error-block__item">
                    {{ $errors->first('authentication_failed') }}
                </div>
            </div>
        @endif
        <form class="form_login" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="userSigninLogin">Телефон или е-мейл</label>
                <input
                        name="login"
                        type="text"
                        class="form-control"
                        id="userSigninLogin"
                        placeholder="Введите телефон или е-мейл"
                        value="{{ old('login') ?? ''}}" />
                @if ($errors->has('login'))
                    <div class="invalid-feedback d-block login">{{ $errors->first('login') }}</div>
                @endif
                @if ($errors->has('phone'))
                    <div class="invalid-feedback d-block phone">{{ $errors->first('phone') }}</div>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label" for="userSigninPassword">Пароль</label>
                <input
                        name="password"
                        type="password"
                        class="form-control"
                        id="userSigninPassword"
                        placeholder="Введите пароль" />
                @if ($errors->has('password'))
                    <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                @endif
            </div>
            <div class="form-btn">
                <button type="submit" class="btn btn-danger btn-block">Войти</button>
                <a href="{{route('password.forgot')}}" class="form-remind-password-link">
                    Забыли пароль?
                </a>
            </div>
        </form>
    </div>
</div>
<div class="shadow-box no-shadow">
    <div class="form-btn">
        <button type="button" onclick="location.href='{{route('register')}}'" class="btn btn-info btn-block">Зарегистрироваться</button>
    </div>
</div>
@endsection
