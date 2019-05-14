@extends('layouts.auth')

@section('content')
<div class="shadow-box jsAjaxForm">
    <div class="shadow-box__title">Авторизация</div>
    <div class="shadow-box__content">
        <div id='ajaxErrorMessage' class="error-block hidden">&nbsp;</div>
        <form class="form_login"
              data-request="onSignin"
              data-request-validate>
            <div class="form-group">
                <label class="form-label" for="userSigninLogin">Телефон или email</label>
                <input
                        name="login"
                        type="text"
                        class="form-control"
                        id="userSigninLogin"
                        placeholder="Введите телефон или email" />
                <div class="invalid-feedback" data-validate-for="login"></div>
            </div>
            <div class="form-group">
                <label class="form-label" for="userSigninPassword">Пароль</label>
                <input
                        name="password"
                        type="password"
                        class="form-control"
                        id="userSigninPassword"
                        placeholder="Введите пароль" />
                <div class="invalid-feedback" data-validate-for="password"></div>
            </div>
            <div class="form-btn">
                <button type="submit" class="btn btn-info btn-block">Войти</button>
            </div>
        </form>
    </div>
</div>
<div class="shadow-box shadow-box_additionals">
    <span class="form-additional-message">
        <a href="/login" class="form-additional-message__link">Войти</a> или <a href="/register" class="form-additional-message__link">зарегистрироваться</a>
    </span>
</div>
@endsection
