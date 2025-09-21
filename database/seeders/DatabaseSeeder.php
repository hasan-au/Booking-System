<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        DB::table('admins')->insert([
            'name' => 'Hasan',
            'email' => 'hasan@laravel.com',
            'password' => bcrypt('aa11ss22'),
        ]);
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $employees = Employee::factory()->count(2)->create();
        $services = Service::factory()->count(3)->create();
        $employees->each(function ($employee) {
            $employee->services()->attach(Service::inRandomOrder()->take(2)->pluck('id'));
            $employee->employeeDayOffs()->createMany(
                \App\Models\EmployeeDayOff::factory()->count(1)->make()->toArray()
            );
            // Create short afternoon break (every day)
            for($i=0; $i<=6; $i++){
                $employee->employeeBreaks()->create([
                    'start_time' => '12:00:00',
                    'end_time' => '13:00:00',
                    'is_recurring' => true,
                    'weekday' => $i, // Monday to Sunday
                    'date' => null,
                ]);
            }
        });

        // Booking::factory()->create([
        //     'employee_id' => function () {
        //         return Employee::inRandomOrder()->first()->id;
        //     },
        //     'service_id' => function () {
        //         return Service::inRandomOrder()->first()->id;
        //     },
        // ]);
    }
}
