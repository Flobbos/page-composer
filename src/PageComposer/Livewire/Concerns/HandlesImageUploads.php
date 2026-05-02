<?php

namespace Flobbos\PageComposer\Livewire\Concerns;

use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

/**
 * @property array|null $page
 */
trait HandlesImageUploads
{
    private const PHOTO_FIELDS = ['photo', 'newsletter_image', 'slider_image'];

    #[Locked]
    public ?string $photo = null;

    #[Locked]
    public ?string $newsletter_image = null;

    #[Locked]
    public ?string $slider_image = null;

    #[On('eventImageUploadComponentSaved.pageComposer.mainPhoto')]
    #[On('eventImageUploadComponentSaved.pageComposer.newsletterImage')]
    #[On('eventImageUploadComponentSaved.pageComposer.sliderImage')]
    public function imageSaved(string $field, string $imagePath, ?int $itemIndex = null): void
    {
        $this->setPhotoField($field, $imagePath);
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.mainPhoto')]
    #[On('eventImageUploadComponentDeleted.pageComposer.newsletterImage')]
    #[On('eventImageUploadComponentDeleted.pageComposer.sliderImage')]
    public function imageDeleted(string $field, ?string $imagePath = null, ?int $itemIndex = null): void
    {
        $this->setPhotoField($field, null);
    }

    private function setPhotoField(string $field, ?string $value): void
    {
        if (!in_array($field, self::PHOTO_FIELDS, true)) {
            return;
        }

        $this->{$field} = $value;
        $this->pageData[$field] = $value;
    }
}
