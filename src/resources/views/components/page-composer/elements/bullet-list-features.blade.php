@props(['content' => []])

<section class="space-y-4">
    @if (Arr::get($content, 'headline'))
        <h2 class="text-2xl font-semibold text-gray-900">{{ Arr::get($content, 'headline') }}</h2>
    @endif

    <ul class="space-y-3">
        @foreach (Arr::get($content, 'features', []) as $feature)
            <li class="flex items-start p-4 space-x-3 border border-gray-200 rounded-xl bg-gray-50">
                <span class="pt-1 text-indigo-600">{{ Arr::get($feature, 'icon', '•') }}</span>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ Arr::get($feature, 'title', '') }}</h3>
                    <p class="text-sm text-gray-700">{{ Arr::get($feature, 'description', '') }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</section>
