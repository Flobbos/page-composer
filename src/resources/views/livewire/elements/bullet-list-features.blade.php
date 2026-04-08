<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="space-y-4">
        <div>
            <label class="block mb-2 text-xs font-medium text-gray-700">Headline</label>
            <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model="data.content.headline" />
        </div>

        @foreach (Arr::get($data, 'content.features', []) as $featureIndex => $feature)
            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700">Feature {{ $featureIndex + 1 }}</h4>
                    <button class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-500" type="button" wire:click="removeFeature({{ $featureIndex }})">Remove</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Icon (optional class or emoji)"
                        wire:model="data.content.features.{{ $featureIndex }}.icon" />
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Title"
                        wire:model="data.content.features.{{ $featureIndex }}.title" />
                    <textarea class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg col-span-2" rows="3" placeholder="Description"
                        wire:model="data.content.features.{{ $featureIndex }}.description"></textarea>
                </div>
            </div>
        @endforeach

        <button class="px-3 py-2 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-500" type="button" wire:click="addFeature">Add Feature</button>
    </div>

</x-page-composer::base-element>
