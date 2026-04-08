<x-page-composer::settings-box target="mediaSettings">
    <x-slot name="icon">
        <x-heroicon-o-photo class="w-5 h-5" />
    </x-slot>

    <x-slot name="title">
        {{ __('Media') }}
    </x-slot>

    <x-slot name="content">
        <div class="flex flex-col gap-4">
            <livewire:image-upload-component existingImage="{{ $photo }}" eventTarget="pageComposer.mainPhoto" imagePath="photos/" key="upload-main-photo" title="Main Photo" />

            <div class="flex gap-4">
                <livewire:image-upload-component class="w-1/2" existingImage="{{ $sliderImage }}" eventTarget="pageComposer.sliderImage" imagePath="photos/" key="upload-slider-photo" title="Slider Photo" />

                <livewire:image-upload-component class="w-1/2" existingImage="{{ $newsletterImage }}" eventTarget="pageComposer.newsletterImage" imagePath="photos/" key="upload-newsletter-photo" title="Newsletter Photo" />
            </div>
        </div>
    </x-slot>
</x-page-composer::settings-box>
