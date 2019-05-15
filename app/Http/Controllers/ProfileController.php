<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\Page;
use App\Services\PhoneNormalizer;
use App\Services\ValidationMessages;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Validator;

class ProfileController extends Controller
{
    /**
     * Display a home admin page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Page::setTitle('Профиль | MeraCapital');
        Page::setDescription('Страница профиля');

        return view('profile.index');
    }
}