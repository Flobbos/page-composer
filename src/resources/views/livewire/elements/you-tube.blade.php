<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="flex flex-col">
        @dump($this->hasContent())
        <div class="mb-4">
            <label class="block mb-2 text-xs font-medium text-gray-700">YouTube Embed Link</label>
            <input class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model.lazy="data.content.videoUrl" />
            <x-page-composer::page-composer.input-error for="data.content.videoUrl" />
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-xs font-medium text-gray-700">Video Caption</label>
            <input class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model.defer="data.content.videoCaption" />
        </div>
        @if (Arr::get($data, 'content.videoUrl'))
            <div class="flex justify-center">
                <iframe width="560" height="315" src="{{ Arr::get($data, 'content.videoUrl') }}" title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        @endif
    </div>

</x-page-composer::base-element>
