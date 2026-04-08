<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="space-y-5">
        <div>
            <label class="block mb-2 text-xs font-medium text-gray-700">Headline</label>
            <input class="block w-full h-12 px-5 mt-1 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model="data.content.headline" />
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-700">Testimonials</h4>
            @foreach (Arr::get($data, 'content.testimonials', []) as $testimonialIndex => $testimonial)
                <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="text-xs font-semibold text-gray-700">Item {{ $testimonialIndex + 1 }}</h5>
                        <button class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-500" type="button" wire:click="removeTestimonial({{ $testimonialIndex }})">Remove</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Name"
                            wire:model="data.content.testimonials.{{ $testimonialIndex }}.name" />
                        <input class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Role"
                            wire:model="data.content.testimonials.{{ $testimonialIndex }}.role" />
                        <textarea class="block w-full px-3 py-2 text-sm border-gray-300 rounded-lg col-span-2" rows="3" placeholder="Quote"
                            wire:model="data.content.testimonials.{{ $testimonialIndex }}.quote"></textarea>
                    </div>
                </div>
            @endforeach
            <button class="px-3 py-2 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-500" type="button" wire:click="addTestimonial">Add Testimonial</button>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-700">Trust Badges / Stats</h4>
            @foreach (Arr::get($data, 'content.badges', []) as $badgeIndex => $badge)
                <div class="grid grid-cols-12 gap-3 p-3 border border-gray-200 rounded-xl bg-gray-50">
                    <input class="block w-full col-span-5 px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Label"
                        wire:model="data.content.badges.{{ $badgeIndex }}.label" />
                    <input class="block w-full col-span-5 px-3 py-2 text-sm border-gray-300 rounded-lg" type="text" placeholder="Value (e.g. GMP, FDA, 25+)"
                        wire:model="data.content.badges.{{ $badgeIndex }}.value" />
                    <button class="col-span-2 px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-500" type="button" wire:click="removeBadge({{ $badgeIndex }})">Remove</button>
                </div>
            @endforeach
            <button class="px-3 py-2 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-500" type="button" wire:click="addBadge">Add Badge/Stat</button>
        </div>
    </div>

</x-page-composer::base-element>
