@props(['content'])
<div>
    <div class="@if(Arr::has($content, 'aspectRatio'))@foreach(Arr::get($content, 'aspectRatio') as $aspectRatio){{$aspectRatio}} @endforeach @endif">
        <img class="@if(Arr::has($content, 'objectFit'))@foreach(Arr::get($content, 'objectFit') as $objectFit){{ $objectFit }} @endforeach @endif @if(Arr::has($content, 'objectPosition'))@foreach(Arr::get($content, 'objectPosition') as $objectPosition){{ $objectPosition }} @endforeach @endif"
             src="{{ asset('storage/photos/' . Arr::get($content, 'photo', '')) }}" alt="{{ Arr::get($content, 'alt_tag', '') }}" />
    </div>
    <p>{{ Arr::get($content, 'caption', '') }}</p>
</div>
