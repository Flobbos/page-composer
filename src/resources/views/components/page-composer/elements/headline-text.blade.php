@props(['content' => []])
<div>
    <h2 class="mb-5 text-xl">{{ Arr::get($content, 'headline', '') }}</h2>
    <div class="prose">{!! Arr::get($content, 'text', '') !!}</div>
</div>
