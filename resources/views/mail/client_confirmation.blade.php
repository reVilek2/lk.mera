@component('mail::mera.message')
# Здравствуйте!

Вы зарегистрировали Личный кабинет на сайте <a href="{{config('mera-capital.main_site_url')}}">mera-capital.com</a>

Ваш логин:  {{$login}}

С уважением,<br>{{ config('app.name') }}

@endcomponent
