<div class="relative pt-10 mx-auto max-w-7xl">
    <div class="flex flex-col">
        <div class="flex">
            <div class="flex space-x-4">
                <livewire:element-component :key="uniqid()" />
                <livewire:category-component :key="uniqid()" />
                <livewire:tag-component :key="uniqid()" />
                <livewire:template-component :key="uniqid()" />
            </div>
            <div class="flex justify-end w-full pb-5 space-x-5">

                @if (session()->has('message'))
                    <div x-data="{
                        show: true,
                        init() {
                            setInterval(() => {
                                this.show = false;
                            }, 1500);
                        },
                    }" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 blur transform translate-x-10"
                        class="flex py-1 pl-4 pr-2 mr-10 text-sm text-white bg-indigo-400 rounded-full">
                        {{ session('message') }}
                        <x-heroicon-o-x-mark @click="show = false" class="w-5 h-5 ml-5 cursor-pointer" />
                    </div>
                @endif
                <a href="{{ route('page-composer::pages.create') }}" class="flex items-center justify-center px-3 py-1 text-sm text-white transition bg-indigo-600 rounded-full cursor-pointer hover:bg-indigo-400">
                    <x-heroicon-o-plus class="w-4 h-4 mr-1" />{{ __('Add Page') }}
                </a>
                @if ($trashedPages || $showTrash)
                    <button wire:click="$toggle('showTrash')" class="flex items-center justify-center px-3 py-1 ml-2 text-sm text-white transition bg-red-600 rounded-full cursor-pointer focus:outline-none hover:bg-red-400">
                        @if ($showTrash)
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> {{ __('Back') }}
                        @else
                            <x-heroicon-o-trash class="w-4 h-4 mr-1" /> {{ __('Trash') }}
                        @endif
                    </button>
                @endif
            </div>
        </div>

        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    {{ __('Title') }}
                                </th>

                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    {{ __('Published') }}
                                </th>

                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    {{ __('Active') }}
                                </th>

                                @if (config('page-composer.useCategories'))
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        {{ __('Category') }}
                                    </th>
                                @endif

                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">{{ __('Edit') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($pages as $page)
                                <tr>
                                    <td class="px-2 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $page->name }}<br />
                                                    <span class="text-xs font-normal">
                                                        @foreach ($page->translations as $trans)
                                                            {{ $trans->language->locale }}: <a class="underline hover:no-underline" href="{{ url($trans->slug) }}" target="_blank">{{ $trans->slug }}</a>
                                                            @if (!$loop->last)
                                                                /
                                                            @endif
                                                        @endforeach
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-2 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $page->published_on ? $page->published_on->format('d.m.Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-2 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div wire:click="setActive({{ $page->id }})" class="text-sm font-medium text-gray-900 cursor-pointer">
                                                    @if ($page->is_published)
                                                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                    @else
                                                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    @if (config('page-composer.useCategories'))
                                        <td class="px-2 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $page->category->name ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endif

                                    @if ($showTrash)
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <a href="#" wire:click.prevent="restorePage({{ $page->id }})" class="text-indigo-600 hover:text-indigo-800">{{ __('Restore') }}</a>
                                            <a href="#" wire:click.prevent="hardDeletePage({{ $page->id }})" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</a>
                                        </td>
                                    @else
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <a href="{{ route('page-composer::pages.edit', $page->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                            <a href="#" wire:click.prevent="deletePage({{ $page->id }})" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap" colspan="5">
                                        {{ __('No pages') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Confirm move to trash --}}
        <x-page-composer::page-composer.dialog-modal :id="uniqid()" maxWidth="2xl" wire:model="showConfirmDelete">
            <x-slot name="title">{{ __('Delete') }} {{ $currentPage->name }}?</x-slot>
            <x-slot name="content">
                {{ __('Are you sure you want to move this page to the trash?') }}
            </x-slot>
            <x-slot name="footer">
                <x-page-composer::page-composer.button wire:click="$toggle('showConfirmDelete')">{{ __('Cancel') }}</x-page-composer::page-composer.button>
                <x-page-composer::page-composer.danger-button wire:click="$toggle('confirmDelete')">OK</x-page-composer::page-composer.button>
            </x-slot>
        </x-page-composer::page-composer.dialog-modal>
        {{-- Confirm hard delete --}}
        <x-page-composer::page-composer.dialog-modal :id="uniqid()" maxWidth="2xl" wire:model="showConfirmHardDelete">
            <x-slot name="title">
                <div class="flex py-2 border-b border-gray-300">
                    <x-heroicon-o-exclamation-triangle class="mr-5 text-red-800 w-7 h-7 animate-pulse" /> {{ __('Delete') }} {{ $currentPage->name }}?
                </div>
            </x-slot>
            <x-slot name="content">
                <div class="flex flex-col pt-5">
                    {{ __('Are you sure you want to delete this page?') }}
                    <span class="py-5 italic font-bold text-red-800">{{ __('This can not be undone!') }}</span>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-page-composer::page-composer.button wire:click="$toggle('showConfirmHardDelete')">{{ __('Cancel') }}</x-page-composer::page-composer.button>
                <x-page-composer::page-composer.danger-button wire:click="$toggle('confirmHardDelete')">OK</x-page-composer::page-composer.button>
            </x-slot>
        </x-page-composer::page-composer.dialog-modal>
    </div>
</div>
