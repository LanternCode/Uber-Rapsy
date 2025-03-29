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
     * If a non-number is passed, return 0.
     *
     * @param mixed $number the number to trim
     * @return float trimmed number
     */
    public function trimTrailingZeroes(mixed $number): float
    {
        if (filter_var($number, FILTER_VALIDATE_FLOAT))
            return str_contains($number, '.') ? rtrim(rtrim($number,'0'),'.') : $number;
        else return 0;
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

    /**
     * YT playlist link usually has two get parameters 'list' and 'index'
     * The list parameter is the actual playlist id
     * Index refers to an item currently being watched and always occurs after list
     * If index is found, mark its position in the string and fetch the id before it
     *
     * @param string $link link to a playlist on YT
     * @return string playlist id on YT
     */
    public function extractPlaylistIdFromLink(string $link): string
    {
        $listPos = strpos($link, "list=");
        if ($listPos > 0) {
            $indexPos = strpos($link, "&index=");
            $indexLength = strlen(substr($link, $indexPos));
            if ($indexPos > 0)
                return substr($link, $listPos+5, -$indexLength);
            else
                return substr($link, $listPos+5);
        }
        return $link;
    }

    /**
     * YouTube videos can be accessed in multiple link variants:
     * //https://www.youtube.com/watch?v=ID
     * //https://youtu.be/ID
     * //https://youtu.be/ID?si=...
     * //https://www.youtube.com/watch?v=ID&index=...
     *
     * This function matches the link variant and returns the video id.
     *
     * @param string $link a YT video link
     * @return string YT video ID
     */
    public function extractVideoIdFromLink(string $link): string
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com.*[?&]v=)([a-zA-Z0-9_-]{11})/', $link, $matches)) {
            return $matches[1];
        }
        else
            return $link;
    }
}
