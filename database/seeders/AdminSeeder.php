<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Cek apakah admin sudah ada
        $adminExists = DB::table('admins')->where('email', 'admin@minisoccer.com')->exists();

        if (! $adminExists) {
            DB::table('admins')->insert([
                'name' => 'Super Admin',
                'email' => 'admin@minisoccer.com',
                'password' => Hash::make('admin123'), // password hashed
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
