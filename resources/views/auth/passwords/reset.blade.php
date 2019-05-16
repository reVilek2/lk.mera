@extends('layouts.auth')

@section('content')
    <div class="shadow-box">
        <div class="shadow-box__title">Восстановление пароля</div>
        <div class="shadow-box__content">
            @if (session('status'))
                <div id='ajaxSuccessMessage' class="success-block d-block">
                    <div class="success-block__item">
                        {{ session('status') }}
                    </div>
                </div>
            @else
                <form class="form_recover" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="form-group">
                        <label class="form-label" for="resetCode">Ваш Email</label>
                        <input type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Введите ваш email" value="{{ $email ?? old('email') }}" required autofocus>
                        @if ($errors->has('email'))
                            <div class="invalid-feedback d-block email">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="registerPassword">Пароль</label>
                        <input
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="Введите пароль"
                                id="registerPassword"
                                required />
                        @if ($errors->has('password'))
                            <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="registerPasswordConfirmation">Пароль еще раз</label>
                        <input
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                id="registerPasswordConfirmation"
                                placeholder="Введите пароль еще раз"
                                required />
                    </div>

                    <div class="form-btn">
                        <button type="submit" class="btn btn-info btn-block">Сбросить пароль</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div class="shadow-box shadow-box_additionals">
        <span class="form-additional-message">
            <a href="{{route('login')}}" class="form-additional-message__link">Войти</a> или <a href="{{route('register')}}" class="form-additional-message__link">зарегистрироваться</a>
        </span>
    </div>
@endsection
