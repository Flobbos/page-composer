<x-page-composer::settings-box target="mediaSettings">

    <x-slot name="icon">
        <x-heroicon-o-photograph class="w-5 h-5" />
    </x-slot>

    <x-slot name="title">
        {{ __('Medien') }}
    </x-slot>

    <x-slot name="content">

        <div class="grid grid-cols-2 gap-4">
            <livewire:image-upload-component title="Main Photo" photoPath="photos" existingPhoto="{{ $page->photo }}" eventTarget="photo" :key="uniqid()" />
            <livewire:image-upload-component title="Slider Photo" photoPath="photos" eventTarget="slider_image" existingPhoto="{{ $page->slider_image }}" :key="uniqid()" />
            <livewire:image-upload-component title="Newsletter Photo" photoPath="photos" eventTarget="newsletter_image" existingPhoto="{{ $page->newsletter_image }}" :key="uniqid()" class="col-span-2" />
        </div>


    </x-slot>

</x-page-composer::settings-box>
