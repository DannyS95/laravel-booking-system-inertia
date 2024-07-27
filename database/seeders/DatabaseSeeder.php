<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $services = [
            [
                'title' => 'Service 1',
                'slug' => 'service-1',
                'duration' => 30,
                'price' => 1000.00,
            ],
            [
                'title' => 'Service 2',
                'slug' => 'service-2',
                'duration' => 45,
                'price' => 1500.00,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $employees = [
            [
                'name' => 'Employee 1',
                'slug' => 'employee-1',
                'profile_photo_url' => 'https://robohash.org/2.png?set=set2',
            ],
            [
                'name' => 'Employee 2',
                'slug' => 'employee-2',
                'profile_photo_url' => 'https://robohash.org/attentive',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }

        $employees = Employee::all();
        $services = Service::all();

        foreach ($employees as $employee) {
            $serviceIds = $services->random(rand(1, count($services)))->pluck('id')->toArray();
            $employee->services()->attach($serviceIds);
        }

        $employees = Employee::all();

        foreach ($employees as $employee) {
            $employee->schedules()->create([
                'starts_at' => now()->startOfYear(),
                'ends_at' => now()->endOfYear(),
                'monday_starts_at' => '09:00:00',
                'monday_ends_at' => '17:00:00',
                'tuesday_starts_at' => '09:00:00',
                'tuesday_ends_at' => '17:00:00',
                'wednesday_starts_at' => '09:00:00',
                'wednesday_ends_at' => '17:00:00',
                'thursday_starts_at' => '09:00:00',
                'thursday_ends_at' => '17:00:00',
                'friday_starts_at' => '09:00:00',
                'friday_ends_at' => '17:00:00',
                'saturday_starts_at' => null,
                'saturday_ends_at' => null,
                'sunday_starts_at' => null,
                'sunday_ends_at' => null,
            ]);

            if ($employee->id === 1) {
                $employee->scheduleExclusions()->create([
                    'starts_at' => now()->addDays(2)->startOfDay(),
                    'ends_at' => now()->addDays(17)->endOfDay(),
                ]);
            }
        }
    }
}
