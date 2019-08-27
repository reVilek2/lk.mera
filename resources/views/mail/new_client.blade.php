@component('mail::message')
# Здравствуйте!

Пользователь {{$client_name}} подтвердил свою регистрацию.

@component('mail::button', ['url' => $url])
Перейти в профиль пользовотеля
@endcomponent

С уважением,<br>{{ config('app.name') }}

@endcomponent
