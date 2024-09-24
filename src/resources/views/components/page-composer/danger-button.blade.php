<button
    {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent shadow-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm hover:bg-red-700']) }}>
    {{ $slot }}
</button>
