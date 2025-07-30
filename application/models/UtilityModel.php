<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model responsible for providing utility methods.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class UtilityModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
    }

    /**
     * Trim trailing zeroes from a given number.
     * If a non-number is passed, return 0.
     *
     * @param mixed $number
     * @return float
     */
    public function trimTrailingZeroes(mixed $number): float
    {
        if (filter_var($number, FILTER_VALIDATE_FLOAT))
            return str_contains($number, '.') ? rtrim(rtrim($number,'0'),'.') : $number;
        else return 0;
    }

    /**
     * Check whether a number is in the given range.
     *
     * @param float $value the number to check
     * @param float $min lower boundary of the range
     * @param float $max upper boundary of the range
     * @return bool
     */
    public function inRange(float $value, float $min, float $max): bool
    {
        return ($value >= $min && $value <= $max);
    }

    /**
     * Playlist links on YouTube usually have two GET parameters: 'list' and 'index'.
     * The 'list' parameter is the actual playlist id.
     * Index refers to the currently watched video and always occurs after 'list'.
     * If index is found, mark its position in the string and fetch the list id in front of it.
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
     * YouTube video link can take one of many forms:
     * https://www.youtube.com/watch?v=ID
     * https://youtu.be/ID
     * https://youtu.be/ID?si=...
     * https://www.youtube.com/watch?v=ID&index=...
     *
     * Match the link variant and returns the video id.
     *
     * @param string $link a YT video link
     * @return string YT video ID
     */
    public function extractVideoIdFromLink(string $link): string
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com.*[?&]v=)([a-zA-Z0-9_-]{11})/', $link, $matches))
            return $matches[1];
        else
            return $link;
    }
}