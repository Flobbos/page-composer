<?php

namespace Flobbos\PageComposer\Livewire;;

use Flobbos\PageComposer\Models\Tag;
use Livewire\Component;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\TagTranslation;

class TagComponent extends Component
{
    public $showTagCreate = false;
    public $showTagList = true;
    public $showTagWindow = false;
    public $tags = [];
    public $languages;
    public $content = [];
    public $tag_id;

    public $rules = [
        'content.*.name' => 'required',
    ];

    public function mount()
    {
        $this->languages = Language::all();
    }

    public function saveTag()
    {
        $this->validate();

        $tag = Tag::create([]);
        $translations = [];
        foreach ($this->content as $key => $translation) {
            $translations[] = new TagTranslation(array_merge($translation, ['language_id' => $key]));
        }

        $tag->translations()->saveMany($translations);

        $this->resetForm();

        $this->toggleView();
    }

    public function editTag(Tag $tag)
    {
        $this->tag_id = $tag->id;

        $tag->load('translations');

        foreach ($tag->translations as $translation) {
            $this->content[$translation->language_id]['name'] = $translation->name;
            $this->content[$translation->language_id]['tag_translation_id'] = $translation->id;
        }

        $this->toggleView();
    }

    public function updateTag(Tag $tag)
    {
        $this->validate();

        foreach ($this->content as $key => $translation) {
            if (isset($translation['tag_translation_id'])) {
                $item = TagTranslation::find($translation['tag_translation_id']);
                $item->name = $translation['name'];
                $item->save();
            } else {
                TagTranslation::create([
                    'name' => $translation['name'],
                    'language_id' => $key,
                    'tag_id' => $tag->id
                ]);
            }
        }

        $this->resetForm();
        $this->toggleView();
    }

    public function deleteTag(Tag $tag)
    {
        $tag->delete();
    }

    public function toggleView()
    {
        $this->showTagList = !$this->showTagList;
        $this->showTagCreate = !$this->showTagCreate;
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->toggleView();
    }

    public function resetForm()
    {
        $this->reset(['content', 'tag_id']);
    }

    public function resetComponent()
    {
        $this->reset();
    }

    public function render()
    {
        $this->tags = Tag::with('translations')->get();
        return view('page-composer::livewire.tag-component');
    }
}
