@props(['content' => []])
<div>
    <h2 class="mb-5 text-xl">{{ Arr::get($content, 'headline', '') }}</h2>
    {!! Arr::get($content, 'text', '') !!}
</div>
