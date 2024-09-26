<x-base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="flex flex-col">
        <div class="mb-4">
            <label class="block mb-2 text-xs font-medium text-gray-700">Alt-Tag</label>
            <input class="block w-full px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model.defer="data.content.alt_tag" />
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-xs font-medium text-gray-700">Caption</label>
            <input type="text" class="block w-full px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" type="text"
                wire:model.defer="data.content.caption" />
        </div>
        <div x-data="{
            activeTab: @entangle('activeTab'),
            select(id) {
                this.activeTab = id
            },
            isSelected(id) {
                return this.activeTab === id
            },
        }" class="w-full mb-5 text-sm">
            <!-- Tab List -->
            <ul x-ref="tablist" role="tablist" class="flex items-stretch -mb-px" wire:ignore>
                <!-- Tab -->
                @foreach ($breakpoints as $key => $breakpoint)
                    <li>
                        <button wire:key="tab-{{ $breakpoint }}" @mousedown.prevent @click="select({{ $key }})" @focus="select({{ $key }})" type="button" :tabindex="isSelected({{ $key }}) ? 0 : -1"
                            :class="isSelected({{ $key }}) ? 'border-gray-200 bg-white' : 'border-transparent'" class="inline-flex rounded-t-md border-t border-l border-r px-5 py-2.5" role="tab">
                            {{ $breakpoint }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Panels -->
            <div role="tabpanels" class="bg-white border border-gray-200 rounded-b-md" wire:ignore.self>
                <!-- Panel -->
                @foreach ($breakpoints as $key => $breakpoint)
                    <section x-show="isSelected({{ $key }})" role="tabpanel" class="p-4">
                        <div class="flex flex-col mb-4 space-y-2">
                            <h4>Aspect Ratio</h4>
                            <div class="flex space-x-4">
                                @if ($key == 0)
                                    <div wire:click="setAspectRatio('square', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            'aspect-w-1 aspect-h-1',
                                        'bg-gray-50 text-black' =>
                                            !Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            'aspect-w-1 aspect-h-1',
                                    ])>
                                        Quadratisch
                                    </div>
                                    <div wire:click="setAspectRatio('portrait', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            'aspect-w-1 aspect-h-2',
                                        'bg-gray-50 text-black' =>
                                            !Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            'aspect-w-1 aspect-h-2',
                                    ])>
                                        Hochformat
                                    </div>
                                    <div wire:click="setAspectRatio('landscape', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            'aspect-w-16 aspect-h-9',
                                        'bg-gray-50 text-black' =>
                                            !Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            'aspect-w-16 aspect-h-9',
                                    ])>
                                        Querformat
                                    </div>
                                @else
                                    <div wire:click="setAspectRatio('square', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            $breakpoint . ':' . 'aspect-w-1 ' . $breakpoint . ':' . 'aspect-h-1',
                                        'bg-gray-50 text-black' =>
                                            !Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            $breakpoint . ':' . 'aspect-w-1 ' . $breakpoint . ':' . 'aspect-h-1',
                                    ])>
                                        Quadratisch
                                    </div>
                                    <div wire:click="setAspectRatio('portrait', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            $breakpoint . ':' . 'aspect-w-1 ' . $breakpoint . ':' . 'aspect-h-2',
                                        'bg-gray-50 text-black' =>
                                            !Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            $breakpoint . ':' . 'aspect-w-1 ' . $breakpoint . ':' . 'aspect-h-2',
                                    ])>
                                        Hochformat
                                    </div>
                                    <div wire:click="setAspectRatio('landscape', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            $breakpoint . ':' . 'aspect-w-16 ' . $breakpoint . ':' . 'aspect-h-9',
                                        'bg-gray-50 text-black' =>
                                            !Arr::get($data, 'content.aspectRatio.' . $breakpoint) ==
                                            $breakpoint . ':' . 'aspect-w-16 ' . $breakpoint . ':' . 'aspect-h-9',
                                    ])>
                                        Querformat
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col mb-4 space-y-2">
                            <h4>Object-Fit</h4>
                            <div class="flex space-x-4">
                                @if ($key == 0)
                                    <div wire:click="setObjectFit('object-cover', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) == 'object-cover',
                                        'bg-gray-50 text-black' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) !== 'object-cover',
                                    ])>

                                        Cover
                                    </div>
                                    <div wire:click="setObjectFit('object-contain', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) == 'object-contain',
                                        'bg-gray-50 text-black' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) !==
                                            'object-contain',
                                    ])>

                                        Contain
                                    </div>
                                    <div wire:click="setObjectFit('object-none', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) == 'object-none',
                                        'bg-gray-50 text-black' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) !== 'object-none',
                                    ])>

                                        None
                                    </div>
                                @else
                                    <div wire:click="setObjectFit('object-cover', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) ==
                                            $breakpoint . ':' . 'object-cover',
                                        'bg-gray-50 text-black' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) !==
                                            $breakpoint . ':' . 'object-cover',
                                    ])>
                                        Cover
                                    </div>
                                    <div wire:click="setObjectFit('object-contain', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) ==
                                            $breakpoint . ':' . 'object-contain',
                                        'bg-gray-50 text-black' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) !==
                                            $breakpoint . ':' . 'object-contain',
                                    ])>
                                        Contain
                                    </div>
                                    <div wire:click="setObjectFit('object-none', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) ==
                                            $breakpoint . ':' . 'object-none',
                                        'bg-gray-50 text-black' =>
                                            Arr::get($data, 'content.objectFit.' . $breakpoint) !==
                                            $breakpoint . ':' . 'object-none',
                                    ])>
                                        None
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col mb-4 space-y-2">
                            <h4>Object-Position</h4>
                            <div class="flex space-x-4">
                                @if ($key == 0)
                                    <div wire:click="setObjectPosition('left', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' => Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'left'),
                                        'bg-gray-50 text-black' => !Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'left'),
                                    ])>
                                        Left
                                    </div>
                                    <div wire:click="setObjectPosition('right', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' => Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'right'),
                                        'bg-gray-50 text-black' => !Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'right'),
                                    ])>Right
                                    </div>
                                    <div wire:click="setObjectPosition('center', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' => Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'center'),
                                        'bg-gray-50 text-black' => !Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'center'),
                                    ])>Center
                                    </div>
                                    <div wire:click="setObjectPosition('top', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' => Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'top'),
                                        'bg-gray-50 text-black' => !Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'top'),
                                    ])>Top
                                    </div>
                                    <div wire:click="setObjectPosition('bottom', '{{ $breakpoint }}')" @class([
                                        'hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer',
                                        'bg-gray-600 text-gray-50' => Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'bottom'),
                                        'bg-gray-50 text-black' => !Str::contains(
                                            Arr::get($data, 'content.objectPosition.' . $breakpoint),
                                            'bottom'),
                                    ])>Bottom
                                    </div>
                                @else
                                    <div wire:click="setObjectPosition('left', '{{ $breakpoint }}')"
                                        class="{{ Str::contains($data['content']['objectPosition'][$breakpoint], 'left') ? 'bg-gray-600 text-gray-50' : 'bg-gray-50 text-black' }} hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer">
                                        Left
                                    </div>
                                    <div wire:click="setObjectPosition('right', '{{ $breakpoint }}')"
                                        class="{{ Str::contains($data['content']['objectPosition'][$breakpoint], 'right') ? 'bg-gray-600 text-gray-50' : 'bg-gray-50 text-black' }} hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer">
                                        Right
                                    </div>
                                    <div wire:click="setObjectPosition('center', '{{ $breakpoint }}')"
                                        class="{{ Str::contains($data['content']['objectPosition'][$breakpoint], 'center') ? 'bg-gray-600 text-gray-50' : 'bg-gray-50 text-black' }} hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer">
                                        Center
                                    </div>
                                    <div wire:click="setObjectPosition('top', '{{ $breakpoint }}')"
                                        class="{{ Str::contains($data['content']['objectPosition'][$breakpoint], 'top') ? 'bg-gray-600 text-gray-50' : 'bg-gray-50 text-black' }} hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer">
                                        Top
                                    </div>
                                    <div wire:click="setObjectPosition('bottom', '{{ $breakpoint }}')"
                                        class="{{ Str::contains($data['content']['objectPosition'][$breakpoint], 'bottom') ? 'bg-gray-600 text-gray-50' : 'bg-gray-50 text-black' }} hover:bg-gray-600 hover:text-gray-50 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-4 rounded-xl cursor-pointer">
                                        Bottom
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">
                Photo
            </label>
            <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                <div class="space-y-1 text-center">
                    @if ($photo)
                        <div class="relative rounded-lg">
                            <x-heroicon-o-x-mark-circle class="absolute w-6 h-6 text-red-600 bg-white rounded-full shadow-md cursor-pointer -right-2 -top-2 hover:text-red-900" wire:click="deletePhoto" />
                            <x-heroicon-o-check-circle class="absolute w-6 h-6 text-green-600 bg-white rounded-full shadow-md cursor-pointer right-4 -top-2 hover:text-green-900" wire:click="savePhoto" />
                            <img class="w-32 h-32 mx-auto rounded-lg" src="{{ $photo->temporaryUrl() }}">
                        </div>
                    @elseif ($data['content']['photo'] ?? null)
                        <div class="relative rounded-lg">
                            <x-heroicon-o-x-mark-circle class="absolute w-6 h-6 text-red-600 bg-white rounded-full shadow-md cursor-pointer -right-2 -top-2 hover:text-red-900" wire:click="deleteExistingPhoto" />
                            <img class="w-32 h-32 mx-auto rounded-lg" src="{{ asset('storage/photos/' . $data['content']['photo']) }}">
                        </div>
                    @else
                        <x-heroicon-o-photo class="w-12 h-12 mx-auto text-gray-400" />
                        <div class="flex text-sm text-gray-600">
                            <label for="file-upload-{{ $elementId }}"
                                class="relative font-medium text-indigo-600 bg-white rounded-md cursor-pointer hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload a file</span>
                                <input id="file-upload-{{ $elementId }}" name="photo" type="file" class="sr-only" wire:model="photo">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            PNG, JPG, GIF up to 1MB
                        </p>
                    @endif
                    @error('photo')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

</x-base-element>
