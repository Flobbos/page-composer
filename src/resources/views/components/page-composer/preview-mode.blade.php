@props(['previewMode' => false, 'display' => 0])
<div>
    <button wire:click="$toggle('previewMode')" @class([
        'relative z-10 flex items-center justify-center w-8 h-8 transition rounded-full shadow-xl cursor-pointer hover:bg-indigo-600 hover:text-white',
        'bg-white' => !$previewMode,
        'bg-indigo-600' => $previewMode,
    ])>
        <x-heroicon-o-eye @class(['w-5 h-5', 'text-white' => $previewMode]) />
    </button>
</div>
