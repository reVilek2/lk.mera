<?php

use App\Services\MobileDetectManager;

if (!function_exists('is_mobile')) {
    function is_mobile()
    {
        $mobileDetect = app()->make(MobileDetectManager::class);
        return $mobileDetect->is_mobile();
    }
}

if (!function_exists('is_tablet')) {
    function is_tablet()
    {
        $mobileDetect = app()->make(MobileDetectManager::class);
        return $mobileDetect->is_tablet();
    }
}

if (!function_exists('is_desktop')) {
    function is_desktop()
    {
        $mobileDetect = app()->make(MobileDetectManager::class);
        return $mobileDetect->is_desktop();
    }
}

if (!function_exists('layout_type')) {
    function layout_type()
    {
        $mobileDetect = app()->make(MobileDetectManager::class);
        return $mobileDetect->getLayoutType();
    }
}
