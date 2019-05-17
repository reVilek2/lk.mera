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

}
catch (\DaveJamesMiller\Breadcrumbs\Exceptions\DuplicateBreadcrumbException $e) {
    Log::error($e->getMessage());
}