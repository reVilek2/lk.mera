<?php
try {
    // home
    Breadcrumbs::for('home', function ($trail) {
        $trail->push('Главная', route('profile'), ['fa' => 'fa-dashboard']);
    });
    // profile
    Breadcrumbs::for('profile', function ($trail) {
        $trail->parent('home');
        $trail->push('Профиль', route('profile'), ['fa' => 'fa-user']);
    });
    // users
    Breadcrumbs::for('users', function ($trail) {
        $trail->parent('home');
        $trail->push('Пользователи', route('users'), ['fa' => 'fa-users']);
    });
    Breadcrumbs::for('user', function ($trail) {
        $trail->parent('users');
        $trail->push('Пользователь', '', ['fa' => 'fa-user']);
    });
    Breadcrumbs::for('documents', function ($trail) {
        $trail->parent('home');
        $trail->push('Документы', '', ['fa' => 'fa-file-pdf-o']);
    });
}
catch (\DaveJamesMiller\Breadcrumbs\Exceptions\DuplicateBreadcrumbException $e) {
    Log::error($e->getMessage());
}