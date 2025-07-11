<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A service dedicated to sanitising user input.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class HtmlSanitiser
{
    protected CI_Controller $ci;
    protected HTMLPurifier $purifier;

    public function __construct()
    {
        $this->ci =& get_instance();

        require_once APPPATH . 'third_party/htmlsanitiser/HTMLPurifier.auto.php';

        $config = HTMLPurifier_Config::createDefault();

        //Customize allowed tags, attributes, styles
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '#^https?://(www\.youtube\.com/embed/|player\.vimeo\.com/video/)#');
        $config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href|target],ul,ol,li,br,span[style],div[style],img[src|alt|width|height],h1,h2,h3,h4,h5,h6,blockquote,pre,code');
        $config->set('CSS.AllowedProperties', ['font', 'font-size', 'font-weight', 'text-decoration', 'color', 'background-color', 'text-align']);
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.RemoveEmpty', true);

        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Purify rich text (that contains html).
     *
     * @param $dirty_html
     * @return string
     */
    public function purify($dirty_html): string
    {
        return $this->purifier->purify($dirty_html);
    }
}
