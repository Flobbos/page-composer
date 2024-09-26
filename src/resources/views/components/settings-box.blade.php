<div x-cloak x-show="settingsBox === '{{ $attributes->get('target') }}'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 blur transform -translate-x-10"
    x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 blur transform translate-x-10" class="container absolute left-0 z-50 flex-col w-full top-2 backface-hidden" @keydown.escape.window="settingsBox = false">

    <div class="h-full bg-white shadow-2xl rounded-xl">

        <div class="flex items-center h-16 px-8 py-10 space-x-2 text-gray-600">

            @if (isset($icon))
                <div class="text-indigo-600">
                    {{ $icon ?? '' }}
                </div>
            @endif

            <div class="flex items-center w-full space-x-1 text-xl font-semibold font-title">
                {{ $title ?? '' }}
            </div>

            <button @click="settingsBox = false" class="p-1 text-red-800 transition rounded-full bg-red-50 hover:bg-red-400 hover:text-red-100 focus:outline-none" title="Close window">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>

        </div>

        <div class="px-8 pt-2 pb-8">
            {{ $content ?? '' }}
        </div>

    </div>

</div>
