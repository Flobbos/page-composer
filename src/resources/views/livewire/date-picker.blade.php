<div class="antialiased sans-serif">
    <div x-data="{
            showDatepicker: @entangle('showDatepicker'),
            today: @entangle('todayFormat'),
            isToday(date){
                return date == this.today;
            }
        }" x-cloak>
        <div class="w-full mb-5">
            <label for="datepicker" class="block mb-2 text-xs font-medium text-gray-700">Select Date</label>
            <div class="relative">
                <input type="hidden" name="date" wire:model="datepickerValue">
                <input type="text" readonly wire:model="datepickerValue" @click="showDatepicker = !showDatepicker"
                    @keydown.escape="showDatepicker = false"
                    class="block w-full h-12 px-5 mt-1 transition duration-300 border-gray-300 shadow-sm bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl"
                    placeholder="Select date">

                <div class="absolute right-0 px-3 py-2 top-1">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <div class="absolute top-0 left-0 z-10 p-4 mt-12 bg-white rounded-lg shadow" style="width: 17rem"
                    x-show.transition="showDatepicker" @click.away="showDatepicker = false">

                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="text-lg font-bold text-gray-800">{{ $today->format('F') }}</span>
                            <span class="ml-1 text-lg font-normal text-gray-600">{{ $today->format('Y') }}</span>
                        </div>
                        <div>
                            <button type="button"
                                class="inline-flex p-1 transition duration-100 ease-in-out rounded-full cursor-pointer hover:bg-gray-200"
                                wire:click="subMonth()">
                                <svg class="inline-flex w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button type="button"
                                class="inline-flex p-1 transition duration-100 ease-in-out rounded-full cursor-pointer hover:bg-gray-200"
                                wire:click="addMonth()">
                                <svg class="inline-flex w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap mb-3 -mx-1">
                        @for ($day = 0; $day < 7; $day++)
                            <div style="width: 14.26%" class="px-1">
                                <div class="text-xs font-medium text-center text-gray-800">
                                    {{ $startOfWeek->addDays($day)->format('D') }}</div>
                            </div>
                        @endfor
                    </div>

                    <div class="flex flex-wrap -mx-1">
                        @foreach ($currentMonth as $key => $value)
                            <div style="width: 14.28%" class="px-1 mb-1">
                                <div wire:click="selectDate('{{ $value['date'] }}')"
                                    class="text-sm leading-none leading-loose text-center transition duration-100 ease-in-out rounded-full cursor-pointer"
                                    :class="{'bg-blue-500 text-white': isToday('{{ $value['date'] }}') == true, 'text-gray-700 hover:bg-blue-200': isToday('{{ $value['date'] }}') == false }">
                                    {{ $value['day'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
