<?php

namespace App\Feature\Collections;

use App\Feature\Booking\Slots;
use Illuminate\Support\Collection;

class SlotCollection extends Collection
{
    public function firstAvailableDate()
    {
        return $this->first(function (Slots $date) {
            return $date->slots->count() >= 1;
        });
    }

    public function availableSlots(): Collection
    {
        return $this->filter(function (Slots $date) {
            return !$date->slots->isEmpty();
        });
    }
}
