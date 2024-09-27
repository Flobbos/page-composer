@props(['faqId'])
<div>
    <div class="flex justify-between px-2 py-1 italic border border-gray-200 cursor-pointer" @click="switchFaq({{ $faqId }})">
        <span :class="{ 'underline': currentFaq == {{ $faqId }} }">{{ $question }}</span>
        <x-heroicon-o-plus class="w-5 h-5 mt-1 transition ease-in-out transform cursor-pointer" ::class="{ 'rotate-45 text-indigo-500': currentFaq == {{ $faqId }} }" />
    </div>
    <div class="p-5 transform border-b border-l border-r border-gray-200" x-show="currentFaq === {{ $faqId }}" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0 -translate-y-1/3"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-1/3 opacity-0">
        {{ $answer }}
    </div>
</div>
