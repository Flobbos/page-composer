<button @isset($target) @click="settingsBox = '{{ $attributes->get('target') }}'" @endisset @if (isset($disabled) && $disabled) disabled title="{{ __('Please select language first.') }}" @endif
    class="group relative flex focus:outline-none w-full h-16 items-center justify-center transition duration-500 bg-white {{ $class ?? '' }}"
    :class="{'opacity-50 cursor-default': {{ isset($disabled) && $disabled ? 1 : 0 }}, 'text-white bg-indigo-400': settingsBox === '{{ $target ?? null }}', 'text-gray-700': settingsBox !== '{{ $target ?? null }}'}">
    {{ $slot }}

    @isset($tooltip)
    <div x-show="settingsBox !== '{{ $target ?? null }}'" class="absolute z-50 items-center justify-center p-2 text-xs font-light text-gray-700 transition duration-500 delay-200 transform scale-90 -translate-x-1 translate-y-2 bg-white opacity-0 group-hover:opacity-100 group-hover:translate-y-0 left-full font-title backdrop-filter backdrop-blur-md bg-opacity-90 rounded-r-xl">
      {{ $tooltip }}
    </div>
    @endisset
</button>

