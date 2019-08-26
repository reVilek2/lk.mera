@component('mail::message')

На вашу рекомендацию {{$recommendation_title}} от {{ $recommendation_date }} откликнулся {{ $client_name }}.

{{ config('app.name') }}
@endcomponent
