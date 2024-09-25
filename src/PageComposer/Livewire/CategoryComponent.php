<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\CategoryTranslation;
use Flobbos\PageComposer\Models\Language;

class CategoryComponent extends Component
{
    public $showCategoryCreate = false;
    public $showCategoryList = true;
    public $showCategoryWindow = false;
    public $categories = [];
    public $languages;
    public $content = [];
    public $category_id;

    public $rules = [
        'content.*.name' => 'required',
    ];

    public function mount()
    {
        $this->languages = Language::all();
    }

    public function saveCategory()
    {
        $this->validate();

        $category = Category::create([]);
        $translations = [];
        foreach ($this->content as $key => $translation) {
            $translations[] = new CategoryTranslation(array_merge($translation, ['language_id' => $key]));
        }

        $category->translations()->saveMany($translations);

        $this->resetForm();

        $this->toggleView();
    }

    public function editCategory(Category $category)
    {
        $this->category_id = $category->id;

        $category->load('translations');

        foreach ($category->translations as $translation) {
            $this->content[$translation->language_id]['name'] = $translation->name;
            $this->content[$translation->language_id]['category_translation_id'] = $translation->id;
        }

        $this->toggleView();
    }

    public function updateCategory(Category $category)
    {
        $this->validate();

        foreach ($this->content as $key => $translation) {
            if (isset($translation['category_translation_id'])) {
                $item = CategoryTranslation::find($translation['category_translation_id']);
                $item->name = $translation['name'];
                $item->save();
            } else {
                CategoryTranslation::create([
                    'name' => $translation['name'],
                    'language_id' => $key,
                    'category_id' => $category->id
                ]);
            }
        }

        $this->resetForm();
        $this->toggleView();
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();
    }

    public function toggleView()
    {
        $this->showCategoryList = !$this->showCategoryList;
        $this->showCategoryCreate = !$this->showCategoryCreate;
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->toggleView();
    }

    public function resetForm()
    {
        $this->reset(['content', 'category_id']);
    }

    public function resetComponent()
    {
        $this->reset();
    }

    public function render()
    {
        $this->categories = Category::with('translations')->get();
        return view('livewire.category-component');
    }
}
