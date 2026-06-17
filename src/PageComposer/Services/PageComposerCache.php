<?php

namespace Flobbos\PageComposer\Services;

use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\CategoryTranslation;
use Flobbos\PageComposer\Models\Element;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\Tag;
use Flobbos\PageComposer\Models\TagTranslation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PageComposerCache
{
    private const KEY_ELEMENTS = 'page-composer.elements';
    private const KEY_LANGUAGES = 'page-composer.languages';
    private const KEY_CATEGORIES = 'page-composer.categories';
    private const KEY_TAGS = 'page-composer.tags';

    private const TTL_DEFAULT_MINUTES = 30;
    private const TTL_LANGUAGES_MINUTES = 5;

    public function elements(bool $refresh = false): Collection
    {
        return $this->remember(
            self::KEY_ELEMENTS,
            self::TTL_DEFAULT_MINUTES,
            $refresh,
            fn() => Element::all()->toArray(),
            fn(array $cached) => Element::hydrate($cached),
        );
    }

    public function languages(bool $refresh = false): Collection
    {
        return $this->remember(
            self::KEY_LANGUAGES,
            self::TTL_LANGUAGES_MINUTES,
            $refresh,
            fn() => Language::all()->toArray(),
            fn(array $cached) => Language::hydrate($cached),
        );
    }

    public function categories(bool $refresh = false): Collection
    {
        return $this->remember(
            self::KEY_CATEGORIES,
            self::TTL_DEFAULT_MINUTES,
            $refresh,
            fn() => Category::with('translations')->get()->toArray(),
            fn(array $cached) => $this->hydrateWithTranslations(Category::class, CategoryTranslation::class, $cached),
        );
    }

    public function tags(bool $refresh = false): Collection
    {
        return $this->remember(
            self::KEY_TAGS,
            self::TTL_DEFAULT_MINUTES,
            $refresh,
            fn() => Tag::with('translations')->get()->toArray(),
            fn(array $cached) => $this->hydrateWithTranslations(Tag::class, TagTranslation::class, $cached),
        );
    }

    public function forgetAll(): void
    {
        foreach ([self::KEY_ELEMENTS, self::KEY_LANGUAGES, self::KEY_CATEGORIES, self::KEY_TAGS] as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Caching arrays (rather than Eloquent Collections) avoids
     * serialize/unserialize fragility across framework upgrades and
     * cache driver changes; the hydrator rebuilds models on read.
     */
    private function remember(string $key, int $ttlMinutes, bool $refresh, callable $loader, callable $hydrator): Collection
    {
        if ($refresh) {
            Cache::forget($key);
        }

        $cached = Cache::remember($key, now()->addMinutes($ttlMinutes), $loader);

        return $hydrator($cached);
    }

    private function hydrateWithTranslations(string $modelClass, string $translationClass, array $cached): Collection
    {
        return collect($cached)->map(function (array $row) use ($modelClass, $translationClass) {
            $translations = Arr::pull($row, 'translations', []);
            $model = $modelClass::hydrate([$row])->first();
            $model->setRelation('translations', $translationClass::hydrate($translations));
            return $model;
        });
    }
}
