@extends('layouts.auth')

@section('content')
    <div class="shadow-box">
        <div class="shadow-box__title">{{$information ? 'Телефон не подтвержден' : 'Подтверждение телефона'}}</div>
        <div class="shadow-box__content">
            @if ($information)
                <div class="error-block">
                    <div class="error-block__item">
                        Ожидается подтверждение что вы являетесь владельцем телефона: {{ $phone }}
                    </div>
                    <div class="error-block__footer">
                        Если по какой-то причине вы не получили Sms активации, нажмите "Отправить код повторно".
                    </div>
                </div>
            @else
                <div class="success-block">
                    <div class="success-block__item">
                        Sms с кодом активации отправлен на телефон: <span class="success-block__email">{{ $phone }}</span>
                        <br>
                    </div>
                    <div class="success-block__footer">
                        Если по какой-то причине вы не получили Sms активации, нажмите "Отправить код повторно".
                    </div>
                </div>
            @endif
            @if ($errors->has('phone_confirm_form'))
                <div class="error-block">
                    <div class="error-block__item">
                        {{ $errors->first('phone_confirm_form') }}
                    </div>
                </div>
            @endif
            <form class="form_confirm_phone" method="POST" action="{{ route('phone.confirm', $phone) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="phoneCode">Код активации</label>
                    <input
                            name="code"
                            type="text"
                            class="form-control"
                            id="phoneCode"
                            placeholder="Введите код активации"
                            value="{{ old('code') ?? ''}}" />
                    @if ($errors->has('code'))
                        <div class="invalid-feedback d-block">{{ $errors->first('code') }}</div>
                    @endif
                    <script type="text/javascript">
                        window.onload = function () {
                            let i = {{$resend_phone_code_time}};
                            if (i > 0) {
                                document.getElementById('resend-link').style.display = 'none';
                                document.getElementById('resend-link-timer').style.display = 'block';
                                let timer = setInterval(function () {
                                    i--;
                                    document.getElementById('resend-link-timer').innerHTML = 'Время действия кода: ' + i + ' сек.';
                                    if (i === 0) {
                                        document.getElementById('resend-link').style.display = 'block';
                                        document.getElementById('resend-link-timer').style.display = 'none';
                                        clearInterval(timer);
                                    }
                                }, 1000);
                            }
                        }
                    </script>

                    <span id="resend-link-timer" class="form-resend-link" style="display: none"></span>
                    <div id="resend-link">
                        <a href="{{route('phone.code.resend', $phone)}}" class="form-resend-link js-resend-phone-code">
                            Отправить код повторно.
                        </a>
                    </div>

                </div>
                <div class="form-btn">
                    <button type="submit" class="btn btn-info btn-block">Подтвердить</button>
                </div>
            </form>
        </div>
    </div>
    <div class="shadow-box shadow-box_additionals">
            <span class="form-additional-message">
                <a href="{{route('login')}}" class="form-additional-message__link">Войти</a> или <a href="{{route('register')}}" class="form-additional-message__link">зарегистрироваться</a>
            </span>
    </div>
@endsection
