<?php

namespace Tests\Feature;

use App\Models\ServiceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceLogTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'motor' => 'Vario 125',
            'serviced_at' => '2026-10-12',
            'odometer_km' => 24310,
            'service_type' => 'Ganti oli mesin',
            'cost' => 55000,
            'workshop' => 'AHASS Sleman',
            'notes' => null,
        ], $override);
    }

    public function test_guest_gets_401_on_every_endpoint(): void
    {
        $log = ServiceLog::factory()->create();

        $this->getJson('/api/service-logs')->assertUnauthorized();
        $this->postJson('/api/service-logs', $this->payload())->assertUnauthorized();
        $this->getJson("/api/service-logs/{$log->id}")->assertUnauthorized();
        $this->putJson("/api/service-logs/{$log->id}", $this->payload())->assertUnauthorized();
        $this->deleteJson("/api/service-logs/{$log->id}")->assertUnauthorized();
    }

    public function test_guest_without_json_accept_header_still_gets_401(): void
    {
        // Nginx meneruskan permintaan tanpa Accept: application/json. Tanpa
        // pengaturan redirectGuestsTo, Laravel mencari route login dan gagal 500.
        $this->get('/api/service-logs')->assertUnauthorized();
    }

    public function test_index_lists_only_own_logs(): void
    {
        $user = User::factory()->create();
        ServiceLog::factory()->count(2)->create(['user_id' => $user->id]);
        ServiceLog::factory()->create(); // milik user lain

        Sanctum::actingAs($user);

        $this->getJson('/api/service-logs')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_store_creates_log_and_returns_201(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/service-logs', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.service_type', 'Ganti oli mesin');

        $this->assertDatabaseHas('service_logs', [
            'user_id' => $user->id,
            'odometer_km' => 24310,
        ]);
    }

    public function test_store_with_invalid_data_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/service-logs', $this->payload([
            'motor' => '',
            'odometer_km' => 'banyak',
            'cost' => -1,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['motor', 'odometer_km', 'cost'])
            ->assertJsonPath('errors.cost.0', 'Biaya tidak boleh kurang dari 0.');
    }

    public function test_show_returns_own_log_but_404_for_other_users_log(): void
    {
        $user = User::factory()->create();
        $own = ServiceLog::factory()->create(['user_id' => $user->id]);
        $other = ServiceLog::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/service-logs/{$own->id}")->assertOk()->assertJsonPath('data.id', $own->id);
        $this->getJson("/api/service-logs/{$other->id}")->assertNotFound();
    }

    public function test_update_changes_the_log(): void
    {
        $user = User::factory()->create();
        $log = ServiceLog::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->putJson("/api/service-logs/{$log->id}", $this->payload(['cost' => 75000]))
            ->assertOk()
            ->assertJsonPath('data.cost', 75000);

        $this->assertDatabaseHas('service_logs', ['id' => $log->id, 'cost' => 75000]);
    }

    public function test_destroy_deletes_the_log(): void
    {
        $user = User::factory()->create();
        $log = ServiceLog::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/service-logs/{$log->id}")->assertOk();

        $this->assertDatabaseMissing('service_logs', ['id' => $log->id]);
    }
}
