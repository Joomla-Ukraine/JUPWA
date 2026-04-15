<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Classes
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Classes;

/**
 * Compress HTML
 *
 * This is a heavy regex-based removal of whitespace, unnecessary comments and
 * tokens. IE conditional comments are preserved. There are also options to have
 * STYLE and SCRIPT blocks compressed by callback functions.
 *
 * A test suite is available.
 *
 * @since   1.0
 * @author  Stephen Clay <steve@mrclay.org>
 * @package Minify
 */
#[AllowDynamicProperties]
class Minify
{
    /** @var bool */
    private bool $_jsCleanComments = true;

    /** @var string */
    private string $_html;

    /** @var bool|null */
    private ?bool $_isXhtml = null;

    /** @var string */
    private string $_replacementHash = '';

    /** @var array */
    private array $_placeholders = [];

    /** @var callable|null */
    private $_cssMinifier;

    /** @var callable|null */
    private $_jsMinifier;

    /**
     * @param string $html
     *
     * @param array $options
     *
     * @return string
     * @since 1.0
     */
    public static function minify(string $html, array $options = []): string
    {
        return (new self($html, $options))->process();
    }

    /**
     * Create a minifier object
     *
     * @param string $html
     *
     * @param array $options
     *
     * 'cssMinifier' : (optional) callback function to process content of STYLE
     * elements.
     *
     * 'jsMinifier' : (optional) callback function to process content of SCRIPT
     * elements. Note: the type attribute is ignored.
     *
     * 'jsCleanComments' : (optional) whether to remove HTML comments beginning and end of script block
     *
     * 'xhtml' : (optional boolean) should content be treated as XHTML1.0? If
     * unset, minify will sniff for an XHTML doctype.
     *
     * @since 1.0
     */
    public function __construct(string $html, array $options = [])
    {
        $this->_html = str_replace("\r\n", "\n", trim($html));

        $this->_isXhtml = $options['xhtml'] ?? null;

        if (isset($options['cssMinifier']) && is_callable($options['cssMinifier'])) {
            $this->_cssMinifier = $options['cssMinifier'];
        }

        if (isset($options['jsMinifier']) && is_callable($options['jsMinifier'])) {
            $this->_jsMinifier = $options['jsMinifier'];
        }

        if (isset($options['jsCleanComments'])) {
            $this->_jsCleanComments = (bool)$options['jsCleanComments'];
        }
    }

    /**
     * Minify the markeup given in the constructor
     *
     * @return string
     * @since 1.0
     */
    public function process(): string
    {
        if ($this->_isXhtml === null) {
            $this->_isXhtml = str_contains($this->_html, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML');
        }

        $requestTime = $_SERVER['REQUEST_TIME'] ?? time();
        $this->_replacementHash = 'MINIFYHTML'.md5((string)$requestTime);
        $this->_placeholders = [];

        $this->_html = preg_replace_callback('/(\\s*)<script(\\b[^>]*?>)([\\s\\S]*?)<\\/script>(\\s*)/iu', [
            $this,
            '_removeScriptCB',
        ], $this->_html);

        $this->_html = preg_replace_callback('/\\s*<style(\\b[^>]*>)([\\s\\S]*?)<\\/style>\\s*/iu', [
            $this,
            '_removeStyleCB',
        ], $this->_html);

        $this->_html = preg_replace_callback('/<!--([\\s\\S]*?)-->/u', [
            $this,
            '_commentCB',
        ], $this->_html);

        $this->_html = preg_replace_callback('/\\s*<pre(\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/iu', [
            $this,
            '_removePreCB',
        ], $this->_html);

        $this->_html = preg_replace_callback('/\\s*<textarea(\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/iu', [
            $this,
            '_removeTextareaCB',
        ], $this->_html);

        $this->_html = preg_replace('/^\\s+|\\s+$/mu', '', $this->_html);

        $blockElements = 'area|article|aside|base(?:font)?|blockquote|body|canvas|caption|center|col(?:group)?|dd|dir|div|dl|dt|fieldset|figcaption|figure|footer|form|frame(?:set)?|h[1-6]|head|header|hgroup|hr|html|legend|li|link|main|map|menu|meta|nav|ol|opt(?:group|ion)|output|p|param|section|t(?:able|body|head|d|h|r|foot|itle)|ul|video';
        $this->_html = preg_replace('/\\s+(<\\/?(?:'.$blockElements.')\\b[^>]*>)/iu', '$1', $this->_html);

        $this->_html = preg_replace('/>(\\s(?:\\s*))?([^<]+)(\\s(?:\\s*))?</u', '>$1$2$3<', $this->_html);

        $this->_html = preg_replace('/(<[a-z\\-]+)\\s+([^>]+>)/iu', "$1\n$2", $this->_html);

        if (!empty($this->_placeholders)) {
            $this->_html = str_replace(
                array_keys($this->_placeholders),
                array_values($this->_placeholders),
                $this->_html
            );

            $this->_html = str_replace(
                array_keys($this->_placeholders),
                array_values($this->_placeholders),
                $this->_html
            );
        }

        return $this->_html;
    }

    protected function _commentCB(array $m): string
    {
        if (str_starts_with($m[1], '[') || str_contains($m[1], '<![')) {
            return $m[0];
        }

        return '';
    }

    protected function _reservePlace(string $content): string
    {
        $placeholder = '%'.$this->_replacementHash.count($this->_placeholders).'%';
        $this->_placeholders[$placeholder] = $content;

        return $placeholder;
    }

    protected function _removePreCB(array $m): string
    {
        return $this->_reservePlace("<pre{$m[1]}");
    }

    protected function _removeTextareaCB(array $m): string
    {
        return $this->_reservePlace("<textarea{$m[1]}");
    }

    protected function _removeStyleCB(array $m): string
    {
        $openStyle = "<style{$m[1]}";
        $css = $m[2];
        $css = preg_replace('/(?:^\\s*<!--|-->\\s*$)/u', '', $css) ?? '';
        $css = $this->_removeCdata($css);

        $css = ($this->_cssMinifier) ? ($this->_cssMinifier)($css) : trim($css);

        return $this->_reservePlace(
            $this->_needsCdata($css) ? "$openStyle/*<![CDATA[*/$css/*]]>*/</style>" : "$openStyle$css</style>"
        );
    }

    protected function _removeScriptCB(array $m): string
    {
        $openScript = "<script{$m[2]}";
        $js = $m[3];

        $ws1 = ($m[1] === '') ? '' : ' ';
        $ws2 = ($m[4] === '') ? '' : ' ';

        if ($this->_jsCleanComments) {
            $js = preg_replace('/(?:^\\s*<!--\\s*|\\s*(?:\\/\\/)?\\s*-->\\s*$)/u', '', $js) ?? '';
        }

        $js = $this->_removeCdata($js);

        $js = ($this->_jsMinifier) ? ($this->_jsMinifier)($js) : trim($js);

        return $this->_reservePlace(
            $this->_needsCdata(
                $js
            ) ? "$ws1$openScript/*<![CDATA[*/$js/*]]>*/</script>$ws2" : "$ws1$openScript$js</script>$ws2"
        );
    }

    protected function _removeCdata(string $str): string
    {
        if (str_contains($str, '<![CDATA[')) {
            return str_replace(['<![CDATA[', ']]>'], '', $str);
        }

        return $str;
    }

    protected function _needsCdata(string $str): bool
    {
        return ($this->_isXhtml && preg_match('/(?:[<&]|\\-\\-|\\]\\]>)/u', $str));
    }
}