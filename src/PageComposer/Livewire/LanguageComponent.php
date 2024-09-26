<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;
use Flobbos\PageComposer\Models\Language;

class LanguageComponent extends Component
{
    public $showLanguageAdd = false;
    public $name, $locale;

    public $rules = [
        'name' => 'required',
        'locale' => 'required'
    ];

    protected $listeners = ['showLanguageCreate'];

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
        $this->emitUp('languageAdded');
    }

    public function showLanguageCreate()
    {
        $this->showLanguageAdd = true;
    }
}
