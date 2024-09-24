@props(['content'])
<div>
    {!! Arr::get($content, 'text', '') !!}
</div>
