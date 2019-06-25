<?php

namespace App\Http\Controllers;

use App\Services\Page;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        Page::setTitle('Оплата услуг | MeraCapital');
        Page::setDescription('Оплата услуг');

        return view('finances.index');
    }
}
