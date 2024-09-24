<div class="flex flex-col">
    <div class="mb-4">
        <label class="inline-block mb-2">Headline</label>
        <input
            class="block w-full px-3 py-2 text-base font-normal leading-normal text-gray-700 bg-white border border-gray-400 rounded"
            type="text" wire:model.lazy="data.content.headline" />
    </div>
    <div class="mb-2">
        <label class="inline-block mb-2">Text</label>
        <textarea
            class="block w-full px-3 py-2 text-base font-normal leading-normal text-gray-700 bg-white border border-gray-400 rounded"
            type="text" wire:model.lazy="data.content.text"></textarea>
    </div>
</div>
