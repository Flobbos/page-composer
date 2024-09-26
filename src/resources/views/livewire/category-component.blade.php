<div x-data="{
    showCategoryWindow: @entangle('showCategoryWindow'),
    showCategoryList: @entangle('showCategoryList'),
    showCategoryCreate: @entangle('showCategoryCreate'),
    hideElement() {
        this.showCategoryWindow = false
        this.showCategoryList = true
        this.showCategoryCreate = false
    }
}" @click.outside="hideElement" @keydown.escape.window="hideElement">
    <button @click="showCategoryWindow = ! showCategoryWindow" class="flex items-center px-4 py-1 space-x-1 text-sm text-white transition bg-indigo-600 rounded-full hover:bg-indigo-400 focus:outline-none">
        <x-heroicon-o-list-bullet class="w-5 h-5" />
        <span>{{ __('Categories') }}</span>
    </button>
    <div x-cloak x-show="showCategoryWindow" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 blur transform -translate-x-10" x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 blur transform translate-x-10"
        class="absolute z-30 flex-col w-1/2 overflow-hidden transform -translate-x-1 bg-white shadow-2xl top-20 rounded-xl left-32">
        <!-- header -->
        <div class="flex items-center h-16 px-8 py-10 space-x-2 text-gray-600">

            <div class="flex items-center w-full space-x-2 text-xl font-semibold font-title">
                <x-heroicon-o-list-bullet class="w-5 h-5 text-indigo-600" />
                <span>
                    {{ __('Categories') }}
                </span>
                <button @click="showCategoryCreate = true ; showCategoryList = false" class="text-gray-700 transition hover:text-indigo-600">
                    <x-heroicon-o-plus class="w-6 h-6" />
                </button>

            </div>

            <div @click="hideElement" class="transition duration-500 transform cursor-pointer hover:rotate-90 hover:text-indigo-600">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </div>

        </div>

        <!-- main content -->
        <div x-show="showCategoryList && !showCategoryCreate" class="flex flex-col w-full px-8 pb-10 space-y-4 divide-y divide-gray-100">
            @forelse($categories as $category)
                <div class="flex w-full pt-2 text-md">
                    <div class="p-1 text-indigo-400">
                        {!! $category->icon !!}
                    </div>
                    <div class="w-full p-1">
                        {{ $category->name }}
                    </div>
                    <div class="flex space-x-1">
                        <x-page-composer.button wire:click="editCategory({{ $category->id }})" class="lg:text-xs" primary>
                            {{ __('Edit') }}
                        </x-page-composer.button>
                        <x-page-composer.button wire:click="deleteCategory({{ $category->id }})" class="text-white bg-red-500 lg:text-xs hover:bg-red-600">
                            {{ __('Delete') }}
                        </x-page-composer.button>
                    </div>
                </div>
            @empty
                No Categories
            @endforelse
        </div>
        <div x-show="showCategoryCreate && !showCategoryList" class="w-full px-8 pb-4">
            <h5 class="mb-5 font-semibold font-title">
                @if (is_null($category_id))
                    {{ __('Add Category') }}
                @else
                    {{ __('Edit Category') }}
                @endif
            </h5>
            @forelse($languages as $lang)
                <div class="mb-4">
                    <x-page-composer.label for="name">
                        {{ __('Name') }} ({{ $lang->locale }})
                    </x-page-composer.label>
                    <x-page-composer.input wire:model="content.{{ $lang->id }}.name" id="category_name_{{ $lang->id }}" />
                    <input type="hidden" wire:model="content.{{ $lang->id }}.category_translation_id" />
                </div>
            @empty
                <div class="mb-4">
                    {{ __('No languages present') }}
                </div>
            @endforelse
        </div>
        <div x-show="showCategoryCreate && !showCategoryList" class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">

            @if (is_null($category_id))
                <x-page-composer.button wire:click="saveCategory" primary>
                    {{ __('Save') }}
                </x-page-composer.button>
            @else
                <x-page-composer.button wire:click="updateCategory({{ $category_id }})" primary>
                    {{ __('Update') }}
                </x-page-composer.button>
            @endif

            <x-page-composer.button wire:click="cancelEdit">
                {{ __('Cancel') }}
            </x-page-composer.button>

        </div>
    </div>
</div>
