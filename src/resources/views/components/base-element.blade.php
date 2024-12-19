@props(['hasContent' => false, 'previewMode' => false, 'sorting' => 1, 'elementData' => []])
<div x-data="{ hoverEdit: false }" class="flex w-full py-1">
    @if (!$previewMode || !$hasContent)
        <div class="flex w-full p-1 @if ($hasContent) bg-gray-300 @else bg-gray-100 @endif rounded-full cursor-pointer hover:bg-gray-200">
            <div class="flex">
                @if ($sorting['up'])
                    <button wire:click="$parent.sortElementUp({{ $itemKey }})" class="p-1 text-gray-600 transition rounded-full bg-gray-50 hover:bg-green-400 hover:text-green-100 focus:outline-none">
                        <x-heroicon-o-arrow-up class="w-4 h-4" />
                    </button>
                @endif
                @if ($sorting['down'])
                    <button wire:click="$parent.sortElementDown({{ $itemKey }})" class="p-1 text-gray-600 transition rounded-full bg-gray-50 hover:bg-green-400 hover:text-green-100 focus:outline-none">
                        <x-heroicon-o-arrow-down class="w-4 h-4" />
                    </button>
                @endif
            </div>

            {{-- Toggle element inputs --}}
            <div wire:click="$toggle('showElementInputs')" class="flex justify-center w-full pt-1 pr-2 text-sm text-indigo-400">
                <span class="mr-5">{!! Arr::get($elementData, 'icon') !!}</span>
                {{ Arr::get($elementData, 'name') }}
            </div>
            {{-- Delete item --}}
            <div>
                <button class="p-1 text-gray-600 transition rounded-full bg-gray-50 hover:bg-red-400 hover:text-red-100 focus:outline-none" wire:click="$parent.deleteElement({{ $itemKey }})">
                    <x-heroicon-o-trash class="w-4 h-4 stroke-current" />
                </button>
            </div>
        </div>
    @else
        <div @mouseenter="hoverEdit = true" @mouseleave="hoverEdit = false" class="relative w-full hover:bg-gray-100">
            <x-dynamic-component :component="'page-composer-elements.' . $elementData['component']" :content="$elementData['content']" />
            <!-- edit options -->
            <div wire:click="$toggle('showElementInputs')" x-cloak x-show="hoverEdit" class="absolute top-0 right-0 px-2 py-1 rounded-b-l">
                <button class="relative z-10 flex items-center justify-center w-6 h-6 transition bg-white rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
    <x-page-composer::page-composer.dialog-modal :id="uniqid()" maxWidth="xxl" wire:model="showElementInputs">
        <x-slot name="title">{{ Arr::get($elementData, 'name') }}</x-slot>
        <x-slot name="content">
            {{ $slot ?? '' }}
        </x-slot>
        <x-slot name="footer">
            <x-page-composer::page-composer.danger-button wire:click="$toggle('showElementInputs')">Cancel
            </x-page-composer::page-composer.danger-button>
            <x-page-composer::page-composer.button wire:click="updateData">Save</x-page-composer::page-composer.button>
        </x-slot>
    </x-page-composer::page-composer.dialog-modal>
</div>
