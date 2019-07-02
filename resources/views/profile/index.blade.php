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
    @if (session('status'))
        @include('components/noty', [
            'type' => session('status'),
            'text' => session('statusMessage'),
        ])
    @endif

    <div class="row">
        <div class="col-md-3">

            <!-- Profile Image -->
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <user-profile-box :profile-user="{{$user}}"
                                      :is-profile="true"
                                      :managers="{{'[]'}}"
                                      :current-manager="{{$currentManager ?? '{}'}}"></user-profile-box>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
            <user-profile-tabs :profile-user="{{$user}}"
                               :is-profile="true"></user-profile-tabs>

            <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
    </div>
@endsection