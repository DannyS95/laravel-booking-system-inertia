<?php

namespace App\Feature\Booking;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Feature\Collections\SlotCollection;

class SlotRangeGenerator
{
    public function __construct(protected Carbon $startsAt, protected Carbon $endsAt)
    {
        //
    }

    public function generate(int $interval): SlotCollection
    {
        $collection = new SlotCollection();

        $days = CarbonPeriod::create($this->startsAt, '1 day', $this->endsAt);

        foreach ($days as $day) {
            $date = new Slots($day);

            $times = CarbonPeriod::create($day->startOfDay(), sprintf('%d minutes', $interval), $day->copy()->endOfDay());

            foreach ($times as $time) {
                $date->addSlot(new Slot($time));
            }

            $collection->push($date);
        }

        return $collection;
    }
}
