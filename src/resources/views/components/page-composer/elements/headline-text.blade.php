@props(['content' => []])
<div>
    <h2>{{ Arr::get($content, 'headline', '') }}</h2>
    {!! Arr::get($content, 'text', '') !!}
</div>
