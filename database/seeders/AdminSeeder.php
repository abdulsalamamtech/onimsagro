<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // The password should be change immediately after deployment
        // Developer admin
        if (!User::where('email', 'abdulsalamamtech@gmail.com')->exists()) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'abdulsalamamtech@gmail.com',
                'password' => bcrypt('password'), // Use bcrypt for password hashing by default
                'email_verified_at' => now(),
            ]);
            $user->assignRole('super-admin');
        }
        // Application admin
        if (!User::where('email', 'contact@onimsagro.com')->exists()) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'contact@onimsagro.com',
                'password' => bcrypt('password'), // Use bcrypt for password hashing by default
                'email_verified_at' => now(),
            ]);
            $user->assignRole('super-admin');
        }
    }
}
