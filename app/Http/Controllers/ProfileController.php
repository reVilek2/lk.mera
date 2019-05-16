<?php

namespace App\Http\Controllers;

use App\Services\Page;

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