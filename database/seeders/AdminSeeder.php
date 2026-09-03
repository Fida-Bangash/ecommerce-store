<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed a default admin user that can log in to the dashboard.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ibnesultan.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
