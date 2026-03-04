@props(['content' => []])
@php
    $columns = (int) Arr::get($content, 'columns', 3);
    $columns = max(1, min(4, $columns));
    $gridClass = [
        1 => 'md:grid-cols-1',
        2 => 'md:grid-cols-2',
        3 => 'md:grid-cols-3',
        4 => 'md:grid-cols-4',
    ][$columns];
@endphp

<section class="space-y-6">
    @if (Arr::get($content, 'headline'))
        <h2 class="text-2xl font-semibold text-gray-900">{{ Arr::get($content, 'headline') }}</h2>
    @endif

    <div class="grid grid-cols-1 gap-4 {{ $gridClass }}">
        @foreach (Arr::get($content, 'cards', []) as $card)
            <article class="p-5 border border-gray-200 rounded-xl bg-gray-50">
                @if (Arr::get($card, 'imageUrl'))
                    <img src="{{ Arr::get($card, 'imageUrl') }}" alt="{{ Arr::get($card, 'title', '') }}" class="object-cover w-full h-40 mb-4 rounded-lg" />
                @endif
                @if (Arr::get($card, 'icon'))
                    <div class="mb-2 text-xl text-indigo-600">{{ Arr::get($card, 'icon') }}</div>
                @endif
                <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ Arr::get($card, 'title', '') }}</h3>
                <p class="mb-3 text-sm text-gray-700">{{ Arr::get($card, 'description', '') }}</p>
                @if (Arr::get($card, 'linkUrl'))
                    <a href="{{ Arr::get($card, 'linkUrl') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        {{ Arr::get($card, 'linkLabel', 'Read more') }}
                    </a>
                @endif
            </article>
        @endforeach
    </div>
</section>
