@props(['content' => []])

<section class="space-y-4">
    @if (Arr::get($content, 'headline'))
        <h2 class="text-2xl font-semibold text-gray-900">{{ Arr::get($content, 'headline') }}</h2>
    @endif

    <div class="space-y-2">
        @foreach (Arr::get($content, 'items', []) as $faqIndex => $item)
            <details class="p-4 border border-gray-200 rounded-xl bg-gray-50" @if($faqIndex === 0) open @endif>
                <summary class="font-medium text-gray-900 cursor-pointer">{{ Arr::get($item, 'question', '') }}</summary>
                <div class="pt-2 text-sm text-gray-700">{{ Arr::get($item, 'answer', '') }}</div>
            </details>
        @endforeach
    </div>
</section>
