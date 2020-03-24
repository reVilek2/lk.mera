@component('mail::mera.message')

<div class="greetings">
    <p>
        Здравствуйте!
    </p>
</div>

Вы зарегистрировали Личный кабинет на сайте <a href="{{config('mera-capital.main_site_url')}}">mera-capital.com</a>
<br>
Ваш логин:  {{$login}}

Для завершения регистрации Личного кабинета подтвердите Вашу электронную почту
@component('mail::button', ['url' => $url, 'color' => 'red'])
Подтвердить почту
@endcomponent

@component('mail::subcopy')
Если кнопка "Подтвердить почту" не работает, скопируйте данную ссылку в адресную строку Вашего браузера:<br>{{ $url }}
@endcomponent

@endcomponent
