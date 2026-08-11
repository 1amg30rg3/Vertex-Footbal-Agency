<?php

namespace App\Support;

use Illuminate\Support\Str;

class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'mark', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'span', 'div',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'href', 'target', 'rel', 'src', 'alt', 'title', 'colspan', 'rowspan',
        'style', 'class', 'width', 'height',
    ];

    private const ALLOWED_STYLES = ['text-align'];

    public static function clean(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        if (trim(strip_tags($html)) === '' && ! str_contains($html, '<img')) {
            return null;
        }

        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return e(strip_tags($html));
        }

        static::scrub($document->documentElement ?? $document);

        $body = $document->getElementsByTagName('body')->item(0);

        if ($body === null) {
            return null;
        }

        $out = '';

        foreach ($body->childNodes as $child) {
            $out .= $document->saveHTML($child);
        }

        return trim($out) ?: null;
    }

    protected static function scrub(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes ?? []) as $child) {
            if ($child instanceof \DOMComment) {
                $child->parentNode?->removeChild($child);

                continue;
            }

            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                static::scrub($child);
                static::unwrap($child);

                continue;
            }

            static::scrubAttributes($child, $tag);
            static::scrub($child);
        }
    }

    protected static function scrubAttributes(\DOMElement $element, string $tag): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = $attribute->nodeValue ?? '';

            if (! in_array($name, self::ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! static::safeUrl($value, $name)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if ($name === 'style') {
                $safe = static::filterStyle($value);
                $safe === '' ? $element->removeAttribute('style') : $element->setAttribute('style', $safe);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    protected static function safeUrl(string $url, string $attribute): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '') {
            return ! preg_match('/^\s*[a-z0-9.+-]+\s*:/i', $url);
        }

        $allowed = $attribute === 'src'
            ? ['http', 'https', 'data']
            : ['http', 'https', 'mailto', 'tel'];

        if (! in_array($scheme, $allowed, true)) {
            return false;
        }

        if ($scheme === 'data') {
            return (bool) preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $url);
        }

        return true;
    }

    protected static function filterStyle(string $style): string
    {
        return collect(explode(';', $style))
            ->map(fn (string $rule) => trim($rule))
            ->filter()
            ->filter(function (string $rule) {
                $property = strtolower(trim(explode(':', $rule)[0] ?? ''));

                return in_array($property, self::ALLOWED_STYLES, true)
                    && ! preg_match('/url\s*\(|expression|javascript:/i', $rule);
            })
            ->implode('; ');
    }

    protected static function unwrap(\DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    public static function cleanMap(mixed $map): array
    {
        return collect(Locales::normalizeMap($map, trim: false))
            ->map(fn (?string $value) => static::clean($value))
            ->all();
    }

    public static function excerpt(?string $html, int $length = 160): string
    {
        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return Str::limit($text, $length);
    }
}
