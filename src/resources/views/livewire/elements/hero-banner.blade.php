<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="flex flex-col space-y-4">
        <div>
            <label class="block mb-2 text-xs font-medium text-gray-700">Background Image URL</label>
            <input class="block w-full px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model="data.content.bgImageUrl" />
        </div>
        <div>
            <label class="block mb-2 text-xs font-medium text-gray-700">Headline</label>
            <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model="data.content.headline" />
        </div>
        <div>
            <label class="block mb-2 text-xs font-medium text-gray-700">Subheadline</label>
            <textarea class="block w-full px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl"
                wire:model="data.content.subheadline"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-2 text-xs font-medium text-gray-700">CTA Label</label>
                <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                    wire:model="data.content.ctaLabel" />
            </div>
            <div>
                <label class="block mb-2 text-xs font-medium text-gray-700">CTA URL</label>
                <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                    wire:model="data.content.ctaUrl" />
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-2 text-xs font-medium text-gray-700">CTA Target</label>
                <select class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl"
                    wire:model="data.content.ctaTarget">
                    <option value="_self">Same tab</option>
                    <option value="_blank">New tab</option>
                </select>
            </div>
            <div>
                <label class="block mb-2 text-xs font-medium text-gray-700">Overlay Opacity (0-90)</label>
                <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="number" min="0" max="90"
                    wire:model="data.content.overlayOpacity" />
            </div>
        </div>
    </div>

</x-page-composer::base-element>
