<div x-on:show-element-selector.window="handleSelectorEvent" x-data="{
    showSelector: false,
    target: null,
    handleSelectorEvent($event) {
        this.showSelector = true
        this.target = $event.detail.id
        console.log(this.target)
    }
}" class="py-2">
    <div x-cloak x-show="showSelector" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showSelector" x-transition:enter="eease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showSelector" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @keydown.escape.window="showSelector = false" class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow rounded-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="flex flex-col p-8 text-gray-600 ">

                    <div class="flex items-center justify-between w-full pb-2 space-x-1 text-lg font-semibold font-title">
                        <h3 class="font-semibold leading-6 text-gray-900 font-title" id="modal-title">
                            {{ __('Choose Element') }}
                        </h3>

                        <button @click="showSelector = false" class="p-1 text-red-800 transition rounded-full bg-red-50 hover:bg-red-400 hover:text-red-100 focus:outline-none" title="Close window">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="flex flex-wrap items-start gap-3 mt-2">
                        @foreach ($elements as $element)
                            <div @click.prevent="$dispatch('elementAdded.'+target, {element: {{ $element->id }}})" @click="showSelector = false"
                                class="flex items-center justify-center p-2 px-4 space-x-2 text-xs transition bg-gray-100 cursor-pointer rounded-xl hover:bg-indigo-600 hover:text-white">
                                <span>
                                    {!! $element->icon !!}
                                </span>
                                <span>
                                    {{ $element->name }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
