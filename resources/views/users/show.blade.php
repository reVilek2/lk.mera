@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Пользователь',
        'description'=>''
    ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('user') }}
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
                    <user-profile-box :current-user="{{$user}}" :managers="{{$managers}}" :current-manager="{{$currentManager ?? '{}'}}"></user-profile-box>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="{{(session('active_tab') && session('active_tab') == 'info') || !session('active_tab') ? 'active': ''}}"><a href="#info" data-toggle="tab">Информация</a></li>
                    <li class="{{session('active_tab') && session('active_tab') == 'settings' ? 'active': ''}}"><a href="#settings" data-toggle="tab">Учетные данные</a></li>
                    <li class="{{session('active_tab') && session('active_tab') == 'password' ? 'active': ''}}"><a href="#password" data-toggle="tab">Смена пароля</a></li>
                </ul>
                <div class="tab-content">
                    <div class="{{(session('active_tab') && session('active_tab') == 'info') || !session('active_tab') ? 'active': ''}} tab-pane" id="info">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4>Информация о пользователе</h4>
                                <div class="info-mb-5"><strong>ФИО:</strong> {{$user->last_name}} {{$user->first_name}} {{$user->second_name}}</div>
                                <div class="info-mb-5"><strong>Роль:</strong> {{$user->role}}</div>
                                <div class="info-mb-5"><strong>Зарегистрирован:</strong> {{$user->created_at_short}}</div>
                                <hr>
                                <h4>Контактные данные</h4>
                                <div class="info-mb-5">
                                    <strong>Email:</strong>
                                    @if($user->email)
                                        <a href="mailto:{{$user->email}}"> {{$user->email}}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="info-mb-5">
                                    <strong>Дата подтверждения email:</strong>
                                    @if($user->email_verified_at)
                                        {{humanize_date($user->email_verified_at, 'd.m.Y H:i')}}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="info-mb-5">
                                    <strong>Телефон:</strong>
                                    @if($user->phone)
                                        <a href="mailto:{{$user->phone}}"> {{$user->phone}}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="info-mb-5">
                                    <strong>Дата подтверждения телефона:</strong>
                                    @if($user->phone_verified_at)
                                        {{humanize_date($user->phone_verified_at, 'd.m.Y H:i')}}
                                    @else
                                        —
                                    @endif
                                </div>

                                <br>
                                <br>
                            </div>
                        </div>
                    </div>
                    <div class="{{session('active_tab') && session('active_tab') == 'settings' ? 'active': ''}} tab-pane" id="settings">
                        @if (session('email_changed'))
                            <div class="row">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="alert alert-info alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h4><i class="icon fa fa-info"></i> Внимание вы сменили email!</h4>
                                        Требуется подтверждение что вы являетесь владельцем почтового ящика, письмо со ссылкой отправлено на <b>{{$user->email}}</b><br>
                                        Если этого письма нет во «Входящих», пожалуйста, проверьте «Спам». <br>
                                        Если по какой-то причине вы не получили письмо активации, <a href="#">свяжитесь с нами</a>, и мы сделаем все возможное что бы помочь вам.
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (session('phone_changed'))
                            <div class="row">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="alert alert-info alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h4><i class="icon fa fa-info"></i> Внимание вы сменили телефон!</h4>
                                        Требуется подтверждение что вы являетесь владельцем телефонного номера, перейдите по
                                        <a href="#">ссылке</a> для отправки sms с кодом.
                                    </div>
                                </div>
                            </div>
                        @endif
                        <form class="form-horizontal" method="post" action="{{ route('profile.user.update', $user) }}" autocomplete="off">
                            @csrf
                            @method('put')
                            <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
                                <label for="inputFirstName" class="col-sm-2 control-label">Имя</label>

                                <div class="col-sm-10">
                                    <input type="text"
                                           class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                                           id="inputFirstName"
                                           name="first_name"
                                           placeholder="Введите ваше имя"
                                           value="{{ old('first_name') ?? $user->first_name ?? ''}}">
                                    <div class="help-block with-errors">
                                        @if ($errors->has('first_name')){{ $errors->first('first_name') }}@endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('second_name') ? ' has-error' : '' }}">
                                <label for="inputSecondName" class="col-sm-2 control-label">Отчество</label>

                                <div class="col-sm-10">
                                    <input type="text"
                                           class="form-control{{ $errors->has('second_name') ? ' is-invalid' : '' }}"
                                           id="inputSecondName"
                                           name="second_name"
                                           placeholder="Введите ваше отчество"
                                           value="{{ old('second_name') ?? $user->second_name ?? ''}}">
                                    <div class="help-block with-errors">
                                        @if ($errors->has('second_name')){{ $errors->first('second_name') }}@endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
                                <label for="inputLastName" class="col-sm-2 control-label">Фамилия</label>

                                <div class="col-sm-10">
                                    <input type="text"
                                           class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                                           id="inputLastName"
                                           name="last_name"
                                           placeholder="Введите вашу фамилию"
                                           value="{{ old('last_name') ?? $user->last_name ?? ''}}">
                                    <div class="help-block with-errors">
                                        @if ($errors->has('last_name')){{ $errors->first('last_name') }}@endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                                <label for="inputPhone" class="col-sm-2 control-label">Телефон</label>

                                <div class="col-sm-10">
                                    @if ($user->phone)
                                        <div class="input-group">
                                            @endif
                                            <input type="text"
                                                   class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                                   id="inputPhone"
                                                   name="phone"
                                                   placeholder="Введите ваш телефон"
                                                   value="{{ old('phone') ?? $user->phone ?? ''}}">
                                            @if ($user->phone)
                                                <div class="input-group-btn" title="{{$user->phone_verified_at ? 'Телефон подтвержден':'Телефон не подтвержден'}}">
                                                    <button type="button" class="btn {{$user->phone_verified_at ? 'btn-success':'btn-danger'}}"><i class="fa fa-check text-white"></i></button>
                                                </div>
                                            @endif
                                            @if ($user->phone)
                                        </div>
                                    @endif
                                    <div class="help-block with-errors">
                                        @if ($errors->has('phone')){{ $errors->first('phone') }}@endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="inputEmail" class="col-sm-2 control-label">Email</label>

                                <div class="col-sm-10">
                                    @if ($user->email)
                                        <div class="input-group">
                                            @endif
                                            <input type="email"
                                                   class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                                   id="inputEmail"
                                                   name="email"
                                                   placeholder="Введите ваш email"
                                                   value="{{ old('email') ?? $user->email ?? ''}}">
                                            @if ($user->email)
                                                <div class="input-group-btn" title="{{$user->email_verified_at ? 'Email подтвержден':'Email не подтвержден'}}">
                                                    <button type="button" class="btn {{$user->email_verified_at ? 'btn-success':'btn-danger'}}"><i class="fa fa-check text-white"></i></button>
                                                </div>
                                            @endif
                                            @if ($user->email)
                                        </div>
                                    @endif
                                    <div class="help-block with-errors">
                                        @if ($errors->has('email')){{ $errors->first('email') }}@endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="{{session('active_tab') && session('active_tab') == 'password' ? 'active': ''}} tab-pane" id="password">
                        <form class="form-horizontal" method="post" action="{{ route('profile.password.update', $user) }}">
                            @csrf
                            @method('put')
                            <div class="form-group{{ $errors->has('old_password') ? ' has-error' : '' }}">
                                <label for="inputOldPassword" class="col-sm-2 control-label">Текуший пароль</label>

                                <div class="col-sm-10">
                                    <input type="password"
                                           class="form-control{{ $errors->has('old_password') ? ' is-invalid' : '' }}"
                                           id="inputOldPassword"
                                           name="old_password"
                                           placeholder="Введите текуший пароль">
                                    <div class="help-block with-errors">
                                        @if ($errors->has('old_password')){{ $errors->first('old_password') }}@endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="inputPassword" class="col-sm-2 control-label">Новый пароль</label>

                                <div class="col-sm-10">
                                    <input type="password"
                                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                           id="inputPassword"
                                           name="password"
                                           placeholder="Введите новый пароль">
                                    <div class="help-block with-errors">
                                        @if ($errors->has('password')){{ $errors->first('password') }}@endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="inputСonfirmationPassword" class="col-sm-2 control-label">Подтвердите пароль</label>

                                <div class="col-sm-10">
                                    <input type="password"
                                           class="form-control"
                                           id="inputСonfirmationPassword"
                                           name="password_confirmation"
                                           placeholder="Введите подтверждение пароля">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary">Сменить пароль</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>
            <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
    </div>
@endsection