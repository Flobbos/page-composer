<?php

use Flobbos\PageComposer\Livewire\PageComposer;
use Flobbos\PageComposer\Models\Language;
use Livewire\Livewire;

it('mounts for a new page without errors', function () {
    seedLanguages(['en']);
    seedElement();

    Livewire::test(PageComposer::class)
        ->assertOk()
        ->assertSet('pageId', null);
});

it('exposes the seeded languages via its queried properties', function () {
    seedLanguages(['en', 'de']);
    seedElement();

    $component = Livewire::test(PageComposer::class);

    // availableLanguages is populated by hydrateLanguages() during render based
    // on translations present. With no page + no translations, it's empty and
    // all seeded languages should be selectable.
    expect($component->get('languages'))->toHaveCount(2);
});

it('adds a language and initializes an empty row bucket for it', function () {
    seedLanguages(['en', 'de']);
    seedElement();

    $english = Language::where('locale', 'en')->first();

    $component = Livewire::test(PageComposer::class)
        ->call('addLanguage', $english->id);

    $rows = $component->get('rows');

    expect($rows)->toBeArray()
        ->and($rows)->toHaveKey('en')
        ->and($rows['en']['rows'])->toBeArray()
        ->and($rows['en']['rows'])->toBeEmpty()
        ->and($component->get('currentLanguage.locale'))->toBe('en');
});

it('adds a new row when addRow is called', function () {
    seedLanguages(['en']);
    seedElement();

    $english = Language::where('locale', 'en')->first();

    $component = Livewire::test(PageComposer::class)
        ->call('addLanguage', $english->id)
        ->call('addRow');

    $rows = $component->get('rows.en.rows');

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['sorting'])->toBe(1)
        ->and($rows[0]['available_space'])->toBe(12)
        ->and($rows[0]['columns'])->toBe([])
        ->and($rows[0])->toHaveKey('uuid');
});
