<a {{ $href }}
    {{ $attributes->merge(['class' => 'inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent shadow-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 sm:ml-3 sm:w-auto sm:text-sm hover:bg-indigo-700']) }}>
    {{ $slot }}
</a>
