<?php

use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Services\ContentSanitizer;
use Flobbos\PageComposer\Services\PageBuilder;

it('strips scripts and event handlers from rich-text keys', function () {
    $clean = app(ContentSanitizer::class)->sanitize([
        'text' => '<p class="ql-align-center">Hi <strong>there</strong><script>alert(1)</script><img src=x onerror=alert(1)></p>',
    ]);

    expect($clean['text'])->toBe('<p class="ql-align-center">Hi <strong>there</strong></p>');
});

it('keeps the markup the Quill toolbar produces', function () {
    $html = '<h2>Title</h2><p><em>a</em> <u>b</u> <a href="https://example.com" target="_blank">c</a></p>'
        . '<ol><li data-list="bullet"><span class="ql-ui"></span>one</li></ol>';

    expect(app(ContentSanitizer::class)->sanitize(['text' => $html])['text'])->toBe($html);
});

it('drops javascript links inside rich text', function () {
    $clean = app(ContentSanitizer::class)->sanitize(['text' => '<a href="javascript:alert(1)">x</a>']);

    expect($clean['text'])->not->toContain('javascript');
});

it('drops unsafe schemes from url keys at any depth', function () {
    $clean = app(ContentSanitizer::class)->sanitize([
        'ctaUrl' => 'javascript:alert(1)',
        'videoUrl' => 'https://www.youtube.com/embed/abc',
        'cards' => [
            ['linkUrl' => 'JaVaScRiPt:alert(1)', 'imageUrl' => '/storage/photos/a.jpg'],
            ['linkUrl' => 'mailto:hi@example.com'],
        ],
    ]);

    expect($clean['ctaUrl'])->toBeNull()
        ->and($clean['videoUrl'])->toBe('https://www.youtube.com/embed/abc')
        ->and($clean['cards'][0]['linkUrl'])->toBeNull()
        ->and($clean['cards'][0]['imageUrl'])->toBe('/storage/photos/a.jpg')
        ->and($clean['cards'][1]['linkUrl'])->toBe('mailto:hi@example.com');
});

it('leaves plain-text keys alone for Blade to escape', function () {
    $clean = app(ContentSanitizer::class)->sanitize(['headline' => 'Tom & Jerry <3']);

    expect($clean['headline'])->toBe('Tom & Jerry <3');
});

it('does nothing when disabled', function () {
    config(['pagecomposer.sanitize.enabled' => false]);

    $clean = app(ContentSanitizer::class)->sanitize(['text' => '<script>x</script>', 'ctaUrl' => 'javascript:x']);

    expect($clean)->toBe(['text' => '<script>x</script>', 'ctaUrl' => 'javascript:x']);
});

it('sanitizes element content when a page is saved', function () {
    $languages = seedLanguages(['en']);
    $element = seedElement();

    $result = app(PageBuilder::class)->persist(
        null,
        ['name' => 'Hello', 'category_id' => Category::create([])->id],
        [],
        [],
        ['en' => ['rows' => [[
            'sorting' => 1,
            'columns' => [[
                'column_size' => 12,
                'sorting' => 1,
                'column_items' => [[
                    'element_id' => $element->id,
                    'sorting' => 1,
                    'content' => ['text' => '<p>ok</p><script>alert(1)</script>', 'ctaUrl' => 'javascript:alert(1)'],
                ]],
            ]],
        ]]]],
        $languages->keyBy('locale'),
    );

    expect(ColumnItem::first()->content)->toBe(['text' => '<p>ok</p>', 'ctaUrl' => null])
        ->and($result->rows['en']['rows'][0]['columns'][0]['column_items'][0]['content']['text'])->toBe('<p>ok</p>');
});

it('strips scripts and handlers from element icons', function () {
    $element = seedElement();
    $element->update(['icon' => '<svg viewBox="0 0 24 24" onload="alert(1)"><path d="M1 1"/><script>alert(1)</script><foreignObject><img src=x onerror=alert(1)></foreignObject></svg>']);

    expect($element->fresh()->icon)->toBe('<svg viewbox="0 0 24 24"><path d="M1 1"></path></svg>');
});

it('keeps the seeded element icons intact', function () {
    (new \Flobbos\PageComposer\ElementTableSeeder())->run();

    \Flobbos\PageComposer\Models\Element::all()->each(function ($element) {
        $raw = $element->getRawOriginal('icon');

        expect(substr_count($element->icon, '<path'))->toBe(substr_count($raw, '<path'))
            ->and($element->icon)->toStartWith('<svg');
    });
});

it('cleans icons that were stored before sanitizing existed', function () {
    $element = seedElement();
    \Illuminate\Support\Facades\DB::table($element->getTable())->where('id', $element->id)
        ->update(['icon' => '<svg onload="alert(1)"><path d="M1 1"/></svg>']);

    expect($element->fresh()->icon)->toBe('<svg><path d="M1 1"></path></svg>');
});
