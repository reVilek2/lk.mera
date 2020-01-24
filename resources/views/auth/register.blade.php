@extends('layouts.auth')

@section('content')
<div id="ajax-register">
    <div id="jsAjaxForm" class="shadow-box no-shadow no-padding-top">
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
                        <label class="form-label" for="registerPhone">Телефон *</label>
                        <div class="wrapper-tooltip">
                        <input
                                name="phone"
                                type="text"
                                class="form-control"
                                id="registerPhone"
                                placeholder="Введите телефон"
                                required
                                value="{{ old('phone') ?? ''}}" />
                                <span>Введите номер телефона, указав в начале код вашей страны <br>Например: +7**********<br>+380**********</span>
                            </div>
                        @if ($errors->has('phone'))
                            <div class="invalid-feedback d-block">{{ $errors->first('phone') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="registerEmail">Email *</label>
                        <input
                                name="email"
                                type="text"
                                class="form-control"
                                id="registerEmail"
                                placeholder="Введите email"
                                required
                                value="{{ old('email') ?? ''}}" />
                        @if ($errors->has('email'))
                            <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="registerPassword">Пароль *</label>
                        <div class="wrapper-tooltip">
                            <input
                                    name="password"
                                    type="password"
                                    class="form-control"
                                    id="registerPassword"
                                    placeholder="Введите пароль"
                                    required  />
                            <span>Пароль может состоять из латинских букв и цифр, должен иметь не менее одной прописной буквы и цифры и быть не менее 8 символов в длину</span>
                        </div>
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
                        <button type="submit" class="btn btn-danger btn-block">Регистрация</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div class="shadow-box no-shadow shadow-box_additionals">
        <span class="form-additional-message">
            Уже зарегистрированы? <a href="{{ route('login') }}" class="form-additional-message__link">Войдите</a>
        </span>
    </div>
</div>
@endsection
