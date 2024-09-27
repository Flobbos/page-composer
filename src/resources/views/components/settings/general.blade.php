<x-page-composer::settings-box target="mainSettings">

    <x-slot name="icon">
        <x-heroicon-o-adjustments-horizontal class="w-5 h-5" />
    </x-slot>

    <x-slot name="title">
        {{ __('Page Details') }}
    </x-slot>

    <x-slot name="content">

        <div class="grid grid-cols-6 gap-6 mb-6">

            <div class="col-span-6 sm:col-span-3">
                <label for="page_title" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Title (internal)') }}</label>
                <input type="text" name="name" id="page_title" wire:model.lazy="page.name" autocomplete="given-name"
                    class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
            </div>

            <div class="col-span-6 sm:col-span-3">
                <livewire:date-picker :key="uniqid()" :datepickerValue="$displayDate" />
            </div>

        </div>

        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
                <label for="category" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Category') }}</label>
                <livewire:select-input name="category_id" placeholder="{{ __('Select category') }}" :options="$categories" :selected="$pageCategory" labelBy="name" :key="uniqid()" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <label for="tags" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Tags') }}</label>
                <livewire:multi-select-input name="tags" placeholder="{{ __('Select tags') }}" :options="$tags" :key="uniqid()" labelBy="name" eventName="tagsUpdated" :selected="$pageTags" />
            </div>

        </div>

    </x-slot>

</x-page-composer::settings-box>
