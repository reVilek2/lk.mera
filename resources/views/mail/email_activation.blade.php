@component('mail::message')
# Здравствуйте!

@component('mail::button', ['url' => $url])
Подтвердить почту
@endcomponent

С уважением,<br>{{ config('app.name') }}

@component('mail::subcopy')
Если у вас возникли проблемы с нажатием кнопки "Сбросить пароль", скопируйте URL ниже и вставьте в ваш веб-браузер <br>{{ $url }}
@endcomponent
@endcomponent