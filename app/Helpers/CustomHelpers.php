<?php

/* Set active class
    -------------------------------------------------------- */
function set_active($path, $active = 'active') {
    return call_user_func_array('Request::is', (array)$path) ? $active : '';
}

function set_active_route($path, $active = 'active') {
    return call_user_func_array('Request::routeIs', (array)$path) ? $active : '';
}

function splitCamelCase($string) {
    $pattern = '/(.*?[a-z]{1})([A-Z]{1}.*?)/';
    $replace = '${1} ${2}';
    return preg_replace($pattern, $replace, $string);
}
