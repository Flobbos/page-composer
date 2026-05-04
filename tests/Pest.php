<?php

use Flobbos\PageComposer\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Binds the package's base TestCase to all tests under tests/Feature and
| tests/Unit. RefreshDatabase is applied via the base class, so each test
| runs against a freshly migrated in-memory SQLite database.
|
*/

uses(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Add custom expectations here as the suite grows.
|
*/

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Seed the package's default set of languages for tests that need them.
 *
 * @return \Illuminate\Support\Collection<\Flobbos\PageComposer\Models\Language>
 */
function seedLanguages(array $locales = ['en', 'de']): \Illuminate\Support\Collection
{
    return collect($locales)->map(fn(string $locale) => \Flobbos\PageComposer\Models\Language::create([
        'name' => strtoupper($locale),
        'locale' => $locale,
    ]));
}

/**
 * Seed a minimal element so elements-dependent flows can run.
 */
function seedElement(string $name = 'Text', string $component = 'text'): \Flobbos\PageComposer\Models\Element
{
    return \Flobbos\PageComposer\Models\Element::create([
        'name' => $name,
        'component' => $component,
        'icon' => '<svg></svg>',
    ]);
}
