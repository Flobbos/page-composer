@pushOnce('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        .ql-container.ql-snow {
            border-radius: 0px 0px 10px 10px;
        }

        .ql-toolbar.ql-snow {
            border-radius: 10px 10px 0px 0px;
        }
    </style>
@endpushOnce

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        function quillEditor(data) {
            return {
                instance: null,
                init() {
                    this.$nextTick(() => {
                        this.instance = new Quill(this.$refs.editor, {
                            theme: 'snow'
                        });
                        this.instance.on('text-change', () => {
                            this.$refs.input.dispatchEvent(new CustomEvent('input', {
                                detail: this.instance.root.innerHTML
                            }));
                        });
                    });
                },
            }
        }
    </script>
@endpushOnce
<div class="relative min-h-screen" x-data="{
    settingsBox: @entangle('settingsBox'),
    addLang: false,
    currentLanguage: @entangle('currentLanguage')
}">
    <div class="flex w-1/4">
        {{-- SETTINGS --}}
        <div class="fixed z-20 w-20 h-full mr-2 shadow-xl bg-gray-50">
            <div class="divide-y divide-gray-200 divide-solid">
                <div class="w-full">
                    <x-page-composer::settings-button target="mainSettings" class="hover:bg-pink-200" tooltip="{{ __('Details') }}">
                        <x-heroicon-o-adjustments-horizontal class="w-5 h-5" />
                    </x-page-composer::settings-button>
                </div>

                <div class="w-full">
                    <x-page-composer::settings-button target="mediaSettings" class="hover:bg-purple-200" tooltip="{{ __('Media') }}">
                        <x-heroicon-o-photo class="w-5 h-5" />
                    </x-page-composer::settings-button>
                </div>

                @if ($currentLanguage)
                    <div class="w-full">
                        <x-page-composer::settings-button target="metaSettings" class="hover:bg-blue-200" tooltip="{{ __('Meta') }}">
                            <x-heroicon-o-document-magnifying-glass class="w-5 h-5" />
                        </x-page-composer::settings-button>
                    </div>
                @endif

                @if (count($this->sortedRows))
                    <div class="w-full">
                        <button wire:click="$toggle('showMiniMap')" class="relative flex items-center justify-center w-full h-16 transition duration-500 bg-white group focus:outline-none hover:bg-indigo-200">
                            <x-heroicon-o-map class="w-5 h-5" />
                            <div
                                class="absolute items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 group-hover:opacity-100 group-hover:translate-y-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl">
                                {{ __('Minimap') }}
                            </div>
                        </button>
                    </div>
                @endif

                {{-- TEMPLATES --}}
                <div class="w-full">
                    @if (count($this->sortedRows))
                        <div x-data="{ showOptions: false }" @click.outside="showOptions = false" @click.prevent="showOptions = true" tooltip="{{ __('Save Template') }}"
                            class="relative flex items-center justify-center w-full h-16 transition duration-500 cursor-pointer group focus:outline-none hover:bg-blue-200" :class="{ 'bg-blue-200': showOptions, 'bg-white': !showOptions }">
                            <x-heroicon-o-cloud-arrow-down class="w-5 h-5" />

                            <div class="absolute items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl"
                                :class="{ 'group-hover:opacity-100 group-hover:translate-y-0': !showOptions }">
                                {{ __('Save Template') }}
                            </div>

                            <div x-show="showOptions"
                                class="absolute items-center justify-center text-sm font-light text-gray-700 transition duration-500 delay-200 transform translate-y-0 bg-white rounded-r shadow-md opacity-100 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90">
                                <div class="flex p-1">
                                    <div>
                                        <x-page-composer::page-composer.label>Template Name</x-page-composer::page-composer.label>
                                        @error('templateName')
                                            <span class="text-xs italic text-red-400">{{ $message }}</span>
                                        @enderror
                                        @if (session('template_saved'))
                                            <span class="text-xs italic text-green-400">{{ session()->get('template_saved') }}</span>
                                        @endif
                                        <div wire:loading>
                                            <svg class="w-3 h-3 text-green-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>
                                        <input type="text" class="border border-gray-300 rounded" wire:model.defer="templateName" />
                                    </div>
                                    <div class="flex flex-col ml-5">
                                        <button type="button" class="p-2 text-xs rounded hover:bg-green-200" wire:click="saveTemplate">
                                            {{ __('Save') }}
                                        </button>
                                        <button type="button" class="p-2 text-xs rounded hover:bg-red-200" @click.stop="showOptions = false">
                                            {{ __('Close') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div wire:ignore x-data="{
                            showOptions: false,
                        }" @click.outside="showOptions = false" @click.prevent="showOptions = true" tooltip="{{ __('Load Template') }}"
                            class="relative flex items-center justify-center w-full h-16 transition duration-500 cursor-pointer group focus:outline-none hover:bg-blue-200" :class="{ 'bg-blue-200': showOptions, 'bg-white': !showOptions }">
                            <x-heroicon-o-cloud-arrow-up class="w-5 h-5" />
                            <div class="absolute items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl"
                                :class="{ 'group-hover:opacity-100 group-hover:translate-y-0': !showOptions }">
                                {{ __('Load Template') }}
                            </div>
                            <div x-show="showOptions"
                                class="absolute items-center justify-center text-sm font-light text-gray-700 transition duration-500 delay-200 transform translate-y-0 bg-white rounded-r shadow-md opacity-100 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90">
                                <div class="flex p-1">
                                    <div>
                                        <select class="mt-3 border border-gray-200 rounded" wire:model="selectedTemplate">
                                            <option>{{ __('Select template') }}</option>
                                            @foreach ($templates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex flex-col ml-5">
                                        <button type="button" class="p-2 text-xs rounded hover:bg-green-200" wire:click="selectTemplate">
                                            {{ __('Select') }}
                                        </button>
                                        <button type="button" class="p-2 text-xs rounded hover:bg-red-200" @click.stop="showOptions = false">
                                            {{ __('Close') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                {{-- SAVING --}}
                <div class="w-full">
                    @if ($pageId)
                        <div x-data="{ showOptions: false }" @click.prevent="showOptions = !showOptions" tooltip="{{ __('Save') }}"
                            class="relative flex items-center justify-center w-full h-16 transition duration-500 cursor-pointer group focus:outline-none hover:bg-green-200" :class="{ 'bg-green-200': showOptions, 'bg-white': !showOptions }">
                            <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                            <div class="absolute items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl"
                                :class="{ 'group-hover:opacity-100 group-hover:translate-y-0': !showOptions }">
                                {{ __('Update') }}
                            </div>
                            <div x-show="showOptions" @click.outside="showOptions = false"
                                class="absolute items-center justify-center text-sm font-light text-gray-700 transition duration-500 delay-200 transform translate-y-0 bg-white shadow-md opacity-100 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl">
                                <div class="flex flex-col divide-y w-36">
                                    <button type="button" class="p-2 hover:bg-green-200 rounded-tr-xl" wire:click="updateContent(false)">
                                        {{ __('Update') }}
                                    </button>
                                    <button type="button" class="p-2 hover:bg-red-200 rounded-br-xl" wire:click="updateContent(true)">
                                        {{ __('Update & Close') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div x-data="{ showOptions: false }" @click.prevent="showOptions = !showOptions" tooltip="{{ __('Save') }}"
                            class="relative flex items-center justify-center w-full h-16 transition duration-500 cursor-pointer group focus:outline-none hover:bg-green-200"
                            :class="{ 'bg-green-200': showOptions, 'bg-white': !showOptions }">
                            <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                            <div class="absolute items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl"
                                :class="{ 'group-hover:opacity-100 group-hover:translate-y-0': !showOptions }">
                                {{ __('Save') }}
                            </div>
                            <div x-show="showOptions" @click.outside="showOptions = false"
                                class="absolute items-center justify-center text-sm font-light text-gray-700 transition duration-500 delay-200 transform translate-y-0 bg-white shadow-md opacity-100 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl">
                                <div class="flex flex-col divide-y w-36">
                                    <button type="button" class="p-2 hover:bg-green-200 rounded-tr-xl" wire:click="saveContent(false)">
                                        {{ __('Save') }}
                                    </button>
                                    <button type="button" class="p-2 hover:bg-red-200 rounded-br-xl" wire:click="saveContent(true)">
                                        {{ __('Save & Close') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="w-full">
                    <a href="{{ route('page-composer::pages.index') }}" class="relative flex items-center justify-center w-full h-16 transition duration-500 bg-white group focus:outline-none hover:bg-red-200">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                        <div
                            class="absolute items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 group-hover:opacity-100 group-hover:translate-y-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl">
                            {{ __('Cancel') }}
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    {{-- Main Content --}}
    <div class="relative w-full pl-32 pr-10 mx-auto">
        <!-- messages/errors -->
        @if ($errors->all())
            <div class="relative flex items-center justify-between py-1 pl-5 pr-3 mt-5 mb-5 text-white bg-red-400 rounded-xl">
                <div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="sticky top-0 z-10 flex justify-between py-4 backdrop-filter bg-opacity-60 backdrop-blur-md">
            <div class="flex items-start w-3/4 space-x-2">
                <x-page-composer::settings.general :categories="$categories" :tags="$tags" :displayDate="$displayDate" :pageCategory="$pageCategory" :pageTags="$pageTags" />

                <x-page-composer::settings.media :page="$page" />

                <x-page-composer::settings.meta locale="{{ $currentLanguage->locale ?? '' }}" />
                <x-page-composer::page-composer.help />
                <x-page-composer::page-composer.preview-mode :previewMode="$previewMode" :display="count($this->sortedRows)" />
                <x-page-composer::page-composer.schema-mode :previewMode="$previewMode" :display="count($this->sortedRows)" />

                <div class="flex items-center px-5 space-x-1 text-2xl font-semibold font-title">
                    @if ($previewMode)
                        <span>{{ Arr::get($pageTranslations, Arr::get($currentLanguage, 'locale', 'de') . '.content.title', __('Content')) }}</span>
                    @else
                        <span>{{ __('Schema') }}</span>
                    @endif

                    @if ($currentLanguage)
                        <span class="inline-flex items-center px-1 text-xs text-white bg-gray-400 rounded">
                            {{ strtoupper($currentLanguage->locale) }}
                        </span>
                    @endif
                </div>

                {{-- ADD ROW BUTTON --}}
                <button x-cloak x-show="currentLanguage" wire:click="addRow" class="flex items-center px-2 py-1 mt-1 space-x-1 text-sm text-white transition bg-indigo-400 rounded hover:bg-indigo-600 focus:outline-none">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    <span class="flex text-xs whitespace-nowrap">Add row</span>
                </button>

                {{-- LOADING INDICATOR --}}
                <div wire:loading class="p-2 ml-2 bg-white rounded-full shadow-xl">
                    <svg class="w-4 h-4 text-red-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                {{-- SAVING FEEDBACK --}}
                <div x-data="{
                    show: false,
                    showMessage() {
                        this.show = true;
                        setInterval(() => {
                            this.show = false;
                        }, 2000);
                    },
                }" x-show="show" @saved.window="showMessage" x-cloak x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform"
                    x-transition:leave-end="opacity-0 blur transform" class="flex items-center justify-center h-6 pl-4 pr-2 mt-1 text-sm text-white transition bg-indigo-600 rounded-full shadow-xl">
                    <span class="pr-4 text-xs">{{ session('message') }}</span>
                    <x-heroicon-o-x-mark @click="show = false" class="w-4 h-4 rounded-full cursor-pointer hover:text-indigo-600 hover:bg-white" />
                </div>
            </div>
            <div>
                <livewire:language-component :key="uniqid()" />
            </div>
            {{-- LANGUAGE SELECT --}}
            <div class="relative z-20 flex space-x-3">

                @forelse($availableLanguages as $lang)
                    <button wire:click="setLanguage({{ $lang->id }})"
                        class="focus:outline-none h-8 flex items-center shadow-xl px-4 rounded-xl text-xs transition @if ($lang->id == $currentLanguage->id) bg-indigo-400 text-white
                    font-semibold @else text-gray-700 hover:bg-gray-200 hover:text-gray-600 @endif">
                        {{ strtoupper($lang->locale) }}
                    </button>
                @empty
                @endforelse
                @if (!$availableLanguages->isEmpty() && !$selectableLanguages->isEmpty())
                    <div class="relative " x-data="{ showCopy: false }" @click="showCopy = ! showCopy" @click.away="showCopy = false">
                        <div class="flex items-center justify-center w-8 h-8 text-xs text-gray-600 transition bg-white rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white">
                            <x-heroicon-o-document-duplicate class="w-4 h-4" />
                        </div>
                        <div class="absolute z-10 flex-col items-center justify-center w-auto p-4 bg-white shadow-xl top-9 right-2 rounded-xl" x-show="showCopy">
                            @foreach ($selectableLanguages as $lang)
                                <div>
                                    <button type="button" wire:click="copyContent('{{ $currentLanguage->locale }}','{{ $lang->locale }}')"
                                        class="flex items-center h-8 px-4 text-xs font-semibold text-white transition bg-indigo-400 shadow-md rounded-xl focus:outline-none hover:shadow-none hover:text-indigo-100">
                                        {{ strtoupper($currentLanguage->locale) }}
                                        <x-heroicon-o-arrow-right class="w-4 h-4 mx-2" /> {{ strtoupper($lang->locale) }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @endif
                <div @click="addLang = !addLang" class="relative z-10 flex items-center justify-center w-8 h-8 ml-2 transition bg-white rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white"
                    :class="{ 'bg-indigo-600 text-white': addLang, 'text-gray-700': !addLang }">
                    <x-heroicon-o-globe-alt class="w-4 h-4" />
                </div>

                <div x-cloak x-show="addLang" @click.away="addLang = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 blur transform -translate-y-10"
                    x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 blur transform -translate-y-10" class="absolute right-0 z-10 flex-col items-center justify-center w-auto space-y-2 top-10 rounded-xl">
                    @foreach ($selectableLanguages as $lang)
                        <div wire:click="addLanguage({{ $lang->id }})" @click="addLang = false"
                            class="relative flex items-center justify-center w-8 h-8 text-xs text-gray-600 transition bg-white rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white">
                            {{ strtoupper($lang->locale) }}
                        </div>
                    @endforeach
                    <div wire:click="$dispatch('showLanguageCreate')"
                        class="relative flex items-center justify-center w-8 h-8 text-xs text-gray-600 transition bg-white rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white">
                        <x-heroicon-o-plus class="w-4 h-4" />
                    </div>
                </div>
            </div>

        </div>
        <!-- main page composer -->
        <div class="relative flex pb-10">
            {{-- CONTENT --}}
            <div class="relative w-full bg-white shadow-lg rounded-xl">

                <!-- Main content display -->
                <div class="px-8 py-8 space-y-5">
                    @if (!$availableLanguages->isEmpty())
                        @forelse($this->sortedRows as $rowKey=>$row)
                            <div>
                                <livewire:row-component :key="uniqid()" :row="$row" :rowKey="$rowKey" :previewMode="$previewMode" />
                            </div>
                        @empty
                            <div class="mb-5 text-gray-600 duration-500 border-2 border-dashed rounded-xl">
                                <div class="flex items-center justify-center h-24 space-x-2 animate-pulse ">
                                    <x-css-align-left class="w-6 h-6 text-indigo-600" />
                                    <span class="text-sm">Please add row.</span>
                                </div>
                            </div>
                        @endforelse
                    @else
                        <div class="mb-5 text-gray-600 border-2 border-dashed rounded-xl">
                            <div class="flex items-center justify-center h-24 space-x-2 animate-pulse ">
                                <x-heroicon-o-globe-alt class="w-5 h-5 text-indigo-600" />
                                <span class="text-sm">Please select language.</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Mini Map -->
    <x-page-composer::page-composer.dialog-modal :id="uniqid()" maxWidth="xxl" wire:model="showMiniMap">
        <x-slot name="title">{{ __('Content Mini Map') }}</x-slot>
        <x-slot name="content">
            <div class="relative w-full">
                <div wire:sortable="updateRowSorting">
                    @foreach ($this->sortedRows as $key => $row)
                        <div wire:sortable.item="{{ $key }}" class="relative flex flex-col w-full py-2 pl-6 pr-2 my-4 text-xs text-indigo-900 bg-indigo-200 rounded-lg hover:shadow-md">
                            <span wire:sortable.handle class="absolute p-1 text-indigo-800 transition rounded-full -top-1.5 -left-1.5 bg-indigo-50 hover:bg-indigo-400 hover:text-indigo-100 focus:outline-none" title="Drag column">
                                <x-heroicon-o-hand-raised class="w-4 h-4" />
                            </span>
                            Row {{ $row['sorting'] }}
                            @if (count($row['columns']))
                                <div class="flex w-full mt-1 space-x-2">
                                    @foreach ($row['columns'] as $column)
                                        <div class="{{ $this->columnWidth($column['column_size']) }} p-2 bg-pink-100 rounded-lg"></div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-page-composer::page-composer.button wire:click="$toggle('showMiniMap')">OK</x-page-composer::page-composer.button>
        </x-slot>
    </x-page-composer::page-composer.dialog-modal>
    <div class="hidden"></div>
    <livewire:element-list :key="uniqid()" column_key="$columnKey" />
</div>
