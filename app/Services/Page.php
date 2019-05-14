<?php

namespace App\Services;

use Illuminate\Support\Facades\View;

class Page
{
    /**
     * Set page breadcrumbs
     *
     * @param array $breadcrumbs
     */
    public static function setBreadcrumbs($breadcrumbs = [])
    {
        View::share('breadcrumbs', $breadcrumbs);
    }

    /**
     * Set page title
     *
     * @param string $title
     */
    public static function setTitle($title = '')
    {
        View::share('pageTitle', $title);
    }

    /**
     * Set page description
     *
     * @param string $description
     */
    public static function setDescription($description = '')
    {
        View::share('pageDescription', $description);
    }

    /**
     * Set page description
     *
     * @param string $keywords
     */
    public static function setKeywords($keywords = '')
    {
        View::share('pageKeywords', $keywords);
    }
}