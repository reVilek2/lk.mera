@extends('layouts.app')

@section('content')
    @include('layouts/header')
    <div class="content modal-page">
        <div class="container">
            <div class="modal-topic">
                @include('components/breadcrumbs', [
                    'theme' => 'dark-theme',
                    'breadcrumbs' => [[
                        'name' => 'Home',
                        'link' => route('home'),
                    ],[
                        'name' => 'Sign Up',
                    ]]
                ])
            </div>
            <div class="modal-inline">
                <h1 class="page-header">Sign Up</h1>
                <div class="modal">
                    <div class="auth-modal__tabs">
                        <a class="auth-modal__tabs-link" href="{{ route('login') }}">Sign In</a>
                        <span class="auth-modal__tabs-link active">Sign Up</span>
                    </div>
                    <div class="modal-form">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="form-group">
                                <input type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Email" required>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        {{ $errors->first('email') }}
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="Password" required>
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        {{ $errors->first('password') }}
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Password confirmation" required>
                            </div>
                            <button type="submit" class="button button_blue">Sign up</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts/footer')
@endsection
