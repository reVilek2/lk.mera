<?php

namespace App\Services;

use Illuminate\Support\Facades\View;

class Page
{
    const APP_NAME = APP_NAME;

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
     * Set titles
     *
     * @param string $title
     */
    public static function setTitle($title = '')
    {
        $app_name =  splitCamelCase(config('app.name'));
        if(!$title){
            self::setHeadTitle($app_name);
            self::setPageTitle($app_name);
        }else{
            self::setHeadTitle("{$title} | {$app_name}");
            self::setPageTitle($title);
        }

    }

    /**
     * Set title
     *
     * @param string $title
     */
    public static function setPageTitle($title = '')
    {
        View::share('pageTitle', $title);
    }

    /**
     * Set mobile title
     *
     * @param string $title
     */
    public static function setHeadTitle($title = '')
    {
        View::share('headTitle', $title);
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
