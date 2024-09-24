<x-settings-box target="metaSettings">

    <x-slot name="icon">
        <x-heroicon-o-speakerphone class="w-5 h-5" />
    </x-slot>

    <x-slot name="title">
        {{ __('Meta') }}
        <x-language-label currentLanguage="{{ $attributes->get('locale') }}" />
    </x-slot>

    <x-slot name="content">
        <div class="grid grid-cols-6 gap-6 mb-6">

            <div class="col-span-6 sm:col-span-6">
                <label for="title" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Titel') }}</label>
                <input type="text" name="title" id="title" autocomplete="given-name" wire:model.lazy="pageTranslations.{{ $attributes->get('locale') }}.content.title"
                    class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label for="meta_title" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Meta-Titel') }}</label>
                <input type="text" name="meta_title" id="meta_title" autocomplete="given-name" wire:model.lazy="pageTranslations.{{ $attributes->get('locale') }}.content.meta_title"
                    class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label for="title" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Meta-Beschreibung') }}</label>
                <textarea wire:model.lazy="pageTranslations.{{ $attributes->get('locale') }}.content.meta_description" name="meta_description" id="meta_description" autocomplete="given-name"
                    class="block w-full px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl"></textarea>
            </div>

            <div class="col-span-6 sm:col-span-6">
                <label for="title" class="block mb-2 text-xs font-medium text-gray-700">{{ __('Meta-Keywords') }}</label>
                <input type="text" wire:model.lazy="pageTranslations.{{ $attributes->get('locale') }}.content.keywords" name="keywords" id="keywords" autocomplete="given-name"
                    class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
            </div>

        </div>

    </x-slot>

</x-settings-box>
