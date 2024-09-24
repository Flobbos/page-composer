@props(['content'])
<div>
    <div class="aspect-w-16 aspect-h-9 min-w-max">
        <iframe class="w-full h-full" src="{{ Arr::get($content, 'videoUrl') }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen></iframe>
    </div>
    {!! Arr::get($content, 'videoCaption', '') !!}
</div>
