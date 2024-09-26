<div x-data="{
    showTemplateWindow: @entangle('showTemplateWindow'),
    hideElement() {
        this.showTemplateWindow = false
    }
}" @click.outside="hideElement" @keydown.escape.window="hideElement">
    <button @click="showTemplateWindow = ! showTemplateWindow" class="flex items-center px-4 py-1 space-x-1 text-sm text-white transition bg-indigo-600 rounded-full hover:bg-indigo-400 focus:outline-none">
        <x-heroicon-o-cloud-arrow-up class="w-5 h-5" />
        <span>{{ __('Templates') }}</span>
    </button>
    <div x-cloak x-show="showTemplateWindow" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 blur transform -translate-x-10" x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 blur transform translate-x-10"
        class="absolute z-30 flex-col w-1/2 overflow-hidden transform -translate-x-1 bg-white shadow-2xl top-20 rounded-xl left-64">
        <!-- header -->
        <div class="flex items-center h-16 px-8 py-10 space-x-2 text-gray-600">

            <div class="flex items-center w-full space-x-2 text-xl font-semibold font-title">
                <x-heroicon-o-cloud-arrow-up class="w-5 h-5 text-indigo-600" />
                <span>
                    {{ __('Templates') }}
                </span>
            </div>

            <div @click="hideElement" class="transition duration-500 transform cursor-pointer hover:rotate-90 hover:text-indigo-600">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </div>

        </div>

        <!-- main content -->
        <div class="flex flex-col w-full px-8 pb-10 space-y-4 divide-y divide-gray-100">
            @forelse($templates as $template)
                <div class="flex w-full pt-2 text-md">
                    <div class="w-full p-1">
                        {{ $template->name }} <span class="text-xs italic">by {{ $template->user->name }}</span>
                    </div>
                    <div class="flex space-x-1">
                        <x-page-composer.button-link href="{{ route('pages.create', ['template' => $template->id]) }}" class="lg:text-xs" primary>
                            {{ __('Use') }}
                        </x-page-composer.button-link>
                        <x-page-composer.button wire:click="deleteTemplate({{ $template->id }})" class="text-white bg-red-500 lg:text-xs hover:bg-red-600">
                            {{ __('Delete') }}
                        </x-page-composer.button>
                    </div>
                </div>
            @empty
                No templates saved
            @endforelse
        </div>
    </div>
</div>
