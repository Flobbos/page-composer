<div class="fixed inset-0 z-30 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data="{ showLanguageAdd: @entangle('showLanguageAdd') }" x-cloak x-show="showLanguageAdd">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-cloak x-show="showLanguageAdd" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-out duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-cloak x-show="showLanguageAdd" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="showLanguageAdd = false"
            class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <div class="flex items-center h-16 px-8 py-10 space-x-2 text-gray-600">
                <x-heroicon-o-globe-alt class="w-6 h-6 text-indigo" />

                <div class="flex items-center w-full space-x-1 text-xl font-semibold font-title">
                    {{ __('Add new language') }}
                </div>

                <div @click="showLanguageAdd = false" class="transition duration-500 transform cursor-pointer hover:rotate-90 hover:text-indigo-600">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </div>
            </div>

            <div class="px-8">

                <div class="grid grid-cols-6 gap-6 mb-6">

                    <div class="col-span-6">
                        <x-page-composer.label for="language_name">{{ __('Name') }}</x-page-composer.label>
                        <x-page-composer.input wire:model="name" id="language_name" />
                        @error('name')
                            <span class="text-sm text-red-500 text-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="grid grid-cols-6 gap-6 mb-6">

                    <div class="col-span-6">
                        <x-page-composer.label for="locale">{{ __('Locale') }}</x-page-composer.label>
                        <x-page-composer.input wire:model="locale" id="locale" />
                        @error('locale')
                            <span class="text-sm text-red-500 text-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveLanguage" type="button"
                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Save') }}
                </button>
                <button @click="showLanguageAdd = false" type="button"
                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
