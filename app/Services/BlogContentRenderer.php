<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;

class BlogContentRenderer
{
    private const YOUTUBE_IFRAME_TOKEN_PREFIX = 'BLOG_YOUTUBE_IFRAME_TOKEN_';
    private const DEFAULT_IFRAME_WIDTH = 560;
    private const DEFAULT_IFRAME_HEIGHT = 315;
    private const MAX_IFRAME_DIMENSION = 2000;
    private const YOUTUBE_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
    ];

    private const ALLOWED_QUERY_PARAMETERS = [
        'controls',
        'end',
        'list',
        'loop',
        'modestbranding',
        'playlist',
        'rel',
        'start',
    ];

    public function Render(string $content): string
    {
        [$prepared_content, $safe_iframe_html_by_token] = $this->ExtractSafeYoutubeIframes($content);

        $rendered_html = Str::markdown($prepared_content);

        return $this->RestoreSafeYoutubeIframes((string) $rendered_html, $safe_iframe_html_by_token);
    }

    private function ExtractSafeYoutubeIframes(string $content): array
    {
        $safe_iframe_html_by_token = [];
        $iframe_index = 0;

        $prepared_content = preg_replace_callback(
            '/<iframe\b[^>]*>.*?<\/iframe>/is',
            function (array $matches) use (&$safe_iframe_html_by_token, &$iframe_index): string {
                $safe_iframe_html = $this->BuildSafeYoutubeIframeHtml($matches[0]);

                if ($safe_iframe_html === null) {
                    return $matches[0];
                }

                $token = self::YOUTUBE_IFRAME_TOKEN_PREFIX . $iframe_index++;
                $safe_iframe_html_by_token[$token] = $safe_iframe_html;

                return "\n\n{$token}\n\n";
            },
            $content
        );

        return [$prepared_content ?? $content, $safe_iframe_html_by_token];
    }

    private function BuildSafeYoutubeIframeHtml(string $iframe_html): ?string
    {
        $iframe = $this->ReadIframeElement($iframe_html);

        if (!$iframe instanceof DOMElement) {
            return null;
        }

        $safe_src = $this->BuildSafeYoutubeSrc($iframe->getAttribute('src'));

        if ($safe_src === null) {
            return null;
        }

        $width = $this->ReadSafeDimension($iframe->getAttribute('width'), self::DEFAULT_IFRAME_WIDTH);
        $height = $this->ReadSafeDimension($iframe->getAttribute('height'), self::DEFAULT_IFRAME_HEIGHT);
        $title = trim($iframe->getAttribute('title')) ?: 'YouTube video player';

        return sprintf(
            '<div class="youtube-embed"><iframe width="%d" height="%d" src="%s" title="%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe></div>',
            $width,
            $height,
            e($safe_src),
            e($title)
        );
    }

    private function ReadIframeElement(string $iframe_html): ?DOMElement
    {
        $document = new DOMDocument();
        $previous_error_state = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<!DOCTYPE html><html><body>' . $iframe_html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous_error_state);

        $iframe = $document->getElementsByTagName('iframe')->item(0);

        return $iframe instanceof DOMElement ? $iframe : null;
    }

    private function BuildSafeYoutubeSrc(string $src): ?string
    {
        $src_parts = parse_url($src);

        if (!is_array($src_parts)) {
            return null;
        }

        $scheme = strtolower($src_parts['scheme'] ?? '');
        $host = strtolower($src_parts['host'] ?? '');
        $path = $src_parts['path'] ?? '';

        if ($scheme !== 'https' || !in_array($host, self::YOUTUBE_HOSTS, true)) {
            return null;
        }

        if (!preg_match('/^\/embed\/[A-Za-z0-9_-]+$/', $path)) {
            return null;
        }

        $safe_host = str_contains($host, 'youtube-nocookie.com') ? 'www.youtube-nocookie.com' : 'www.youtube.com';
        $safe_src = 'https://' . $safe_host . $path;
        $safe_query = $this->BuildSafeYoutubeQuery($src_parts['query'] ?? '');

        return $safe_query === '' ? $safe_src : $safe_src . '?' . $safe_query;
    }

    private function BuildSafeYoutubeQuery(string $query): string
    {
        parse_str($query, $query_parameters);

        if (!is_array($query_parameters) || $query_parameters === []) {
            return '';
        }

        $safe_query_parameters = [];

        foreach (self::ALLOWED_QUERY_PARAMETERS as $parameter_name) {
            if (!array_key_exists($parameter_name, $query_parameters)) {
                continue;
            }

            $parameter_value = $query_parameters[$parameter_name];

            if (!is_string($parameter_value) || !preg_match('/^[A-Za-z0-9,_-]+$/', $parameter_value)) {
                continue;
            }

            $safe_query_parameters[$parameter_name] = $parameter_value;
        }

        return http_build_query($safe_query_parameters, '', '&', PHP_QUERY_RFC3986);
    }

    private function ReadSafeDimension(string $dimension, int $default_dimension): int
    {
        if (!preg_match('/^\d{1,4}$/', $dimension)) {
            return $default_dimension;
        }

        $safe_dimension = (int) $dimension;

        if ($safe_dimension < 1 || $safe_dimension > self::MAX_IFRAME_DIMENSION) {
            return $default_dimension;
        }

        return $safe_dimension;
    }

    private function RestoreSafeYoutubeIframes(string $rendered_html, array $safe_iframe_html_by_token): string
    {
        foreach ($safe_iframe_html_by_token as $token => $safe_iframe_html) {
            $rendered_html = str_replace('<p>' . $token . '</p>', $safe_iframe_html, $rendered_html);
            $rendered_html = str_replace($token, $safe_iframe_html, $rendered_html);
        }

        return $rendered_html;
    }
}
