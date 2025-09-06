<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('songInflection')) {
    /**
     * Return the correct inflection of the word 'song' in the polish language.
     *
     * @param int $number
     * @return string
     */
    function songInflection(int $number): string
    {
        $lastDigit = $number % 10;
        $lastTwo = $number % 100;

        if ($number === 1)
            return 'utwór';
        elseif ($lastDigit >= 2 && $lastDigit <= 4 && !($lastTwo >= 12 && $lastTwo <= 14))
            return 'utwory';
        else return 'utworów';
    }
}