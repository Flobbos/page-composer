<div class="relative mt-1" x-data="{ open: @entangle('open') }" @click.away="open = false">
    <div @click="open = ! open"
        class="relative w-full py-3 pl-5 text-left transition duration-300 border border-gray-300 shadow-sm cursor-default pr-7 bg-gray-50 focus:bg-white rounded-xl pr-15 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
        <span class="flex flex-wrap items-start gap-1">

            @if ($selected->isEmpty())
                <div class="flex items-center justify-center font-medium border border-transparent">
                    <div class="flex-initial max-w-full pb-1 font-normal leading-4 text-gray-400">
                        {{ $placeholder }}
                    </div>
                </div>
            @endif

            @foreach ($selected as $item)
                <div class="flex items-center justify-center overflow-hidden font-medium bg-white border border-gray-200 rounded bg-gray-50">
                    <div class="flex-initial max-w-full px-1 text-xs font-normal leading-4">
                        {{ $item[$labelBy] }}
                    </div>
                    <div class="flex flex-row-reverse items-center flex-auto h-full p-1 transition bg-gray-100 hover:bg-indigo-500 hover:text-white" wire:click="removeOption({{ json_encode($item) }})" wire:key="uniqid()">
                        <x-heroicon-o-x-mark-mark class="w-3 h-3 fill-current" />
                    </div>
                </div>
            @endforeach

        </span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3 ml-3 cursor-pointer" @click.stop="open = ! open">
            <x-heroicon-o-chevron-down class="w-4 h-4 transform" ::class="{ 'rotate-180': open == true }" />
        </span>
    </div>

    <ul x-show="open" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute w-full py-1 mt-1 overflow-auto text-base bg-white shadow-lg max-h-56 rounded-xl ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" tabindex="-1" role="listbox" aria-labelledby="listbox-label"
        aria-activedescendant="listbox-option-3">
        @forelse($availableOptions as $option)
            <li wire:click="selectOption({{ json_encode($option) }})" wire:key="{{ uniqid() }}" class="relative px-5 py-3 text-gray-900 transition cursor-default select-none pr-9 hover:bg-gray-50" id="listbox-option-0" role="option">
                <div class="flex items-center">
                    <span class="block font-normal truncate">
                        {{ $option[$labelBy] }}
                    </span>
                </div>
            </li>
        @empty
            <li class="relative py-2 pl-3 italic text-gray-300 cursor-default select-none pr-9">
                {{ __('No entries left') }}
            </li>
        @endforelse

    </ul>

</div>
