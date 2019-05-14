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
                        'name' => 'Lost password',
                    ]]
                ])
            </div>
            <div class="modal-inline">
                <h1 class="page-header">Lost Password</h1>
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div class="modal">
                    <div class="modal-form">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="form-group">
                                <input type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="E-Mail Address" value="{{ old('email') }}" required>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        {{ $errors->first('email') }}
                                    </span>
                                @endif
                            </div>
                            <button type="submit" class="button button_blue">Send Reset Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts/footer')
@endsection
