@extends('layouts.auth')

@section('content')
    <div class="shadow-box no-shadow no-padding-top">
        <div class="shadow-box__title">Код подтверждения</div>
        <div class="shadow-box__content">
            @if (session('status'))
                <div id='ajaxSuccessMessage' class="success-block d-block">
                    <div class="success-block__item">
                        {{ session('status') }}
                    </div>
                </div>
            @else
            <form id="sendCodeForm" class="form_recover" method="POST" action="{{ route('password.code.check', ['phone' => $phone]) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="userRestorePhone">Введите код из SMS</label>
                    <input name="code"
                           type="number"
                           class="form-control"
                           id="userRestorePhone"
                           placeholder="****"
                           onkeyup="sendCode(this.value)">
                    @if ($errors->has('phone'))
                        <div class="invalid-feedback d-block email">{{ $errors->first('phone') }}</div>
                    @endif
                </div>
            </form>
            @endif

            <form class="form_recover" method="POST" action="{{ route('password.phone') }}">
                @csrf
                <div class="form-group">
                    <input type="hidden" name="phone" value="{{ $phone }}">
                    <button type="submit" class="btn btn-info">Выслать код повторно</button>
                    <button type="submit" class="btn btn-default">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <div class="shadow-box no-shadow shadow-box_additionals">
        <span class="form-additional-message">
            <a href="{{route('login')}}" class="form-additional-message__link">Войти</a> или <a href="{{route('register')}}" class="form-additional-message__link">зарегистрироваться</a>
        </span>
    </div>

    <script>
        function sendCode(code) {
            if (code.length >= 4) {
                sendCodeForm.submit();
            }
        }
    </script>
@endsection
