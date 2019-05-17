<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="format-detection" content="telephone=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" data-lifetime="{{config('session.lifetime')}}">

    <title>@if(isset($pageTitle) && !empty($pageTitle)){{ $pageTitle }}@else{{'MeraCapital'}}@endif</title>
    <meta name="description" content="@if(isset($pageDescription) && !empty($pageDescription)){{ $pageDescription }}@endif">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
</head>
{{--
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
--}}
<body class="hold-transition skin-red-light sidebar-mini">
<div class="wrapper">
    @include('layouts/_app/header')

    @include('layouts/_app/sidebar')

    @include('layouts/_app/content')

    @include('layouts/_app/footer')

    @include('layouts/_app/controlSidebar')
</div>
<!-- ./wrapper -->

<!-- Scripts -->
<script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>