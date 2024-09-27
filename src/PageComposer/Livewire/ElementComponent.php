<?php

namespace Flobbos\PageComposer\Livewire;;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Livewire\Component;
use Flobbos\PageComposer\Models\Element;

class ElementComponent extends Component
{
    public $showElementCreate = false;
    public $showElementList = true;
    public $showElementWindow = false;
    public $elements;

    public $name, $icon, $element_id;

    public $rules = [
        'name' => 'required',
        'icon' => 'required',
    ];

    public function render()
    {
        $this->elements = Element::all();
        return view('page-composer::livewire.element-component');
    }

    public function saveElement()
    {
        $this->validate();

        Element::create([
            'name' => $this->name,
            'component' => Str::slug($this->name),
            'icon' => $this->icon
        ]);

        Artisan::call('pagebuilder:element ' . Str::studly($this->name));

        $this->resetForm();

        $this->dispatch('componentAdded');

        $this->toggleView();
    }

    public function editElement(Element $element)
    {
        $this->name = $element->name;
        $this->icon = $element->icon;
        $this->element_id = $element->id;

        $this->toggleView();
    }

    public function updateElement(Element $element)
    {
        $this->validate();

        $originalComponentName = $element->component;
        $updatedComponentName = Str::slug($this->name);

        $element->update([
            'name' => $this->name,
            'component' => $updatedComponentName,
            'icon' => $this->icon
        ]);

        Artisan::call('livewire:move Elements/' . $originalComponentName . ' Elements/' . $updatedComponentName);

        $this->resetForm();
        $this->toggleView();
    }

    public function toggleView()
    {
        $this->showElementList = !$this->showElementList;
        $this->showElementCreate = !$this->showElementCreate;
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->toggleView();
    }

    public function resetForm()
    {
        $this->reset(['name', 'icon', 'element_id']);
    }

    public function resetComponent()
    {
        $this->reset();
    }

    public function getComponentProperty()
    {
        return Str::kebab($this->name);
    }
}
