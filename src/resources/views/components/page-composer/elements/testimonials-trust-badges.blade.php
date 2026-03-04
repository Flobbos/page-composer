@props(['content' => []])

<section class="space-y-6">
    @if (Arr::get($content, 'headline'))
        <h2 class="text-2xl font-semibold text-gray-900">{{ Arr::get($content, 'headline') }}</h2>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach (Arr::get($content, 'testimonials', []) as $testimonial)
            <blockquote class="p-5 border border-gray-200 rounded-xl bg-gray-50">
                <p class="mb-3 text-sm text-gray-800">“{{ Arr::get($testimonial, 'quote', '') }}”</p>
                <footer class="text-sm text-gray-600">
                    <span class="font-semibold">{{ Arr::get($testimonial, 'name', '') }}</span>
                    @if (Arr::get($testimonial, 'role'))
                        <span> — {{ Arr::get($testimonial, 'role') }}</span>
                    @endif
                </footer>
            </blockquote>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach (Arr::get($content, 'badges', []) as $badge)
            <div class="p-4 text-center border border-gray-200 rounded-xl bg-gray-50">
                <div class="text-xl font-bold text-indigo-600">{{ Arr::get($badge, 'value', '') }}</div>
                <div class="text-xs text-gray-700">{{ Arr::get($badge, 'label', '') }}</div>
            </div>
        @endforeach
    </div>
</section>
