<?php

use Illuminate\Support\Carbon;

/**
 * Format: custom
 * @param Carbon $date
 * @param string $format
 * @return string
 */
function humanize_date(Carbon $date, string $format = 'd F Y, H:i'): string
{
    return $date->format($format);
}

/**
 * Format:  1 day ago
 * @param $value
 * @return string
 */
function diffForHumans($value)
{
    $date = new \Carbon\Carbon($value);
    return $date->diffForHumans();
}