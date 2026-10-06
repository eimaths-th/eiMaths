<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

final class BlogHtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'caption', 'code', 'del', 'div', 'em',
        'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i',
        'img', 'li', 'mark', 'ol', 'p', 'pre', 's', 'span', 'strong', 'sub', 'sup',
        'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    private const DROP_WITH_CONTENT = [
        'base', 'button', 'embed', 'form', 'input', 'link', 'math', 'meta',
        'noscript', 'object', 'script', 'select', 'style', 'svg', 'template',
        'textarea',
    ];

    private const YOUTUBE_HOSTS = [
        'youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com',
    ];

    private const YOUTUBE_QUERY_PARAMETERS = [
        'controls', 'end', 'list', 'loop', 'modestbranding', 'playlist', 'rel', 'start',
    ];

    public static function Sanitize(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $source_document = new DOMDocument('1.0', 'UTF-8');
        $previous_error_state = libxml_use_internal_errors(true);

        try {
            $loaded = $source_document->loadHTML(
                '<?xml encoding="UTF-8"><div id="blog-html-root">'.$html.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_error_state);
        }

        if (! $loaded) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $root = $source_document->getElementById('blog-html-root');
        if (! $root instanceof DOMElement) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $safe_document = new DOMDocument('1.0', 'UTF-8');
        $result = '';

        foreach ($root->childNodes as $child_node) {
            $safe_node = self::SanitizeNode($child_node, $safe_document);
            if ($safe_node !== null) {
                $result .= $safe_document->saveHTML($safe_node);
            }
        }

        return $result;
    }

    private static function SanitizeNode(DOMNode $node, DOMDocument $document): ?DOMNode
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $document->createTextNode($node->nodeValue ?? '');
        }

        if (! $node instanceof DOMElement) {
            return null;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            return null;
        }

        if ($tag === 'iframe') {
            return self::SafeYoutubeIframe($node, $document);
        }

        if ($tag === 'video') {
            return self::SafeVideo($node, $document);
        }

        $safe_node = in_array($tag, self::ALLOWED_TAGS, true)
            ? $document->createElement($tag)
            : $document->createDocumentFragment();

        if ($safe_node instanceof DOMElement) {
            self::CopySafeAttributes($node, $safe_node, $tag);
        }

        foreach ($node->childNodes as $child_node) {
            $safe_child = self::SanitizeNode($child_node, $document);
            if ($safe_child !== null) {
                $safe_node->appendChild($safe_child);
            }
        }

        return $safe_node instanceof \DOMDocumentFragment && ! $safe_node->hasChildNodes()
            ? null
            : $safe_node;
    }

    private static function CopySafeAttributes(DOMElement $source, DOMElement $destination, string $tag): void
    {
        if ($tag === 'div' && $source->getAttribute('class') === 'youtube-embed') {
            $destination->setAttribute('class', 'youtube-embed');
        }

        if ($tag === 'span') {
            $safe_styles = [];
            foreach (explode(';', $source->getAttribute('style')) as $declaration) {
                if (preg_match('/^\s*(color|background-color)\s*:\s*(#[0-9a-fA-F]{3}|#[0-9a-fA-F]{6})\s*$/i', $declaration, $matches)) {
                    $safe_styles[strtolower($matches[1])] = strtolower($matches[2]);
                }
            }

            if ($safe_styles !== []) {
                $declarations = [];
                foreach ($safe_styles as $property => $color) {
                    $declarations[] = $property.':'.$color;
                }
                $destination->setAttribute('style', implode(';', $declarations));
            }
        }

        if ($tag === 'a') {
            $href = self::SafeUrl($source->getAttribute('href'), true);
            if ($href !== null) {
                $destination->setAttribute('href', $href);
            }

            if ($source->getAttribute('target') === '_blank') {
                $destination->setAttribute('target', '_blank');
                $destination->setAttribute('rel', 'noopener noreferrer');
            }

            if ($source->hasAttribute('title')) {
                $destination->setAttribute('title', $source->getAttribute('title'));
            }
        }

        if ($tag === 'img') {
            $src = self::SafeUrl($source->getAttribute('src'), false);
            if ($src !== null) {
                $destination->setAttribute('src', $src);
            }
            $destination->setAttribute('alt', $source->getAttribute('alt'));
            $destination->setAttribute('loading', 'lazy');
        }

        if (in_array($tag, ['td', 'th'], true)) {
            foreach (['colspan', 'rowspan'] as $attribute) {
                $size = $source->getAttribute($attribute);
                if (preg_match('/^[1-9][0-9]?$/', $size)) {
                    $destination->setAttribute($attribute, $size);
                }
            }
        }
    }

    private static function SafeUrl(string $url, bool $is_link): ?string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x1F\x7F\\\\]/', $url) || str_starts_with($url, '//')) {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        if (isset($parts['scheme'])) {
            $scheme = strtolower($parts['scheme']);
            if (in_array($scheme, ['https', 'http'], true) && isset($parts['host'])) {
                return $url;
            }
            if ($is_link && $scheme === 'mailto' && filter_var(substr($url, 7), FILTER_VALIDATE_EMAIL)) {
                return $url;
            }
            if ($is_link && $scheme === 'tel' && preg_match('/^\+?[0-9][0-9(). -]*$/', substr($url, 4))) {
                return $url;
            }

            return null;
        }

        return $url;
    }

    private static function SafeYoutubeIframe(DOMElement $source, DOMDocument $document): ?DOMElement
    {
        $url_parts = parse_url(trim($source->getAttribute('src')));
        if (! is_array($url_parts)
            || strtolower($url_parts['scheme'] ?? '') !== 'https'
            || ! in_array(strtolower($url_parts['host'] ?? ''), self::YOUTUBE_HOSTS, true)
            || ! preg_match('#^/embed/[A-Za-z0-9_-]+$#', $url_parts['path'] ?? '')) {
            return null;
        }

        $host = str_contains(strtolower($url_parts['host']), 'youtube-nocookie.com')
            ? 'www.youtube-nocookie.com'
            : 'www.youtube.com';
        $src = 'https://'.$host.$url_parts['path'];
        parse_str($url_parts['query'] ?? '', $query_parameters);
        $safe_query = [];

        foreach (self::YOUTUBE_QUERY_PARAMETERS as $parameter) {
            $parameter_value = $query_parameters[$parameter] ?? null;
            if (is_string($parameter_value) && preg_match('/^[A-Za-z0-9,_-]+$/', $parameter_value)) {
                $safe_query[$parameter] = $parameter_value;
            }
        }

        if ($safe_query !== []) {
            $src .= '?'.http_build_query($safe_query, '', '&', PHP_QUERY_RFC3986);
        }

        $iframe = $document->createElement('iframe');
        $iframe->setAttribute('src', $src);
        $iframe->setAttribute('title', trim($source->getAttribute('title')) ?: 'YouTube video player');
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        $iframe->setAttribute('allowfullscreen', '');

        foreach (['width', 'height'] as $dimension) {
            $size = $source->getAttribute($dimension);
            if (preg_match('/^[1-9][0-9]{0,3}$/', $size) && (int) $size <= 2000) {
                $iframe->setAttribute($dimension, $size);
            }
        }

        return $iframe;
    }

    private static function SafeVideo(DOMElement $source, DOMDocument $document): ?DOMElement
    {
        $src = self::SafeUrl($source->getAttribute('src'), false);
        $parts = $src === null ? false : parse_url($src);
        if (! is_array($parts)
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || ! isset($parts['host'])) {
            return null;
        }

        $video = $document->createElement('video');
        $video->setAttribute('src', $src);
        $video->setAttribute('controls', '');
        $video->setAttribute('preload', 'metadata');

        return $video;
    }
}
