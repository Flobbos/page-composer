<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="flex flex-col">
        <div class="mb-4">
            <label class="block mb-2 text-xs font-medium text-gray-700">Headline</label>
            <input class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model.defer="data.content.headline" />
        </div>
        <div x-data="quillEditor({})">
            <input type="hidden" x-ref="input" wire:model.defer="data.content.text">

            <div wire:ignore>
                <div x-ref="editor">{!! $data['content']['text'] ?? null !!}</div>
            </div>
        </div>
    </div>

</x-page-composer::base-element>
