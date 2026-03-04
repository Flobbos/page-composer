@props(['content' => []])

<section class="px-6 py-12 text-center border border-gray-200 rounded-xl bg-gray-50">
    <div class="max-w-2xl mx-auto">
        <h2 class="mb-3 text-2xl font-semibold text-gray-900">{{ Arr::get($content, 'headline', '') }}</h2>
        <p class="mb-6 text-gray-700">{{ Arr::get($content, 'subheadline', '') }}</p>

        @if (Arr::get($content, 'buttonLabel') && Arr::get($content, 'buttonUrl'))
            <a href="{{ Arr::get($content, 'buttonUrl') }}" target="{{ Arr::get($content, 'buttonTarget', '_self') }}"
                class="inline-flex items-center px-5 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-500">
                {{ Arr::get($content, 'buttonLabel') }}
            </a>
        @endif
    </div>
</section>
