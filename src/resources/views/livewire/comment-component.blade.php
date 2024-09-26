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
        <x-page-composer::page-composer.button wire:click="saveComment">{{ __('Save') }}</x-page-composer::page-composer.button>
    </div>
</div>
