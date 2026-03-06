<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-2 text-xs font-medium text-gray-700">Headline</label>
                <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                    wire:model.defer="data.content.headline" />
            </div>
            <div>
                <label class="block mb-2 text-xs font-medium text-gray-700">Columns (1-4)</label>
                <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="number" min="1" max="4"
                    wire:model.defer="data.content.columns" />
            </div>
        </div>

        @foreach (Arr::get($data, 'content.cards', []) as $cardIndex => $card)
            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700">Card {{ $cardIndex + 1 }}</h4>
                    <button class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-500" type="button" wire:click="removeCard({{ $cardIndex }})">Remove</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Title" wire:model.defer="data.content.cards.{{ $cardIndex }}.title" />
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Icon (optional class or emoji)"
                        wire:model.defer="data.content.cards.{{ $cardIndex }}.icon" />
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Image URL (optional)"
                        wire:model.defer="data.content.cards.{{ $cardIndex }}.imageUrl" />
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Read more URL"
                        wire:model.defer="data.content.cards.{{ $cardIndex }}.linkUrl" />
                    <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg col-span-2" type="text" placeholder="Read more label"
                        wire:model.defer="data.content.cards.{{ $cardIndex }}.linkLabel" />
                    <textarea class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg col-span-2" rows="3" placeholder="Description"
                        wire:model.defer="data.content.cards.{{ $cardIndex }}.description"></textarea>
                </div>
            </div>
        @endforeach

        <button class="px-3 py-2 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-500" type="button" wire:click="addCard">Add Card</button>
    </div>

</x-page-composer::base-element>
