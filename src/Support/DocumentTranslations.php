<?php namespace Seiger\sLang\Support;

use Dom\Attr;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Dom\XPath;
use Illuminate\Support\HtmlString;

/**
 * Render dictionary values in their HTML context after Evolution finishes parsing a page.
 *
 * @since 2.1.0
 */
final class DocumentTranslations
{
    public const LEGACY_MARKER = '~@lang\(\s*(["\'])(.*?)\1\s*\)~s';

    private const HTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';
    private const INLINE_TAGS = ['a', 'b', 'br', 'code', 'em', 'i', 's', 'small', 'span', 'strong', 'sub', 'sup', 'u'];
    private const DROP_SUBTREE = [
        'embed', 'iframe', 'math', 'noembed', 'noframes', 'noscript', 'object',
        'plaintext', 'script', 'style', 'svg', 'template', 'xmp',
    ];
    private const TEXT_ATTRIBUTES = ['alt', 'aria-description', 'aria-label', 'aria-valuetext', 'placeholder', 'title'];

    /**
     * Replace literal legacy markers without reserializing the document.
     *
     * A key can render as HTML in the body and as text in an attribute.
     * Markers in other contexts receive no translated value.
     *
     * @param array<string, mixed> $dictionary Values loaded from the selected locale's JSON file.
     * @since 2.1.0
     */
    public static function replace(string $output, array $dictionary): string
    {
        if (!str_contains($output, '@lang(')) {
            return $output;
        }

        $values = [];
        $prefix = 'slangtranslation' . bin2hex(random_bytes(12)) . 'x';
        $marked = preg_replace_callback(self::LEGACY_MARKER, static function (array $match) use ($prefix, &$values): string {
            $marker = $prefix . count($values) . 'z';
            $values[$marker] = $match[2];

            return $marker;
        }, $output);
        if ($marked === null || $values === []) {
            return $output;
        }

        $document = HTMLDocument::createFromString($marked, LIBXML_NOERROR, 'UTF-8');
        $nodes = (new XPath($document))->query('//text() | //@*');
        $pattern = '~' . $prefix . '[0-9]+z~';
        $replacements = array_fill_keys(array_keys($values), '');
        $rendered = [];

        foreach ($nodes as $node) {
            if (!preg_match_all($pattern, $node->nodeValue ?? '', $matches)) {
                continue;
            }

            $context = self::context($node);
            if ($context === null) {
                continue;
            }

            foreach ($matches[0] as $marker) {
                $key = $values[$marker];
                if (!array_key_exists($key, $rendered)) {
                    $value = $dictionary[$key] ?? '';
                    $rendered[$key] = self::html(is_scalar($value) ? (string) $value : '')->toHtml();
                }

                $replacements[$marker] = $context === 'html'
                    ? $rendered[$key]
                    : self::text($rendered[$key], $context === 'attribute');
            }
        }

        return strtr($marked, $replacements);
    }

    /**
     * Return balanced inline HTML for a caller that chose an HTML body context.
     *
     * @since 2.1.0
     */
    public static function html(string $value): HtmlString
    {
        if (!str_contains($value, '<')) {
            return new HtmlString(self::escape(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        }

        $document = HTMLDocument::createFromString('<!doctype html><body>' . $value, LIBXML_NOERROR, 'UTF-8');
        $body = $document->getElementsByTagName('body')->item(0);

        return new HtmlString($body === null ? '' : self::renderChildren($body));
    }

    /**
     * Classify text and attribute nodes using their final HTML ancestors.
     *
     * @since 2.1.0
     */
    private static function context(Node $node): ?string
    {
        $parent = $node instanceof Attr ? $node->ownerElement : $node->parentElement;
        $textOnly = false;

        for ($element = $parent; $element !== null; $element = $element->parentElement) {
            if ($element->namespaceURI !== self::HTML_NAMESPACE || in_array($element->localName, self::DROP_SUBTREE, true)) {
                return null;
            }
            $textOnly = $textOnly || in_array($element->localName, ['a', 'button', 'option', 'select', 'textarea', 'title'], true);
        }

        if ($node instanceof Attr) {
            $name = $node->localName;
            $allowed = in_array($name, self::TEXT_ATTRIBUTES, true) || str_starts_with($name, 'data-');

            return $allowed ? 'attribute' : null;
        }

        if (!$node instanceof Text || $parent === null) {
            return null;
        }

        return $textOnly ? 'text' : 'html';
    }

    /**
     * Serialize sanitized children without copying stored attributes or markup verbatim.
     *
     * @since 2.1.0
     */
    private static function renderChildren(Node $parent): string
    {
        $output = '';
        foreach ($parent->childNodes as $child) {
            if ($child instanceof Text) {
                $output .= self::escape($child->data);
                continue;
            }
            if (!$child instanceof Element || $child->namespaceURI !== self::HTML_NAMESPACE
                || in_array($child->localName, self::DROP_SUBTREE, true)) {
                continue;
            }

            $tag = $child->localName;
            $children = self::renderChildren($child);
            if (!in_array($tag, self::INLINE_TAGS, true)) {
                $output .= $children;
                continue;
            }
            if ($tag === 'br') {
                $output .= '<br>';
                continue;
            }

            $href = $tag === 'a' ? self::linkTarget($child->getAttribute('href') ?? '') : '';
            $attribute = $href === '' ? '' : ' href="' . self::escape($href) . '"';
            $output .= "<{$tag}{$attribute}>{$children}</{$tag}>";
        }

        return $output;
    }

    /**
     * Accept relative links and explicitly supported web and contact schemes.
     *
     * @since 2.1.0
     */
    private static function linkTarget(string $value): string
    {
        $value = trim($value);
        if (preg_match('~[\x00-\x1f\x7f]~', $value)
            || (preg_match('~^[a-z][a-z0-9+.-]*:~i', $value) && !preg_match('~^(https?|mailto|tel):~i', $value))) {
            return '';
        }

        return $value;
    }

    /**
     * Convert sanitized HTML to text and keep unquoted text attributes a single token.
     *
     * @since 2.1.0
     */
    private static function text(string $html, bool $attribute): string
    {
        $text = strip_tags(str_replace('<br>', ' ', $html));
        if (!$attribute) {
            return $text;
        }

        return strtr($text, [
            " " => '&#32;', "\t" => '&#9;', "\n" => '&#10;', "\r" => '&#13;',
            "\f" => '&#12;', '=' => '&#61;', "\x60" => '&#96;',
        ]);
    }

    /**
     * Encode literal text for HTML and for quoted attribute values.
     *
     * @since 2.1.0
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
