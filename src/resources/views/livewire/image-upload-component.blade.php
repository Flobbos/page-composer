<div class="{{ $class }}">
    <label class="block text-sm font-medium text-gray-700">
        {{ $title }}
    </label>

    <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
        <div class="space-y-1 text-center">
            @if ($image)
                <div class="relative rounded-lg">
                    <x-heroicon-o-x-circle
                        class="absolute w-6 h-6 text-red-600 bg-white rounded-full shadow-md cursor-pointer -right-2 -top-2 hover:text-red-900"
                        wire:click="deleteImage"
                    />

                    <x-heroicon-o-check-circle
                        class="absolute w-6 h-6 text-green-600 bg-white rounded-full shadow-md cursor-pointer right-4 -top-2 hover:text-green-900"
                        wire:click="saveImage"
                    />

                    <img class="w-32 h-32 mx-auto rounded-lg" src="{{ $image->temporaryUrl() }}">
                </div>
            @elseif ($imageInput)
                <div class="relative rounded-lg">
                    <x-heroicon-o-x-circle
                        class="absolute w-6 h-6 text-red-600 bg-white rounded-full shadow-md cursor-pointer -right-2 -top-2 hover:text-red-900"
                        wire:click="deleteExistingImage"
                    />

                    <img class="w-32 h-32 mx-auto rounded-lg" src="{{ asset('storage/' . $imageInput) }}">
                </div>
            @else
                <x-heroicon-o-photo class="w-12 h-12 mx-auto text-gray-400" />

                <div class="flex text-sm text-gray-600">
                    <label
                        class="relative font-medium text-indigo-600 bg-white rounded-md cursor-pointer hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                        for="file-upload-{{ $elementId }}"
                    >
                        <span>Upload a file</span>

                        <input
                            class="sr-only"
                            id="file-upload-{{ $elementId }}"
                            name="image"
                            type="file"
                            wire:model="image"
                        />
                    </label>

                    <p class="pl-1">or drag and drop</p>
                </div>

                <p class="text-xs text-gray-500">
                    PNG, JPG, GIF up to 1MB
                </p>
            @endif

            @error('image')
                <span class="text-xs text-red-600">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
