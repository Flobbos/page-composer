<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;
use Flobbos\PageComposer\Models\Language;
use Livewire\Attributes\On;

class LanguageComponent extends Component
{
    public $showLanguageAdd = false;
    public $name, $locale;

    public $rules = [
        'name' => 'required',
        'locale' => 'required'
    ];

    public function render()
    {
        return view('page-composer::livewire.language-component');
    }

    public function saveLanguage()
    {
        $this->validate();

        Language::create([
            'name' => $this->name,
            'locale' => $this->locale
        ]);

        $this->reset();
        $this->dispatch('languageAdded');
    }

    #[On('showLanguageCreate')]
    public function showLanguageCreate()
    {
        $this->showLanguageAdd = true;
    }
}
