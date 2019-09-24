<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="format-detection" content="telephone=no">

    <title>{{ $headTitle }}</title>
    <meta name="description" content="">
    <meta name="title" content="{{ $headTitle }}">
    <meta name="description" content="@if(isset($pageDescription) && !empty($pageDescription)){{ $pageDescription }}@endif">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" data-lifetime="{{config('session.lifetime')}}">

    <link href="{{ mix('/css/auth.css') }}" rel="stylesheet">
</head>
<body>

<main id="app" class="layout">
    <div class="layout__page layout__page-thin">
        @yield('content')
    </div>
</main>

<!-- Scripts -->
<script src="{{ mix('/js/auth.js') }}"></script>
</body>
</html>
