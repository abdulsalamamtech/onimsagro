<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        // $this->call(UserRoleSeeder::class);
        // php artisan db:seed

        if (!User::where('email', 'abdulsalamamtech@gmail.com')->exists()) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'abdulsalamamtech@gmail.com',
                'password' => bcrypt('password'), // Use bcrypt for password hashing by default
                'email_verified_at' => now(),
            ]);
            $user->assignRole('super-admin');
        }
    }
}
