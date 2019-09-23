@component('mail::layout')

    {{-- Body --}}
    {{ $slot }}

@component('mail::recommendation.sign')
@endcomponent

@endcomponent
