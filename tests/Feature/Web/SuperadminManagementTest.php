<?php

namespace Tests\Feature\Web;

use App\Models\ApiClient;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_manage_users_from_dashboard(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Root Admin',
        ]);

        $this->actingAs($superadmin)
            ->get('/dashboard/superadmin/users')
            ->assertOk()
            ->assertSee('Manajemen User Superadmin')
            ->assertSee('Tambah User');

        $createResponse = $this->actingAs($superadmin)
            ->postJson('/dashboard/superadmin/users/records', [
                'name' => 'Operator Satu',
                'email' => 'operator1@example.test',
                'role' => 'operator',
                'password' => 'password123',
                'email_verified' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Operator Satu')
            ->assertJsonPath('data.role', 'operator');

        $uuid = $createResponse->json('data.uuid');

        $this->assertDatabaseHas('users', [
            'uuid' => $uuid,
            'email' => 'operator1@example.test',
            'role' => 'operator',
        ]);

        $this->actingAs($superadmin)
            ->patchJson("/dashboard/superadmin/users/records/{$uuid}", [
                'name' => 'Admin Operasional',
                'role' => 'admin',
                'email_verified' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Admin Operasional')
            ->assertJsonPath('data.role', 'admin');

        $this->assertDatabaseHas('users', [
            'uuid' => $uuid,
            'name' => 'Admin Operasional',
            'role' => 'admin',
        ]);

        $this->actingAs($superadmin)
            ->deleteJson("/dashboard/superadmin/users/records/{$uuid}")
            ->assertOk();

        $this->assertDatabaseMissing('users', [
            'uuid' => $uuid,
        ]);
    }

    public function test_superadmin_cannot_delete_self_or_remove_last_superadmin_role(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->deleteJson("/dashboard/superadmin/users/records/{$superadmin->uuid}")
            ->assertStatus(422);

        $this->actingAs($superadmin)
            ->patchJson("/dashboard/superadmin/users/records/{$superadmin->uuid}", [
                'role' => 'viewer',
            ])
            ->assertStatus(422);
    }

    public function test_superadmin_can_manage_api_clients_and_generate_bearer_token(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->get('/dashboard/superadmin/api-clients')
            ->assertOk()
            ->assertSee('Generator Bearer Key API')
            ->assertSee('Tambah Client API');

        $createResponse = $this->actingAs($superadmin)
            ->postJson('/dashboard/superadmin/api-clients/records', [
                'name' => 'Bridge Integrator',
                'code' => 'bridge_integrator',
                'owner_name' => 'Tim Integrasi',
                'owner_email' => 'integrasi@example.test',
                'description' => 'Client khusus sinkronisasi data jembatan.',
                'allowed_ips' => ['127.0.0.1'],
                'allowed_origins' => ['https://example.test'],
                'rate_limit_per_minute' => 120,
                'rate_limit_per_day' => 5000,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'bridge_integrator');

        $uuid = $createResponse->json('data.uuid');

        $this->actingAs($superadmin)
            ->patchJson("/dashboard/superadmin/api-clients/records/{$uuid}", [
                'owner_name' => 'Tim Integrasi Baru',
                'rate_limit_per_day' => 7000,
            ])
            ->assertOk()
            ->assertJsonPath('data.owner_name', 'Tim Integrasi Baru')
            ->assertJsonPath('data.rate_limit_per_day', 7000);

        $tokenResponse = $this->actingAs($superadmin)
            ->postJson("/dashboard/superadmin/api-clients/records/{$uuid}/tokens", [
                'token_name' => 'bridge-prod-reader',
                'abilities' => ['master-data:read', 'imports:read'],
                'expires_at' => now()->addDay()->toIso8601String(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.token.name', 'bridge-prod-reader');

        $plainTextToken = $tokenResponse->json('data.plain_text_token');

        $this->assertIsString($plainTextToken);
        $this->assertNotSame('', $plainTextToken);
        $this->assertStringContainsString('|', $plainTextToken);

        $client = ApiClient::query()->where('uuid', $uuid)->firstOrFail();

        $this->assertDatabaseHas('personal_access_tokens', [
            'api_client_id' => $client->id,
            'name' => 'bridge-prod-reader',
        ]);

        $this->actingAs($superadmin)
            ->deleteJson("/dashboard/superadmin/api-clients/records/{$uuid}")
            ->assertOk();

        $this->assertNotNull(ApiClient::withTrashed()->where('uuid', $uuid)->value('deleted_at'));
        $this->assertSame(0, PersonalAccessToken::query()->where('api_client_id', $client->id)->count());
    }

    public function test_admin_cannot_access_superadmin_user_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/dashboard/superadmin/users')
            ->assertForbidden();

        $this->actingAs($admin)
            ->getJson('/dashboard/superadmin/users/records')
            ->assertForbidden();
    }

    public function test_admin_cannot_access_superadmin_api_client_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $apiClient = ApiClient::factory()->create();

        $this->actingAs($admin)
            ->get('/dashboard/superadmin/api-clients')
            ->assertForbidden();

        $this->actingAs($admin)
            ->postJson("/dashboard/superadmin/api-clients/records/{$apiClient->uuid}/tokens", [
                'token_name' => 'blocked-token',
                'abilities' => ['master-data:read'],
            ])
            ->assertForbidden();
    }
}
