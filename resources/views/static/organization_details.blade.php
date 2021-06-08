@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>$pageTitle,
        'description'=>''
     ])
@endsection

@section('content')
    <table class="table">
        <tbody>
        <tr>
            <td>
                <p>Название компании</p>
            </td>
            <td>
                <p>ООО &laquo;Мера Капитал ВэлсМэнэджмент&raquo;</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>ОГРН/ИНН</p>
            </td>
            <td>
                <p>1157847135665/7811191140</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Телефон:</p>
            </td>
            <td>
                <p>8&nbsp;800&nbsp;250-78-77</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Режим работы:</p>
            </td>
            <td>
                <p>Пн.-Пт. 10:00 - 18:00</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Почтовый адрес:</p>
            </td>
            <td>
                <p>191028, Санкт-Петербург, ул. Фурштатская, дом 24, литера А</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Юридический адрес:</p>
            </td>
            <td>
                <p>191028, Санкт-Петербург, ул. Фурштатская, дом 24, литера А</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>e-mail:</p>
            </td>
            <td>
                <p>welcome@mera-capital.com</p>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
