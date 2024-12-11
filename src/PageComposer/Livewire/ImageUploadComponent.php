<?php

namespace Flobbos\PageComposer\Livewire;;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ImageUploadComponent extends Component
{
    use WithFileUploads;

    public $class;
    public $elementId;
    public $eventTarget;
    public $existingImage;
    public $fieldName;
    public $image;
    public $imageInput;
    public $imagePath;
    public $itemIndex = null;
    public $saved = false;
    public $title;

    public function mount()
    {
        $this->imageInput = $this->existingImage;
        $this->elementId = uniqid();
    }

    public function render()
    {
        return view('page-composer::livewire.image-upload-component');
    }

    public function saveImage()
    {
        $this->validate([
            'image' => 'image|max:1024', // 1MB Max
        ]);

        if ($this->imageExists()) {
            $this->addError('image', 'File already exists');
            $this->reset('image');

            return;
        }

        $filename = basename($this->image->getClientOriginalName(), '.' . $this->image->getClientOriginalExtension());
        $filename = Str::slug($filename) . '_' . uniqid() . '.' . $this->image->getClientOriginalExtension();

        $this->image->storeAs($this->imagePath, $filename, 'public');
        $this->imageInput = $this->imagePath . $filename;

        $this->saved = true;

        $this->dispatch('eventImageUploadComponentSaved.' . $this->eventTarget, imagePath: $this->imagePath . $filename, itemIndex: $this->itemIndex);

        $this->existingImage = $this->image;

        $this->reset('image');
    }

    public function deleteImage()
    {
        if ($this->imageExists()) {
            if ($this->existingImage) {
                Storage::delete('public/' . $this->existingImage);
            } else {
                Storage::delete('public/' . $this->imagePath . '/' . $this->image->getClientOriginalName());
            }
        }

        $this->reset('image', 'imageInput', 'existingImage');
    }

    public function imageExists()
    {
        if ($this->existingImage) {
            return Storage::exists('photos/' . $this->existingImage);
        } else {
            return Storage::exists('photos/' . $this->imagePath . '/' . $this->image->getClientOriginalName());
        }

        return false;
    }

    public function deleteExistingImage()
    {
        Storage::delete('public/' . $this->existingImage);
        
        $this->dispatch('eventImageUploadComponentDeleted.' . $this->eventTarget, imagePath: $this->existingImage, itemIndex: $this->itemIndex);

        $this->reset('image', 'imageInput', 'existingImage');
    }
}
