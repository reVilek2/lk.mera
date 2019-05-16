@extends('layouts.app')

@section('content')
@auth()
    {{ Auth::user()->email }}
@else
    Не авторизован
@endauth
@endsection