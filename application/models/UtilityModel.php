<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class which holds utility methods.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class UtilityModel extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
    }

    /**
     * Trims trailing zeroes from a given number.
     *
     * @param float $nbr number to trim
     * @return float trimmed number
     */
    function TrimTrailingZeroes(float $nbr): float
    {
        return str_contains($nbr, '.') ? rtrim(rtrim($nbr,'0'),'.') : $nbr;
    }

    /**
     * Returns whether a number is in a given range or not
     *
     * @param float $value number to check
     * @param float $min lower boundary of the range
     * @param float $max upper boundary of the range
     * @return bool true if number is in range, false if not
     */
    function InRange(float $value, float $min, float $max): bool
    {
        return ($value >= $min && $value <= $max);
    }
}
