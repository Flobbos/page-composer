<?php

namespace Flobbos\PageComposer\Livewire;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Livewire\Component;
use Flobbos\PageComposer\Models\Element;
use Flobbos\PageComposer\Services\PageComposerCache;

class ElementComponent extends Component
{
    public $showElementCreate = false;
    public $showElementList = true;
    public $showElementWindow = false;
    public $elements;

    public $name, $icon, $element_id;
    public $createFromTemplate = true;
    public $componentName = '';

    protected function rules()
    {
        $rules = [
            'name' => 'required',
            'icon' => 'required',
        ];

        if (!$this->createFromTemplate) {
            $rules['componentName'] = 'required';
        }

        return $rules;
    }

    public function render()
    {
        $this->elements = app(PageComposerCache::class)->elements();
        return view('page-composer::livewire.element-component');
    }

    public function saveElement()
    {
        $this->validate();

        if (!$this->createFromTemplate) {
            // Validate that component files exist
            $classFile = app_path('Livewire/PageComposerElements/' . Str::studly($this->componentName) . '.php');
            $viewFile = resource_path('views/livewire/page-composer-elements/' . Str::slug($this->componentName) . '.blade.php');
            $previewFile = resource_path('/views/components/page-composer-elements/' . Str::slug($this->componentName) . '.blade.php');

            if (!file_exists($classFile)) {
                $this->addError('componentName', "Component class file not found at: app/Livewire/PageComposerElements/" . Str::studly($this->componentName) . ".php");
                return;
            }

            if (!file_exists($viewFile)) {
                $this->addError('componentName', "Component view file not found at: resources/views/livewire/page-composer-elements/" . Str::slug($this->componentName) . ".blade.php");
                return;
            }

            if (!file_exists($previewFile)) {
                $this->addError('componentName', "Component preview file not found at: resources/views/components/page-composer-elements/" . Str::slug($this->componentName) . ".blade.php");
                return;
            }
        }

        Element::create([
            'name' => $this->name,
            'component' => $this->createFromTemplate ? Str::slug($this->name) : Str::slug($this->componentName),
            'icon' => $this->icon
        ]);

        app(PageComposerCache::class)->elements(true);

        if ($this->createFromTemplate) {
            Artisan::call('page-composer:element ' . Str::studly($this->name));
        }

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

        app(PageComposerCache::class)->elements(true);

        // Rename component files if they exist
        $originalClassFile = app_path('Livewire/PageComposerElements/' . Str::studly($originalComponentName) . '.php');
        $updatedClassFile = app_path('Livewire/PageComposerElements/' . Str::studly($updatedComponentName) . '.php');
        
        $originalViewFile = resource_path('views/livewire/page-composer-elements/' . $originalComponentName . '.blade.php');
        $updatedViewFile = resource_path('views/livewire/page-composer-elements/' . $updatedComponentName . '.blade.php');

        if (file_exists($originalClassFile)) {
            rename($originalClassFile, $updatedClassFile);
        }

        if (file_exists($originalViewFile)) {
            rename($originalViewFile, $updatedViewFile);
        }

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
        $this->reset(['name', 'icon', 'element_id', 'createFromTemplate', 'componentName']);
        $this->createFromTemplate = true;
        $this->componentName = '';
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
