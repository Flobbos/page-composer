<div class="py-10 mx-auto max-w-7xl">
    <div class="w-full pl-3 font-semibold text-left">
        {{ __('Welcome to the Page Composer issue tracker and wish list') }}
    </div>
    <div class="flex flex-col">
        <div class="flex justify-end w-full pb-5">
            <div wire:loading class="flex items-center justify-center w-6 h-6 mr-2 text-white bg-indigo-600 rounded-full">
                <svg class="w-6 h-6 text-green-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 blur transform translate-x-10"
                    class="flex py-1 pl-4 pr-2 mr-10 text-sm text-white bg-indigo-400 rounded-full">
                    {{ session('message') }}
                    <x-heroicon-o-x-mark @click="show = false" class="w-5 h-5 ml-5 cursor-pointer" />
                </div>
            @endif
            @if ($bugId)
                <button type="button" wire:click="closeBug()" class="flex items-center justify-center px-3 py-1 text-sm text-white transition bg-indigo-600 rounded-full cursor-pointer hover:bg-indigo-400">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" />{{ __('Back') }}
                </button>
            @else
                <button type="button" wire:click="$toggle('showForm')" class="flex items-center justify-center px-3 py-1 text-sm text-white transition bg-indigo-600 rounded-full cursor-pointer hover:bg-indigo-400">
                    <x-heroicon-o-plus class="w-4 h-4 mr-1" />{{ __('Add Bug') }}
                </button>
            @endif
            <button wire:click="$toggle('showTrash')" class="flex items-center justify-center px-3 py-1 ml-2 text-sm text-white transition bg-red-600 rounded-full cursor-pointer focus:outline-none hover:bg-red-400">
                @if ($showTrash)
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> {{ __('Back') }}
                @else
                    <x-heroicon-o-trash class="w-4 h-4 mr-1" /> {{ __('Trash') }}
                @endif
            </button>
        </div>

        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                    @if (!$showForm && !$bugId)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        {{ __('Title') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        {{ __('User') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        {{ __('Type') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        {{ __('Viewed') }}
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">{{ __('Edit') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($bugs as $bug)
                                    <tr>
                                        <td class="px-2 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $bug->title }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $bug->user->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if (!$bug->type)
                                                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600" />
                                                        @else
                                                            <x-heroicon-o-light-bulb class="w-6 h-6 text-green-600" />
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if ($bug->resolved)
                                                            <x-heroicon-o-check-circle wire:click="resolve({{ $bug->id }})" class="w-6 h-6 text-green-600 cursor-pointer" />
                                                        @else
                                                            <x-heroicon-o-wrench wire:click="resolve({{ $bug->id }})" class="w-6 h-6 text-red-600 cursor-pointer" />
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if ($bug->viewed)
                                                            <x-heroicon-o-eye class="w-6 h-6 text-indigo-600" />
                                                        @else
                                                            <x-heroicon-o-eye-slash class="w-6 h-6 text-indigo-600" />
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @if ($showTrash)
                                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                                <a href="#" wire:click.prevent="restoreBug({{ $bug->id }})" class="text-indigo-600 hover:text-indigo-800">{{ __('Restore') }}</a>
                                            </td>
                                        @else
                                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                                <a href="#" wire:click.prevent='showBug({{ $bug->id }})' class="text-indigo-600 hover:text-indigo-900">{{ __('Show') }}</a>
                                                <a href="#" wire:click.prevent="deleteBug({{ $bug->id }})" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</a>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap" colspan="6">
                                            {{ __('No issues') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                    @if ($showForm)
                        <div class="w-full p-5 bg-white">
                            <h4 class="mb-5">{{ __('Create new entry') }}</h4>
                            <div class="flex py-2 space-x-4">
                                <div class="w-full">
                                    <x-page-composer::page-composer.label>{{ __('Title') }}</x-page-composer::page-composer.label>
                                    <x-page-composer::page-composer.input wire:model.defer="title" />
                                </div>
                                <div>
                                    <x-page-composer::page-composer.label>{{ __('Type') }}</x-page-composer::page-composer.label>
                                    <select class="block w-32 h-12 pl-5 pr-10 mt-1 mb-2 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl"
                                        wire:model.defer="type">
                                        <option value="0" selected>{{ __('Bug') }}</option>
                                        <option value="1">{{ __('Wish') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="py-2">
                                <x-page-composer::page-composer.label>{{ __('Description') }}</x-page-composer::page-composer.label>
                                <textarea class="block w-full h-48 px-5 mt-1 mb-2 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" wire:model.defer="description"></textarea>
                                <div class="py-2">
                                    @if ($photo)
                                        <x-page-composer::page-composer.label>{{ __('Photo Preview:') }}</x-page-composer::page-composer.label>
                                        <img class="max-w-md my-2 rounded-lg" src="{{ $photo->temporaryUrl() }}">
                                    @endif
                                    <div class="flex flex-col">
                                        <x-page-composer::page-composer.label>{{ __('Screenshot') }}</x-page-composer::page-composer.label>
                                        <input type="file" wire:model="photo">
                                        @error('photo')
                                            <span class="text-xs italic text-red-600">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <x-page-composer::page-composer.danger-button wire:click="hideForm">{{ __('Cancel') }}</x-page-composer::page-composer.danger-button>
                                <x-page-composer::page-composer.button wire:click="saveBug">{{ __('Save') }}</x-page-composer::page-composer.button>
                            </div>
                        </div>
                    @endif

                    @if ($bugId)
                        <div class="p-5 bg-white">
                            <x-page-composer::page-composer.label>{{ $currentBug->user->name }} {{ $currentBug->created_at->format('d.m.Y') }}:</x-page-composer::page-composer.label>
                            <div class="p-2 border border-gray-200 rounded-lg">
                                <h3 class="py-1 border-b border-gray-200">{{ $currentBug->title }}</h3>
                                <p class="py-4 text-gray-800">{{ $currentBug->description }}</p>
                                @if ($currentBug->photo)
                                    <a href="{{ asset('storage/photos/' . $currentBug->photo) }}">
                                        <img class="max-w-md my-2 rounded-lg" src="{{ asset('storage/photos/' . $currentBug->photo) }}" />
                                    </a>
                                @endif
                            </div>
                            <livewire:comment-component :bug="$currentBug" />
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
