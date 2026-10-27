<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * The admin accounts to create when they do not exist yet.
     */
    private const ADMINS = [
        // Developer admin
        ['name' => 'Admin User', 'email' => 'abdulsalamamtech@gmail.com'],
        // Application admin
        ['name' => 'Admin User', 'email' => 'contact@onimsagro.com'],
    ];

    private const MIN_PASSWORD_LENGTH = 12;

    /**
     * Run the database seeds.
     *
     * Only creates admins that do not exist yet, using ADMIN_DEFAULT_PASSWORD.
     * Existing admins are never modified, so running this on every deploy is safe.
     */
    public function run(): void
    {
        $missing = array_filter(
            self::ADMINS,
            fn (array $admin) => ! User::where('email', $admin['email'])->exists()
        );

        if (empty($missing)) {
            return;
        }

        $password = config('auth.admin_default_password');

        if (! $this->isStrongEnough($password)) {
            $this->warn('AdminSeeder: ADMIN_DEFAULT_PASSWORD is missing or weaker than '
                .self::MIN_PASSWORD_LENGTH.' characters; no admin users were created.');

            return;
        }

        $role = Role::findOrCreate('super-admin', 'web');

        foreach ($missing as $admin) {
            $user = User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $password,
                'email_verified_at' => now(),
            ]);
            $user->assignRole($role);
        }
    }

    private function isStrongEnough(mixed $password): bool
    {
        return is_string($password) && strlen($password) >= self::MIN_PASSWORD_LENGTH;
    }

    private function warn(string $message): void
    {
        Log::warning($message);
        $this->command?->warn($message);
    }
}
