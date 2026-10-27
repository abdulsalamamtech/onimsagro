<?php

namespace Tests\Feature;

use App\Models\CropType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The technical-support form needs a list of crop types for its dropdown.
 */
class CropTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_list_crop_types(): void
    {
        CropType::factory()->create(['name' => 'Maize']);
        CropType::factory()->create(['name' => 'Cassava']);

        $this->getJson('/api/crop-types')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_the_list_is_empty_not_an_error_when_there_are_no_crop_types(): void
    {
        $this->getJson('/api/crop-types')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_guests_cannot_manage_crop_types(): void
    {
        $this->postJson('/api/admin/crop-types', ['name' => 'Rice'])->assertUnauthorized();
        $this->assertSame(0, CropType::count());
    }

    public function test_an_admin_can_create_rename_and_delete_crop_types(): void
    {
        Sanctum::actingAs(User::factory()->superAdmin()->create());

        $id = $this->postJson('/api/admin/crop-types', ['name' => 'Rice'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Rice')
            ->json('data.id');

        $this->postJson('/api/admin/crop-types', ['name' => 'Rice'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        // Renaming to its own current name is allowed; renaming to another crop's name is not.
        CropType::factory()->create(['name' => 'Yam']);
        $this->putJson('/api/admin/crop-types/'.$id, ['name' => 'Rice'])->assertOk();
        $this->putJson('/api/admin/crop-types/'.$id, ['name' => 'Yam'])->assertUnprocessable();
        $this->putJson('/api/admin/crop-types/'.$id, ['name' => 'Paddy Rice'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Paddy Rice');

        $this->deleteJson('/api/admin/crop-types/'.$id)->assertOk();
        $this->assertSoftDeleted('crop_types', ['id' => $id]);
    }
}
