<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Photo extends Component
{
    use WithFileUploads;

    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $photo, $elementId;

    public $target;

    public $breakpoints = [
        'none',
        'sm',
        'md',
        'lg',
        'xl',
        '2xl'
    ];

    protected $breakpointTemplates = [
        'none' => '',
        'sm' => '',
        'md' => '',
        'lg' => '',
        'xl' => '',
        '2xl' => '',
    ];

    public $activeTab = 0;

    public function mount()
    {
        $this->elementId = uniqid();
        if (
            !Arr::has($this->data['content'], 'objectFit') &&
            !Arr::has($this->data['content'], 'objectPosition') &&
            !Arr::has($this->data['content'], 'aspectRatio')
        ) {
            Arr::set($this->data, 'content.objectFit', $this->breakpointTemplates);
            Arr::set($this->data, 'content.objectPosition', $this->breakpointTemplates);
            Arr::set($this->data, 'content.aspectRatio', $this->breakpointTemplates);
        }
    }

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
    }

    public function deleteExistingPhoto()
    {
        Storage::delete('public/photos/' . $this->data['content']['photo']);
        Arr::set($this->data, 'content.photo', null);
    }

    public function deletePhoto()
    {
        $this->reset('photo');
    }


    public function setAspectRatio($aspectRatio, $breakpoint)
    {
        $breakpointClass = '';

        if ($breakpoint !== 'none') {
            $breakpointClass = $breakpoint . ':';
        }

        if ($aspectRatio == 'square') {
            Arr::set($this->data, 'content.aspectRatio.' . $breakpoint, $breakpointClass . 'aspect-w-1' . ' ' . $breakpointClass . 'aspect-h-1');
        }
        if ($aspectRatio == 'portrait') {
            Arr::set($this->data, 'content.aspectRatio.' . $breakpoint, $breakpointClass . 'aspect-w-1' . ' ' . $breakpointClass . 'aspect-h-2');
        }
        if ($aspectRatio == 'landscape') {
            Arr::set($this->data, 'content.aspectRatio.' . $breakpoint, $breakpointClass . 'aspect-w-16' . ' ' . $breakpointClass . 'aspect-h-9');
        }
    }

    public function setObjectFit($objectFit, $breakpoint)
    {
        $breakpointClass = '';

        if ($breakpoint !== 'none') {
            $breakpointClass = $breakpoint . ':';
        }
        $this->data['content']['objectFit'][$breakpoint] = $breakpointClass . $objectFit;
    }

    public function setObjectPosition($objectPosition, $breakpoint)
    {
        $breakpointClass = '';

        if ($breakpoint !== 'none') {
            $breakpointClass = $breakpoint . ':';
        }

        if ($objectPosition == 'right') {
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'left')) {
                $found = Arr::get($this->data, 'content.objectPosition.' . $breakpoint);
                $replace = Str::replace('left', 'right', $found);
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $replace);
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'top')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-right-top');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'bottom')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-right-bottom');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'center')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-right');
            }
        }
        if ($objectPosition == 'left') {
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'right')) {
                $found = Arr::get($this->data, 'content.objectPosition.' . $breakpoint);
                $replace = Str::replace('right', 'left', $found);
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $replace);
                //                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, Str::replace('left', 'right', Arr::get($this->data, 'content.objectPosition.' . $breakpoint)));
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'top')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-left-top');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'bottom')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-left-bottom');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'center')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-left');
            }
        }
        if ($objectPosition == 'top') {
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'bottom')) {
                $found = Arr::get($this->data, 'content.objectPosition.' . $breakpoint);
                $replace = Str::replace('bottom', 'top', $found);
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $replace);
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'left')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-left-top');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'right')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-right-top');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'center')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-top');
            }
        }
        if ($objectPosition == 'bottom') {
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'top')) {
                $found = Arr::get($this->data, 'content.objectPosition.' . $breakpoint);
                $replace = Str::replace('top', 'bottom', $found);
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $replace);
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'left')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-left-bottom');
            }

            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'right')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-right-bottom');
            }
            if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), 'center')) {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-bottom');
            }
        }
        if (Str::contains(Arr::get($this->data, 'content.objectPosition.' . $breakpoint), $objectPosition)) {
            Str::remove($objectPosition, Arr::get($this->data, 'content.objectPosition.' . $breakpoint));
            Str::replace('--', '-', Arr::get($this->data, 'content.objectPosition.' . $breakpoint));
            if (Arr::get($this->data, 'content.objectPosition.' . $breakpoint) == 'object-') {
                return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, null);
            }
        }
        if ($objectPosition == 'center') {
            return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-center');
        }
        if (is_null(Arr::get($this->data, 'content.objectPosition.' . $breakpoint)) || Arr::get($this->data, 'content.objectPosition.' . $breakpoint) == '') {
            return Arr::set($this->data, 'content.objectPosition.' . $breakpoint, $breakpointClass . 'object-' . $objectPosition);
        }
    }

    public function savePhoto()
    {
        //Delete existing photo if replaced
        if (isset($this->data['content']['photo']) && !is_null($this->data['content']['photo'])) {
            $this->deleteExistingPhoto();
            $this->reset('existingPhoto');
        }
        //Randomize filename
        $filename = basename($this->photo->getClientOriginalName(), '.' . $this->photo->getClientOriginalExtension());
        $filename = Str::slug($filename) . '_' . uniqid() . '.' . $this->photo->getClientOriginalExtension();
        //Save photo
        $this->photo->storeAs('public/photos/', $filename);
        $this->data['content']['photo'] = $filename;

        $this->reset('photo');
    }

    public function hasContent()
    {
        return !empty($this->data['content']['photo']);
    }

    public function render()
    {
        return view('livewire.page-composer-elements.photo');
    }
}
