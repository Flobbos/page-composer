<div>
    @foreach ($bug->comments as $comment)
        <div class="w-full p-1 my-2">
            <div class="text-xs italic text-gray-600">{{ $comment->user->name }} {{ $comment->created_at->format('d.m.Y') }}</div>
            <div>{{ $comment->content }}</div>
        </div>
    @endforeach
    <x-page-composer::page-composer.label>{{ __('Comment') }}</x-page-composer::page-composer.label>
    <textarea class="block w-full h-24 px-5 mt-1 mb-2 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl" wire:model.defer="content"></textarea>
    <div class="flex justify-end w-full mt-2">
        <div wire:loading class="flex items-center justify-center w-6 h-6 mt-2 mr-2 text-white bg-indigo-600 rounded-full">
            <svg class="w-6 h-6 text-green-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
        <x-page-composer::page-composer.button wire:click="saveComment">{{ __('Save') }}</x-page-composer::page-composer.button>
    </div>
</div>
