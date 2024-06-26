<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;

class AppointmentShowController extends Controller
{
    public function __invoke(Appointment $appointment)
    {
        return inertia()->render('Confirmation', [
            'appointment' => AppointmentResource::make($appointment)
        ]);
    }
}
