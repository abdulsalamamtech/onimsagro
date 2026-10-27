<?php

namespace Tests\Feature;

use App\Models\CropType;
use App\Models\TechnicalSupport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Known issue #6: the website's technical-support form had no route to submit to.
 */
class TechnicalSupportTest extends TestCase
{
    use RefreshDatabase;

    private function validRequest(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Musa Farmer',
            'phone_number' => '08012345678',
            'email' => 'musa@example.com',
            'stage_of_plant' => 'flowering',
            'problem_with_crop' => 'Leaves are turning yellow with brown spots',
        ], $overrides);
    }

    public function test_a_guest_can_submit_a_technical_support_request(): void
    {
        $maize = CropType::factory()->create(['name' => 'Maize']);

        $response = $this->postJson('/api/technical-supports', $this->validRequest(['crop_type_id' => $maize->id]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Musa Farmer')
            ->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('technical_supports', [
            'email' => 'musa@example.com',
            'crop_type_id' => $maize->id,
            'status' => 'pending',
        ]);
    }

    public function test_crop_type_is_optional(): void
    {
        $this->postJson('/api/technical-supports', $this->validRequest())->assertCreated();
    }

    public function test_a_guest_cannot_set_the_status_of_their_request(): void
    {
        $this->postJson('/api/technical-supports', $this->validRequest(['status' => 'solved']))->assertCreated();

        $this->assertSame('pending', TechnicalSupport::sole()->status);
    }

    public function test_invalid_requests_are_rejected(): void
    {
        $this->postJson('/api/technical-supports', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'phone_number', 'email', 'stage_of_plant', 'problem_with_crop']);

        $this->postJson('/api/technical-supports', $this->validRequest(['email' => 'not-an-email', 'crop_type_id' => 999]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'crop_type_id']);
    }

    public function test_guests_cannot_list_or_manage_requests(): void
    {
        $request = TechnicalSupport::factory()->create();

        $this->getJson('/api/admin/technical-supports')->assertUnauthorized();
        $this->getJson('/api/admin/technical-supports/'.$request->id)->assertUnauthorized();
        $this->patchJson('/api/admin/technical-supports/'.$request->id, ['status' => 'solved'])->assertUnauthorized();
        $this->deleteJson('/api/admin/technical-supports/'.$request->id)->assertUnauthorized();
        // There is no public list of other people's requests.
        $this->assertNotSame(200, $this->getJson('/api/technical-supports')->status());
    }

    public function test_an_admin_can_list_view_update_and_delete_requests(): void
    {
        Sanctum::actingAs(User::factory()->superAdmin()->create());
        $requests = TechnicalSupport::factory()->count(2)->create();
        $first = $requests->first();

        $this->getJson('/api/admin/technical-supports')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/admin/technical-supports/'.$first->id)
            ->assertOk()
            ->assertJsonPath('data.id', $first->id);

        // An admin can change only the status.
        $this->patchJson('/api/admin/technical-supports/'.$first->id, ['status' => 'solved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'solved');
        $this->assertSame('solved', $first->refresh()->status);

        $this->patchJson('/api/admin/technical-supports/'.$first->id, ['status' => 'unknown'])
            ->assertUnprocessable();

        $this->deleteJson('/api/admin/technical-supports/'.$first->id)->assertOk();
        $this->assertSoftDeleted($first);
    }
}
