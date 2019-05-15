@auth()
    {{ Auth::user()->email }}
@else
    Не авторизован
@endauth