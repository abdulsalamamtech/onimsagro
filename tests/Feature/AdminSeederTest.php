<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Known issue #1: AdminSeeder used the password "password" and runs on every deploy.
 */
class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_EMAILS = ['abdulsalamamtech@gmail.com', 'contact@onimsagro.com'];

    private const STRONG_PASSWORD = 'Test-Only-Strong-Passw0rd!';

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super-admin', 'web');
    }

    public function test_no_admin_is_created_when_the_password_variable_is_missing(): void
    {
        config(['auth.admin_default_password' => null]);
        Log::spy();

        $this->seed(AdminSeeder::class);

        $this->assertSame(0, User::count());
        Log::shouldHaveReceived('warning')->atLeast()->once();
    }

    public function test_no_admin_is_created_when_the_password_is_weak(): void
    {
        config(['auth.admin_default_password' => 'password']);
        Log::spy();

        $this->seed(AdminSeeder::class);

        $this->assertSame(0, User::count());
        Log::shouldHaveReceived('warning')->atLeast()->once();
    }

    public function test_admins_are_created_with_the_password_from_the_environment(): void
    {
        config(['auth.admin_default_password' => self::STRONG_PASSWORD]);

        $this->seed(AdminSeeder::class);

        foreach (self::ADMIN_EMAILS as $email) {
            $admin = User::where('email', $email)->sole();
            $this->assertTrue(Hash::check(self::STRONG_PASSWORD, $admin->password));
            $this->assertFalse(Hash::check('password', $admin->password));
            $this->assertTrue($admin->hasRole('super-admin'));
        }
    }

    public function test_an_existing_admins_password_is_never_changed(): void
    {
        $existing = User::factory()->create([
            'email' => 'abdulsalamamtech@gmail.com',
            'password' => 'The-Live-Password-They-Chose-1',
        ]);
        $originalHash = $existing->password;
        config(['auth.admin_default_password' => self::STRONG_PASSWORD]);

        // Deploys run the seeder every time, so run it more than once.
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->assertSame($originalHash, $existing->refresh()->password);
        $this->assertSame(1, User::where('email', 'abdulsalamamtech@gmail.com')->count());
    }
}
