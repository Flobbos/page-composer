<?php

namespace Flobbos\PageComposer\Livewire;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ImageUploadComponent extends Component
{
    use WithFileUploads;

    public $class;
    public $elementId;
    /**
     * Where the upload lands, which file it replaces and who hears about it
     * are all decided by the parent view at mount time. Locked so a client
     * can't point uploads or deletes at arbitrary paths on the public disk.
     */
    #[Locked]
    public $eventTarget;

    #[Locked]
    public $existingImage;

    #[Locked]
    public $fieldName;

    public $image;
    public $imageInput;

    #[Locked]
    public $imagePath;
    public $itemIndex = null;
    public $saved = false;
    public $title;

    public function mount()
    {
        $this->imageInput = $this->existingImage;
        $this->elementId = Str::random(8);
    }

    public function render()
    {
        return view('page-composer::livewire.image-upload-component');
    }

    private function imageRules(): array
    {
        return [
            'image' => 'required|image|max:1024', // 1MB Max
        ];
    }

    /**
     * Validate as soon as a file is picked. The view previews the pending
     * upload with temporaryUrl(), which throws for anything that isn't an
     * image, so a bad file is dropped before the next render.
     */
    public function updatedImage()
    {
        try {
            $this->validate($this->imageRules());
        } catch (ValidationException $e) {
            $this->reset('image');

            throw $e;
        }
    }

    public function saveImage()
    {
        $this->validate($this->imageRules());

        if ($this->imageExists()) {
            $this->addError('image', 'File already exists');
            $this->reset('image');

            return;
        }

        //Extension comes from the file's contents, not the client-supplied name
        $filename = Str::slug(pathinfo($this->image->getClientOriginalName(), PATHINFO_FILENAME))
            . '_' . Str::ulid() . '.' . $this->image->extension();
        $path = $this->imagePath . $filename;

        $this->image->storeAs($this->imagePath, $filename, 'public');
        $this->imageInput = $path;

        $this->saved = true;

        $this->dispatch('eventImageUploadComponentSaved.' . $this->eventTarget, field: $this->fieldName, imagePath: $path, itemIndex: $this->itemIndex);

        $this->existingImage = $path;

        $this->reset('image');
    }

    /**
     * Discard a pending upload that hasn't been saved yet.
     */
    public function deleteImage()
    {
        $this->reset('image');
    }

    public function imageExists()
    {
        return filled($this->existingImage) && Storage::disk('public')->exists($this->existingImage);
    }

    public function deleteExistingImage()
    {
        Storage::disk('public')->delete($this->existingImage);

        $this->dispatch('eventImageUploadComponentDeleted.' . $this->eventTarget, field: $this->fieldName, imagePath: $this->existingImage, itemIndex: $this->itemIndex);

        $this->reset('image', 'imageInput', 'existingImage');
    }
}
