<div class="mt-1 relative" x-data="{open: false}" @click.away="open = false">
    <button
        type="button"
        @click="open = !open"
        class="relative h-10 w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        aria-haspopup="listbox"
        aria-expanded="true"
        aria-labelledby="listbox-label"
    >
        <span class="flex items-center">
            <span class="block truncate"></span>
        </span>

        <span class="ml-3 absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <!-- Heroicon name: solid/selector -->
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    <ul
        x-show="open" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute mt-1 w-full bg-white shadow-lg max-h-56 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        tabindex="-1"
        role="listbox"
        aria-labelledby="listbox-label"
        aria-activedescendant="listbox-option-3"
    >
        @foreach ($options as $key => $value)
            <li
                @click=""
                class="text-gray-900 hover:bg-gray-50 cursor-default select-none relative py-2 pl-3 pr-9"
                id="listbox-option-0"
                role="option"
            >
                <div class="flex items-center">
                    <span class="font-normal block truncate">
                        {{ $value->translations }} {{ $key }}
                    </span>
                </div>
            </li>
        @endforeach
    </ul>
</div>