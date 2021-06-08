<?php


namespace App\Http\Controllers;


use App\Services\Page;

class StaticPagesController extends Controller
{
    function terms()
    {
        Page::setTitle('Пользовательское соглашение');

        return view('static.terms');
    }

    function services()
    {
        Page::setTitle('Услуги и правила оплаты');

        return view('static.services');
    }

    function organizationDetails()
    {
        Page::setTitle('Реквизиты организации');

        return view('static.organization_details');
    }
}