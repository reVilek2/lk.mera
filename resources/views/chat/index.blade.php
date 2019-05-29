@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Чат'
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('profile') }}
@endsection

@section('content')
    @if (session('status'))
        @include('components/noty', [
            'type' => session('status'),
            'text' => session('statusMessage'),
        ])
    @endif
    <chats-list :userid="{{auth()->id()}}" :chats="{{$chats}}"></chats-list>
@endsection