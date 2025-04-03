<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (UserRoleEnum::cases() as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role->value],
                [
                    'guard_name' => 'web', // 👈 Add this line
                    'created_at' => now(), 
                    'updated_at' => now()
                ]
            );

        }
    }
}
