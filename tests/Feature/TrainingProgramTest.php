<?php

namespace Tests\Feature;

use App\Models\FarmingInterest;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Known issue #8: training_programs holds people's sign-ups (name, phone, email), so it
 * must stay private. The training topics shown on the form are farming interests.
 */
class TrainingProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_training_sign_ups(): void
    {
        TrainingProgram::factory()->create(['email' => 'applicant@example.com', 'phone_number' => '08099998888']);

        $response = $this->getJson('/api/training-programs');

        $this->assertNotSame(200, $response->status());
        $response->assertDontSee('applicant@example.com');
        $response->assertDontSee('08099998888');
    }

    public function test_guests_can_list_training_topics_from_farming_interests(): void
    {
        FarmingInterest::factory()->create(['name' => 'Poultry farming']);
        FarmingInterest::factory()->create(['name' => 'Fish farming']);

        $this->getJson('/api/farming-interests')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertSee('Poultry farming');
    }

    public function test_a_guest_can_sign_up_for_training_on_a_topic(): void
    {
        $topic = FarmingInterest::factory()->create(['name' => 'Poultry farming']);

        $this->postJson('/api/training-programs', [
            'full_name' => 'Ada Farmer',
            'phone_number' => '08012345678',
            'email' => 'ada@example.com',
            'farming_interest_id' => $topic->id,
        ])->assertCreated();

        $this->assertDatabaseHas('training_programs', [
            'email' => 'ada@example.com',
            'farming_interest_id' => $topic->id,
        ]);
    }
}
