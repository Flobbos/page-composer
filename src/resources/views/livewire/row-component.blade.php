<div class="w-full p-4 transition border border-transparent @if ($row['active']) hover:border-gray-100 @else border-red-500 @endif group hover:shadow-lg rounded-xl">
    <div x-data="{ showAddColumn: false, showRowSettings: false, showConfirm: false }" class="flex text-sm">
        <div class="relative flex items-center justify-between w-full">
            <!-- add column popup -->
            <div class="absolute flex items-center @if (Arr::get($row, 'columns')) invisible @endif space-x-2 group-hover:visible -top-7 -right-6">
                <span class="px-1 font-semibold text-gray-500 bg-green-200 rounded group-hover:shadow-md font-title">
                    {{ __('Row') }} {{ $row['sorting'] + 1 }}
                </span>
                <button class="p-1 text-gray-800 transition bg-gray-200 rounded-full group-hover:shadow-md hover:bg-gray-400 hover:text-gray-100 focus:outline-none" @click="showRowSettings = ! showRowSettings" title="Row settings">
                    <x-heroicon-o-cog class="w-4 h-4" />
                </button>
                <button class="p-1 text-green-400 transition bg-green-200 rounded-full group-hover:shadow-md hover:bg-green-400 hover:text-green-100 focus:outline-none" @click="showAddColumn = ! showAddColumn"
                    x-show="{{ $row['available_space'] }} > 0" title="{{ __('Add columns') }}">
                    <x-heroicon-o-plus class="w-4 h-4 stroke-current" />
                </button>

                <button class="p-1 text-red-800 transition bg-red-200 rounded-full group-hover:shadow-md hover:bg-red-400 hover:text-red-100 focus:outline-none" @click="showConfirm = ! showConfirm" title="Remove row">
                    <x-heroicon-o-trash class="w-4 h-4 stroke-current" />
                </button>
                <div class="absolute z-10 p-2 text-xs bg-white rounded-lg shadow-md w-28 -left-2 top-8" x-show="showConfirm" @click.away="showConfirm = false">
                    {{ __('Delete this row?') }}
                    <div class="flex justify-between pt-2">
                        <button class="px-2 text-white bg-red-600 rounded hover:bg-red-700" type="button" wire:click="$dispatch('deleteRow', {rowKey: {{ $rowKey }}})">{{ __('Yes') }}</button>
                        <button class="px-2 text-white bg-green-600 rounded hover:bg-green-700" type="button" @click="showConfirm = false">{{ __('No') }}</button>
                    </div>
                </div>

            </div>

            <div @click.away="showAddColumn = ! showAddColumn" class="absolute top-0 z-50 flex flex-col w-64 p-5 text-xs text-pink-700 bg-white border border-gray-200 rounded-lg shadow-lg right-5" x-show="showAddColumn"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <h3 class="mb-2 font-semibold text-gray-700 font-title">{{ __('Add column') }}</h3>

                <!-- full size -->
                @if ($row['available_space'] == 12)
                    <div wire:click="addColumn(12)" @click="showAddColumn = false" class="mb-2 cursor-pointer hover:text-pink-500">
                        <div class="w-full h-6 px-2 py-1 bg-pink-200 rounded">{{ __('Full') }}</div>
                    </div>
                @endif
                @if ($row['available_space'] >= 6)
                    <div wire:click="addColumn(6)" @click="showAddColumn = false" class="flex mb-2 space-x-1 cursor-pointer hover:text-pink-500">
                        <div class="w-1/2 h-6 px-2 py-1 bg-pink-200 rounded">{{ __('Half') }}</div>
                        <div class="w-1/2 h-6 px-2 py-1 bg-pink-200 rounded"></div>
                    </div>
                @endif
                @if ($row['available_space'] >= 3)
                    <div wire:click="addColumn(3)" @click="showAddColumn = false" class="flex space-x-1 cursor-pointer hover:text-pink-500">
                        <div class="w-1/4 h-6 px-2 py-1 bg-pink-200 rounded">1/4</div>
                        <div class="w-1/4 h-6 px-2 py-1 bg-pink-200 rounded"></div>
                        <div class="w-1/4 h-6 px-2 py-1 bg-pink-200 rounded"></div>
                        <div class="w-1/4 h-6 px-2 py-1 bg-pink-200 rounded"></div>
                    </div>
                @endif
            </div>
            <div @click.away="showRowSettings = false" class="absolute top-0 z-50 flex flex-col w-1/2 p-5 text-xs text-pink-700 bg-white border border-gray-200 rounded-lg shadow-lg"
                :class="{ 'right-12': {{ $row['available_space'] }} > 0, 'right-6': {{ $row['available_space'] }} == 0 }" x-show="showRowSettings" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <h3 class="mb-2 font-semibold text-gray-700 font-title">{{ __('Row Settings') }}</h3>
                <div class="flex flex-col w-full">
                    <!-- attributes -->
                    <label class="my-2 text-gray-700">{{ __('Attributes') }}</label>
                    <input type="text" class="w-full mb-4 text-sm text-gray-900 border-gray-200 rounded-lg focus:outline-none" wire:model="row.attributes" />
                    <!-- alignment -->
                    <label class="my-2 text-gray-700">{{ __('Alignment') }}</label>
                    <div class="relative inline-flex mb-4">
                        <select wire:model="row.alignment" class="w-full text-xs text-gray-600 bg-white border border-gray-300 rounded-lg appearance-none hover:border-gray-400 focus:outline-none">
                            <option>{{ __('Choose alignment') }}</option>
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div class="flex px-2 space-x-5">
                        <label class="inline-flex items-center mt-3">
                            <input wire:model="row.active" type="checkbox" class="w-5 h-5 text-red-600 rounded form-checkbox" checked><span class="ml-2 text-gray-700">{{ __('Active') }}</span>
                        </label>
                        <label class="inline-flex items-center mt-3">
                            <input wire:model="row.expanded" type="checkbox" class="w-5 h-5 text-indigo-600 rounded form-checkbox" checked><span class="ml-2 text-gray-700">{{ __('Expanded') }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-5">
                        <button class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-400 focus:outline-none" @click.prevent="showRowSettings = false">{{ __('Cancel') }}</button>
                        <button class="px-2 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-400 focus:outline-none" wire:click.prevent="saveRowSettings">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div wire:sortable="updateColumnOrder" wire:sortable.options="{ animation: 100 }" class="flex space-x-4 justify-left transition pt-0 @if (count($row['columns']) > 0) pt-4 @endif">
        @foreach ($this->sortedColumns as $columnKey => $column)
            <div wire:sortable.item="{{ $columnKey }}" class="{{ $this->columnWidth($column['column_size']) }}">
                <livewire:column-component :column="$column" :key="uniqid()" :columnKey="$columnKey" :previewMode="$previewMode" target="{{ $source }}" />
            </div>
        @endforeach
    </div>
</div>
