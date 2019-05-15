@extends('layouts.auth')

@section('content')
<div id="ajax-register">
    <div id="jsAjaxForm" class="shadow-box">
        <div class="shadow-box__title">Регистрация</div>
        <div class="shadow-box__content">
            <div id='ajaxErrorMessage' class="error-block hidden">&nbsp;</div>
            @if (session('status'))
                <div id='ajaxSuccessMessage' class="success-block d-block">
                    <div class="success-block__item">
                        {{ session('statusMessage') }}
                    </div>
                </div>
            @else
                <form class="form_register" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="registerPhoneOrEmail">Телефон или Email *</label>
                        <input
                                name="phone_or_email"
                                type="text"
                                class="form-control"
                                id="registerPhoneOrEmail"
                                placeholder="Введите телефон или email"
                                required
                                value="{{ old('phone_or_email') ?? ''}}" />
                        @if ($errors->has('phone_or_email'))
                            <div class="invalid-feedback d-block">{{ $errors->first('phone_or_email') }}</div>
                        @endif

                    </div>

                    <div class="form-group">
                        <label class="form-label" for="registerPassword">Пароль *</label>
                        <input
                                name="password"
                                type="password"
                                class="form-control"
                                id="registerPassword"
                                placeholder="Введите пароль"
                                required />
                        @if ($errors->has('password'))
                            <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="registerPasswordConfirmation">Пароль еще раз *</label>
                        <input
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                id="registerPasswordConfirmation"
                                placeholder="Введите пароль еще раз"
                                required />
                    </div>

                    <div class="form-btn">
                        <button type="submit" class="btn btn-info btn-block">Регистрация</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div class="shadow-box shadow-box_additionals">
    <span class="form-additional-message">
        Уже зарегистрированы? <a href="{{ route('login') }}" class="form-additional-message__link">Войдите</a>
    </span>
    </div>
</div>
@endsection
