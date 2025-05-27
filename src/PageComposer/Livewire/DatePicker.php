<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Attributes\Computed;
use Livewire\Component;

class DatePicker extends Component
{
    public $showDatepicker = false;
    public $datepickerValue;
    public $currentMonth;
    public $today;
    public $todayFormat;
    public $currentDay;
    public $startOfWeek;
    public $tempDate;

    public function mount()
    {
        $this->today = now();
        $this->startOfWeek = now()->startOfWeek();
        $this->todayFormat = now()->format('m-d-Y');
        $this->initDates();
    }

    public function initDates(): void
    {
        $this->currentMonth = [];
        $this->tempDate = now()->createFromDate($this->today->year, $this->today->month, 1)->startOfWeek();
        do {
            for ($i = 0; $i < 7; $i++) {
                $this->currentMonth[] = [
                    'day' => $this->tempDate->day,
                    'date' => $this->tempDate->format('m-d-Y')
                ];
                $this->tempDate->addDay();
            }
        } while ($this->tempDate->month == $this->today->month);
    }

    public function addMonth()
    {
        $this->today->addMonth();
        $this->startOfWeek = $this->today->startOfWeek();
        $this->initDates();
    }

    public function subMonth()
    {
        $this->today->subMonth();
        $this->startOfWeek = $this->today->startOfWeek();
        $this->initDates();
    }

    public function selectDate(string $date)
    {
        $this->datepickerValue = $date;
        $this->dispatch('dateSelected', date: $date);
    }

    public function render()
    {
        return view('page-composer::livewire.date-picker');
    }

    #[Computed()]
    public function weekDays(): array
    {
        // Returns short names for Monday to Sunday
        return collect(range(1, 7))
            ->map(fn($d) => now()->startOfWeek()->addDays($d - 1)->format('D'))
            ->toArray();
    }
}
