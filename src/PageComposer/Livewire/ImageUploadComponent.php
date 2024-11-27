<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ImageUploadComponent extends Component
{
    use WithFileUploads;

    public $class;
    public $elementId;
    public $eventTarget;
    public $existingPhoto;
    public $fieldName;
    public $photo;
    public $photoInput;
    public $photoPath;
    public $saved = false;
    public $title;

    public function mount()
    {
        $this->photoInput = $this->existingPhoto;
        $this->elementId = uniqid();
    }

    public function render()
    {
        return view('page-composer::livewire.image-upload-component');
    }

    public function save()
    {
        $this->validate([
            'photo' => 'image|max:1024', // 1MB Max
        ]);

        //Check for overwrite
        if ($this->photoExists()) {
            $this->addError('photo', 'File already exists');
            $this->reset('photo');
            
            return;
        }
        
        //Delete existing photo if replaced
        if (! is_null($this->existingPhoto)) {
            $this->deleteExistingPhoto();
            $this->reset('existingPhoto');
        }

        //Save new photo
        $this->savePhoto();

        $this->saved = true;

        $this->dispatch('photoSaved', $this->eventTarget, $this->photo->getClientOriginalName());
    }

    public function delete()
    {
        if ($this->photoExists()) {
            if ($this->existingPhoto) {
                Storage::delete('public/' . $this->existingPhoto);
            } else {
                Storage::delete('public/' . $this->photoPath . '/' . $this->photo->getClientOriginalName());
            }
        }

        $this->reset('photoInput', 'existingPhoto');
    }

    public function photoExists()
    {
        if ($this->existingPhoto) {
            return Storage::exists('public/' . $this->existingPhoto);
        } else {
            return Storage::exists('public/' . $this->photoPath . '/' . $this->photo->getClientOriginalName());
        }
    }

    public function deletePhoto()
    {
        $this->reset('photo');
    }

    public function savePhoto()
    {
        $this->photo->storeAs($this->photoPath, $this->photo->getClientOriginalName(), 'public');
        $this->photoInput = $this->photoPath . '/' . $this->photo->getClientOriginalName();
    }

    public function deleteExistingPhoto()
    {
        Storage::delete('public/' . $this->existingPhoto);
        
        $this->dispatch('photoRemoved', $this->eventTarget);
        $this->reset('photoInput', 'existingPhoto');
    }
}
