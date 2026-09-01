<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $timeSlots = [
            ['Pagi', '07:00', '08:00', 200000, 250000],
            ['Pagi', '08:00', '09:00', 200000, 250000],
            ['Pagi', '09:00', '10:00', 200000, 250000],
            ['Pagi', '10:00', '11:00', 200000, 250000],
            ['Siang', '11:00', '12:00', 200000, 250000],
            ['Siang', '12:00', '13:00', 200000, 250000],
            ['Siang', '13:00', '14:00', 200000, 250000],
            ['Siang', '14:00', '15:00', 200000, 250000],
            ['Sore', '15:00', '16:00', 200000, 300000],
            ['Sore', '16:00', '17:00', 300000, 300000],
            ['Maghrib', '17:00', '18:00', 300000, 300000],
            ['Maghrib', '18:00', '19:00', 300000, 300000],
            ['Malam', '19:00', '20:00', 300000, 300000],
            ['Malam', '20:00', '21:00', 300000, 300000],
            ['Midnight', '21:00', '22:00', 300000, 300000],
            ['Midnight', '22:00', '23:00', 300000, 300000],
        ];

        foreach ($timeSlots as [$name, $start, $end, $weekday_price, $weekend_price]) {
            DB::table('time_slots')->insert([
                'name' => $name,
                'start_time' => $start,
                'end_time' => $end,
                'weekday_price' => $weekday_price,
                'weekend_price' => $weekend_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
