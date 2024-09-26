<div x-data="{ showConfirm: false }" class="relative flex flex-col items-start justify-center px-3 pt-5 pb-3 bg-white border border-transparent rounded-lg hover:border-gray-100 group/column" wire:key="column-{{ uniqid() }}">
    <!-- remove column button -->
    <button @click="showConfirm = !showConfirm" class="invisible group-hover/column:visible absolute -top-2.5 -right-2 p-1 bg-red-50 text-red-800 hover:bg-red-400 hover:text-red-100 rounded-full focus:outline-none transition"
        title="Remove column">
        <x-heroicon-o-x-mark-mark class="w-4 h-4" />
    </button>
    <div class="absolute z-10 w-32 p-2 text-xs bg-white rounded-lg shadow-md -right-2 top-4" x-show="showConfirm" @click.away="showConfirm = false">
        {{ __('Delete this column?') }}
        <div class="flex justify-between pt-2">
            <button class="px-2 text-white bg-red-600 rounded hover:bg-red-700" type="button" wire:click="$emitUp('deleteColumn', '{{ $columnKey }}')">{{ __('Yes') }}</button>
            <button class="px-2 text-white bg-green-600 rounded hover:bg-green-700" type="button" @click="showConfirm = false">{{ __('No') }}</button>
        </div>
    </div>
    <!-- column settings button -->
    <button wire:click.prevent="$toggle('showColumnSettings')"
        class="invisible group-hover/column:visible absolute -top-2.5 right-5 p-1 bg-gray-200 text-gray-800 hover:bg-gray-400 hover:text-gray-100 rounded-full focus:outline-none transition" title="Column settings">
        <x-heroicon-o-cog class="w-4 h-4" />
    </button>
    <!-- column settings box -->
    @if ($showColumnSettings)
        <div class="absolute z-50 flex flex-col @if ($column['column_size'] <= 6) w-full @else w-1/2 @endif p-5 text-xs bg-white border border-gray-200 rounded-lg shadow-lg top-4 right-7
            space-y-2">
            <h3 class="mb-2 font-semibold text-gray-700 font-title">{{ __('Row Settings') }}</h3>
            <div class="flex flex-col w-full">
                <div class="space-y-2">
                    <label class="text-gray-700">{{ __('Attributes') }}</label>
                    <input type="text" class="w-full mb-4 text-sm text-gray-900 border-gray-200 rounded-lg focus:outline-none" wire:model="column.attributes" />
                </div>
                <div class="mt-4">
                    <label class="inline-flex items-center">
                        <input wire:model="column.active" type="checkbox" class="w-5 h-5 text-red-600 rounded form-checkbox" checked><span class="ml-2 text-gray-700">{{ __('Active') }}</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-5">
                    <button class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-400 focus:outline-none" wire:click.prevent="$toggle('showColumnSettings')">{{ __('Cancel') }}</button>
                    <button class="px-2 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-400 focus:outline-none" wire:click.prevent="saveColumnSettings">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    @endif
    @if (Arr::get($column, 'column_size') < 12)
        <span wire:sortable.handle class="invisible group-hover/column:visible absolute -top-2.5 -left-2 p-1 bg-indigo-50 text-indigo-800 hover:bg-indigo-400 hover:text-indigo-100 rounded-full focus:outline-none transition"
            title="Move column">
            <x-heroicon-o-hand-raised class="w-4 h-4" />
        </span>
    @endif
    <div class="flex flex-col w-full">
        @foreach ($this->sortedElements as $key => $item)
            <div>
                @livewire('elements.' . $item['component'], ['data' => $item, 'itemKey' => $key, 'sorting' => $this->getElementPositionArray($key), 'previewMode' => $previewMode], key(uniqid()))
            </div>
        @endforeach
    </div>
    <div class="flex justify-center w-full">
        <livewire:element-list :key="uniqid()" column_key="$columnKey" :visible="empty($this->sortedElements)" />
    </div>
</div>
