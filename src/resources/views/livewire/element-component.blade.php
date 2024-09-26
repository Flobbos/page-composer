<div x-data="{
    showElementWindow: @entangle('showElementWindow'),
    showElementList: @entangle('showElementList'),
    showElementCreate: @entangle('showElementCreate'),
    hideElement() {
        this.showElementWindow = false
        this.showElementList = true
        this.showElementCreate = false
    }
}" @click.outside="hideElement" @keydown.escape.window="hideElement">
    <button @click="showElementWindow = ! showElementWindow" class="flex items-center px-4 py-1 space-x-1 text-sm text-white transition bg-indigo-600 rounded-full hover:bg-indigo-400 focus:outline-none">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z"
                clip-rule="evenodd"></path>
        </svg>
        <span>{{ __('Elements') }}</span>
    </button>
    <div x-cloak x-show="showElementWindow" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 blur transform -translate-x-10" x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 blur transform translate-x-10"
        class="absolute z-30 flex-col w-1/2 overflow-hidden transform -translate-x-1 bg-white shadow-2xl top-20 rounded-xl">
        <!-- header -->
        <div class="flex items-center h-16 px-8 py-10 space-x-2 text-gray-600">

            <div class="flex items-center w-full space-x-2 text-xl font-semibold font-title">
                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z"
                        clip-rule="evenodd"></path>
                </svg>

                <span>
                    {{ __('Elements') }}
                </span>

                <button @click="showElementCreate = true ; showElementList = false" class="text-gray-700 transition hover:text-indigo-600">
                    <x-heroicon-o-plus class="w-6 h-6" />
                </button>

            </div>

            <div @click="hideElement" class="transition duration-500 transform cursor-pointer hover:rotate-90 hover:text-indigo-600">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </div>

        </div>

        <!-- main content -->
        <div x-show="showElementList && !showElementCreate" class="flex flex-col w-full px-8 pb-10 space-y-4 divide-y divide-gray-100">
            @forelse($elements as $element)
                <div class="flex w-full pt-2 text-md">
                    <div class="p-1 text-indigo-400">
                        {!! $element->icon !!}
                    </div>
                    <div class="w-full p-1">
                        {{ $element->name }}
                    </div>
                    <div>
                        <x-page-composer::page-composer.button wire:click="editElement({{ $element->id }})" class="lg:text-xs" primary>
                            {{ __('Edit') }}
                        </x-page-composer::page-composer.button>
                    </div>
                </div>
            @empty
                No Elements
            @endforelse
        </div>
        <div x-show="showElementCreate && !showElementList" class="w-full px-8 pb-4">
            <h5 class="mb-5 font-semibold font-title">
                @if (is_null($element_id))
                    {{ __('Add Element') }}
                @else
                    {{ __('Edit Element') }}
                @endif
            </h5>
            <div class="mb-4">
                <x-page-composer::page-composer.label for="name">
                    {{ __('Name') }}
                </x-page-composer::page-composer.label>
                <x-page-composer::page-composer.input wire:model="name" id="element_name" />
            </div>
            <div class="mb-4">
                <x-page-composer::page-composer.label for="component">
                    {{ __('Component') }}
                </x-page-composer::page-composer.label>
                <x-page-composer::page-composer.input disabled value="{{ $this->component }}" id="component" />
            </div>
            <div class="mb-4">
                <x-page-composer::page-composer.label for="icon">
                    {{ __('Icon') }}
                </x-page-composer::page-composer.label>
                <x-page-composer::page-composer.textarea wire:model="icon" id="icon" />
            </div>
            <div class="flex justify-between w-full">

            </div>
        </div>
        <div x-show="showElementCreate && !showElementList" class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">

            @if (is_null($element_id))
                <x-page-composer::page-composer.button wire:click="saveElement" primary>
                    {{ __('Save') }}
                </x-page-composer::page-composer.button>
            @else
                <x-page-composer::page-composer.button wire:click="updateElement({{ $element_id }})" primary>
                    {{ __('Update') }}
                </x-page-composer::page-composer.button>
            @endif

            <x-page-composer::page-composer.button wire:click="cancelEdit">
                {{ __('Cancel') }}
            </x-page-composer::page-composer.button>

        </div>
    </div>
</div>
