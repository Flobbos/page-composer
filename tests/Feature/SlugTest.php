<?php

use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Services\PageBuilder;

beforeEach(function () {
    $this->languages = seedLanguages(['en', 'de'])->keyBy('locale');
    $this->categoryId = Category::create([])->id;
});

function savePageTitled(string $title, ?int $pageId = null, string $locale = 'en'): \Flobbos\PageComposer\Models\Page
{
    $test = test();
    $translation = ['language_id' => $test->languages[$locale]->id, 'content' => ['title' => $title]];

    if ($pageId) {
        $existing = PageTranslation::where('page_id', $pageId)->where('language_id', $translation['language_id'])->first();
        if ($existing) {
            $translation['id'] = $existing->id;
        }
    }

    return app(PageBuilder::class)->persist(
        $pageId,
        ['name' => $title, 'category_id' => $test->categoryId],
        [$locale => $translation],
        [],
        [],
        $test->languages,
    )->page;
}

function slugOf(\Flobbos\PageComposer\Models\Page $page, string $locale = 'en'): string
{
    return PageTranslation::where('page_id', $page->id)
        ->where('language_id', test()->languages[$locale]->id)
        ->value('slug');
}

it('suffixes a slug another page already uses in the same language', function () {
    $first = savePageTitled('About Us');
    $second = savePageTitled('About Us');
    $third = savePageTitled('About Us');

    expect(slugOf($first))->toBe('about-us')
        ->and(slugOf($second))->toBe('about-us-2')
        ->and(slugOf($third))->toBe('about-us-3');
});

it('keeps a page\'s own slug when it is saved again', function () {
    $page = savePageTitled('About Us');
    savePageTitled('About Us', $page->id);

    expect(slugOf($page))->toBe('about-us');
});

it('allows the same slug in different languages', function () {
    $en = savePageTitled('Kontakt', locale: 'en');
    $de = savePageTitled('Kontakt', locale: 'de');

    expect(slugOf($en, 'en'))->toBe('kontakt')
        ->and(slugOf($de, 'de'))->toBe('kontakt');
});
