<?php

use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\Element;

it('boots the package service provider', function () {
    expect(config('pagecomposer'))->toBeArray()
        ->and(config('pagecomposer.bug_user'))->toBe(1);
});

it('runs package migrations against an in-memory sqlite db', function () {
    $language = Language::create([
        'name' => 'English',
        'locale' => 'en',
    ]);

    expect($language->exists)->toBeTrue()
        ->and(Language::count())->toBe(1);
});

it('can seed languages and elements via test helpers', function () {
    seedLanguages(['en', 'de', 'fr']);
    seedElement('Text', 'text');

    expect(Language::pluck('locale')->all())->toEqual(['en', 'de', 'fr'])
        ->and(Element::where('component', 'text')->exists())->toBeTrue();
});
