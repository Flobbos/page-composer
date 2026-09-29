<?php

namespace Flobbos\PageComposer\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\TextSanitizer\UrlSanitizer;

/**
 * Cleans element content before it is stored. Content arrives from
 * client-side Livewire state, so it is whatever the browser sent, not
 * whatever the editor produced.
 *
 * - Keys listed in pagecomposer.sanitize.html_keys are rendered raw by the
 *   element views, so they are run through an HTML allowlist.
 * - Keys ending in "url" (videoUrl, ctaUrl, imageUrl...) end up in href/src
 *   attributes, so anything that isn't an allowed scheme or a relative URL
 *   is dropped. Blade escaping doesn't stop javascript: URLs.
 * - Everything else is left alone; Blade escapes it on output.
 */
class ContentSanitizer
{
    private ?HtmlSanitizer $html = null;

    private ?HtmlSanitizer $svg = null;

    /**
     * Presentation-only SVG, for element icons. Attribute names are
     * lowercase because the HTML parser lowercases them; browsers restore
     * SVG casing (viewBox) when parsing inline SVG.
     */
    private const SVG_ELEMENTS = [
        'svg' => ['xmlns', 'viewbox', 'class', 'fill', 'stroke', 'stroke-width', 'width', 'height', 'aria-hidden'],
        'g' => ['class', 'fill', 'stroke', 'stroke-width', 'transform'],
        'path' => ['d', 'class', 'fill', 'stroke', 'stroke-linecap', 'stroke-linejoin', 'stroke-width', 'fill-rule', 'clip-rule', 'transform'],
        'circle' => ['cx', 'cy', 'r', 'class', 'fill', 'stroke', 'stroke-width'],
        'ellipse' => ['cx', 'cy', 'rx', 'ry', 'class', 'fill', 'stroke', 'stroke-width'],
        'rect' => ['x', 'y', 'width', 'height', 'rx', 'ry', 'class', 'fill', 'stroke', 'stroke-width', 'transform'],
        'line' => ['x1', 'y1', 'x2', 'y2', 'class', 'stroke', 'stroke-linecap', 'stroke-width'],
        'polyline' => ['points', 'class', 'fill', 'stroke', 'stroke-linecap', 'stroke-linejoin', 'stroke-width'],
        'polygon' => ['points', 'class', 'fill', 'stroke', 'stroke-linejoin', 'stroke-width'],
    ];

    public function sanitize(array $content): array
    {
        if (!config('pagecomposer.sanitize.enabled', true)) {
            return $content;
        }

        $htmlKeys = (array) config('pagecomposer.sanitize.html_keys', ['text', 'videoCaption']);

        array_walk_recursive($content, function (&$value, $key) use ($htmlKeys) {
            if (!is_string($value) || !is_string($key)) {
                return;
            }

            if (in_array($key, $htmlKeys, true)) {
                $value = $this->html()->sanitize($value);
            } elseif (preg_match('/url$/i', $key)) {
                $value = $this->url($value);
            }
        });

        return $content;
    }

    public function url(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        return UrlSanitizer::sanitize(
            $value,
            (array) config('pagecomposer.sanitize.url_schemes', ['http', 'https', 'mailto', 'tel']),
            false,
            null,
            true,
        );
    }

    /**
     * Clean an element icon. Icons are raw SVG typed into the element
     * creator and rendered unescaped.
     */
    public function svg(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        if (!$this->svg) {
            $config = (new HtmlSanitizerConfig())->withMaxInputLength(-1);

            foreach (self::SVG_ELEMENTS as $element => $attributes) {
                $config = $config->allowElement($element, $attributes);
            }

            $this->svg = new HtmlSanitizer($config);
        }

        return $this->svg->sanitize($value);
    }

    private function html(): HtmlSanitizer
    {
        if ($this->html) {
            return $this->html;
        }

        $config = (new HtmlSanitizerConfig())
            ->allowLinkSchemes((array) config('pagecomposer.sanitize.url_schemes', ['http', 'https', 'mailto', 'tel']))
            ->allowRelativeLinks()
            ->withMaxInputLength(-1);

        foreach ((array) config('pagecomposer.sanitize.allowed_elements', []) as $element => $attributes) {
            $config = $config->allowElement($element, (array) $attributes);
        }

        return $this->html = new HtmlSanitizer($config);
    }
}
