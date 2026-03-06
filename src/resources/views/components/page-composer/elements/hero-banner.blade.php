@props(['content' => []])
@php
    $overlay = (int) Arr::get($content, 'overlayOpacity', 50);
    $overlay = max(0, min(90, $overlay));
@endphp

<section class="relative overflow-hidden rounded-xl {{ Arr::get($content, 'minHeight', 'h-96') }}">
    <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('{{ Arr::get($content, 'bgImageUrl', '') }}');"></div>
    <div class="absolute inset-0 bg-black" style="opacity: {{ $overlay / 100 }};"></div>

    <div class="relative z-10 flex items-center justify-center h-full px-6 py-12 text-center">
        <div class="max-w-4xl text-white">
            <h1 class="mb-3 text-3xl font-bold md:text-5xl">{{ Arr::get($content, 'headline', '') }}</h1>
            <p class="mb-6 text-lg md:text-xl">{{ Arr::get($content, 'subheadline', '') }}</p>
            @if (Arr::get($content, 'ctaLabel') && Arr::get($content, 'ctaUrl'))
                <a href="{{ Arr::get($content, 'ctaUrl') }}" target="{{ Arr::get($content, 'ctaTarget', '_self') }}"
                    class="inline-flex items-center px-5 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-500">
                    {{ Arr::get($content, 'ctaLabel') }}
                </a>
            @endif
        </div>
    </div>
</section>
