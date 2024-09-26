<div x-data="{
    show: false,
    currentFaq: false,
    switchFaq: function(faq) {
        if (this.currentFaq == faq) {
            this.currentFaq = false;
        } else {
            this.currentFaq = faq;
        }
    }
}">
    <button @click="show = ! show" class="relative z-10 flex items-center justify-center w-8 h-8 transition bg-white rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white">
        <x-heroicon-o-question-mark-circle class="w-5 h-5" />
    </button>

    <div x-on:close.stop="show = false" x-on:keydown.escape.window="show = false" x-show="show" id="helpModal" class="fixed inset-0 z-50 px-4 py-6 overflow-y-auto jetstream-modal sm:px-0" style="display: none;">
        <div x-show="show" class="fixed inset-0 transition-all transform" x-on:click="show = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div x-show="show" class="relative mb-6 overflow-hidden transition-all transform bg-white rounded-lg shadow-xl sm:w-full sm:max-w-4xl sm:mx-auto" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div class="px-6 py-4">
                <div class="text-lg">
                    {{ __('Frequently Asked Questions') }}
                </div>

                <div class="relative w-full mt-4 space-y-2">
                    <x-page-composer::page-composer.faq-item faqId="1">
                        <x-slot name="question">{{ __('Required input') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('The minimum required input to save a new page is a name, photo, a category.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="2">
                        <x-slot name="question">{{ __('What is the meta section?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('The meta section holds the main title of your page as well as meta and keyword information. Only the title is mandatory.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="3">
                        <x-slot name="question">{{ __('What is a row?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('Content is ordered primarily in rows. Rows can be divided into columns in full width, 50/50 or in increments of 25% width.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="4">
                        <x-slot name="question">{{ __('What is a column?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('Columns hold your content elements. You can have a virtually infinite number of content items per column. ') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="5">
                        <x-slot name="question">{{ __('What is an element?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('Elements define your content. Standard elements are Text, Headline+Text and Photo. These elements can be expanded to whatever you need to display your page\'s content, like video elements for example.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="6">
                        <x-slot name="question">{{ __('Why do I need to add a language?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('The Page Composer is by default multi lingual. In order to add a content row, you first need to select a language for that content.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="7">
                        <x-slot name="question">{{ __('Why can\'t I copy content after that language was added?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('This is to prevent existing content to be overwritten. Once you have added an extra language, the system assumes it has its own content. If you want both languages to be identical, finish your main language first, then copy.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="8">
                        <x-slot name="question">{{ __('What are the row settings?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('The row settings can have additional classes to be added to that particular row in the attributes section. Alignment will align the row either center, left or right. With active you can switch the content on or off. Expanded will expand the row to full width.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="9">
                        <x-slot name="question">{{ __('What are the column settings?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('The columns settings has attributes for extra classes that should be added to that particular column. Active switches the column on or off.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="10">
                        <x-slot name="question">{{ __('What is the content mini map?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('Since sorting rows across the entire screen is cumbersome when there is a lot of content, the mini map allows you to sort rows using drag&drop with symbolised rows in a mini map.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                    <x-page-composer::page-composer.faq-item faqId="13">
                        <x-slot name="question">{{ __('Why is the preview so ugly?') }}</x-slot>
                        <x-slot name="answer">
                            {{ __('The preview shows all your content in one big overview. This is just a rough sketch of what your page might look like in the front-end and should only give you a rough idea of how it is structured.') }}
                        </x-slot>
                    </x-page-composer::page-composer.faq-item>
                </div>
            </div>
            <div class="px-6 py-4 text-right bg-gray-100">
                <x-page-composer::page-composer.button @click="show = false">{{ __('Close') }}</x-page-composer::page-composer.button>
            </div>
        </div>
    </div>
</div>
