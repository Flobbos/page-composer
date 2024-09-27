<select {{ $attributes->class(['mt-1 mb-2 h-12 px-5 bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 duration-300 transition rounded-xl'])->merge(['name' => '']) }}>
    {{ $slot }}
</select>
