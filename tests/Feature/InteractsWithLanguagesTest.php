<?php

use Flobbos\PageComposer\Livewire\PageComposer;
use Livewire\Livewire;

beforeEach(function () {
    $this->languages = seedLanguages(['en', 'de']);
});

it('keeps the full language list intact after a language is selected', function () {
    $deId = $this->languages->firstWhere('locale', 'de')->id;

    $component = Livewire::test(PageComposer::class)
        ->call('addLanguage', $deId);

    // The master list must still hold every language; only the selectable
    // list should shrink. The bug shared one Collection between $languages
    // and $selectableLanguages, so forgetting the used language from one
    // emptied the other and selected languages vanished from the picker.
    expect($component->get('languages'))->toHaveCount(2);
    expect(collect($component->get('availableLanguages'))->pluck('id'))->toContain($deId);
    expect(collect($component->get('selectableLanguages'))->pluck('id'))->not->toContain($deId);
});
