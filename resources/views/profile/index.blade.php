@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Профиль',
        'description'=>'Просмотр и редактирование информации о пользователе'
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('profile') }}
@endsection

@section('content')
    {{ Auth::user()->email }}
@endsection