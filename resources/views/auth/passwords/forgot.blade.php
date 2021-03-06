@extends('layouts.auth')

@section('content')
    <div class="shadow-box no-shadow no-padding-top">
        <div class="shadow-box__title">Восстановление пароля</div>
        <div class="shadow-box__content">
            @if (session('status'))
                <div id='ajaxSuccessMessage' class="success-block d-block">
                    <div class="success-block__item">
                        {{ session('status') }}
                    </div>
                </div>
            @else
            <form class="form_recover" method="POST" action="{{ route('password.phone') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="userRestorePhone">Телефон</label>
                    <input name="phone"
                           type="phone"
                           class="form-control"
                           id="userRestorePhone"
                           placeholder="Введите ваш телефон"
                           value="{{ old('phone') ?? ''}}" >
                    @if ($errors->has('phone'))
                        <div class="invalid-feedback d-block email">{{ $errors->first('phone') }}</div>
                    @endif
                </div>

                <div class="form-btn">
                    <button type="submit" class="btn btn-info btn-block">Отправить</button>
                </div>
            </form>
            @endif
        </div>
    </div>
    <div class="shadow-box no-shadow shadow-box_additionals">
        <span class="form-additional-message">
            <a href="{{route('login')}}" class="form-additional-message__link">Войти</a> или <a href="{{route('register')}}" class="form-additional-message__link">зарегистрироваться</a>
        </span>
    </div>
@endsection
