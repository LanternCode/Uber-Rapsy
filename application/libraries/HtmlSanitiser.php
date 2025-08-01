<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A service sanitising user inputs.
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
    }

    /**
     * Get a purifier by type. Create and cache it if not already created.
     *
     * @param string $type 'rich' or 'text'
     * @return HTMLPurifier
     */
    protected function getPurifier(string $type = 'rich'): HTMLPurifier
    {
        if (isset($this->purifiers[$type]))
            return $this->purifiers[$type];

        $config = HTMLPurifier_Config::createDefault();
        switch ($type)
        {
            case 'text':
                $config->set('HTML.Allowed', '');
                break;

            case 'rich':
            default:
                $config->set('HTML.SafeIframe', true);
                $config->set('URI.SafeIframeRegexp', '#^https?://(www\.youtube\.com/embed/|player\.vimeo\.com/video/)#');
                $config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href|target],ul,ol,li,br,span[style],div[style],img[src|alt|width|height],h1,h2,h3,h4,h5,h6,blockquote,pre,code');
                $config->set('CSS.AllowedProperties', ['font', 'font-size', 'font-weight', 'text-decoration', 'color', 'background-color', 'text-align']);
                $config->set('AutoFormat.AutoParagraph', false);
                $config->set('AutoFormat.RemoveEmpty', true);
                break;
        }

        $this->purifiers[$type] = new HTMLPurifier($config);
        return $this->purifiers[$type];
    }

    /**
     * Purify content according to a specific config type.
     *
     * @param string $dirty_html
     * @param string $type 'rich' or 'text' (default)
     * @return string
     */
    public function purify(string $dirty_html, string $type = 'text'): string
    {
        return $this->getPurifier($type)->purify($dirty_html);
    }
}
