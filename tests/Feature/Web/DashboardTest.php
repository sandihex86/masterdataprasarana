<?php

namespace Tests\Feature\Web;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_root(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Master Data Prasarana DJKA')
            ->assertSee('Masuk ke Sistem')
            ->assertSee('superadmin@example.com')
            ->assertSee('viewer@example.com')
            ->assertSee('action="/login"', false)
            ->assertDontSee('http://');
    }

    public function test_user_can_login_and_access_dashboard(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Operasional')
            ->assertSee('Status Modul')
            ->assertSee('Buka Swagger Docs')
            ->assertSee('Admin')
            ->assertSee('Client Dummy Lokal');
    }

    public function test_dashboard_system_route_requires_authentication(): void
    {
        $this->get('/dashboard/system')
            ->assertRedirect('/login');
    }

    public function test_dashboard_system_route_returns_json_overview_for_authenticated_user(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($user)
            ->getJson('/dashboard/system')
            ->assertOk()
            ->assertJsonPath('application.name', config('app.name'))
            ->assertJsonPath('application.debug', config('app.debug'))
            ->assertJsonStructure([
                'application',
                'health' => ['status', 'checks'],
                'metrics',
                'modules',
                'user_roles',
                'api_routes',
            ]);
    }

    public function test_non_privileged_user_cannot_access_system_snapshot(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs($user)
            ->get('/dashboard/system')
            ->assertForbidden();
    }

    public function test_swagger_docs_require_authentication(): void
    {
        $this->get('/docs/swagger')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_swagger_and_openapi_json(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get('/docs/swagger')
            ->assertOk()
            ->assertSee('Master Data Prasarana DJKA API Docs')
            ->assertSee('/docs/openapi');

        $this->actingAs($user)
            ->getJson('/docs/openapi')
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.0')
            ->assertJsonPath('paths./api/v1/health.get.summary', 'Ringkasan health dan readiness aplikasi')
            ->assertJsonPath('paths./api/v1/master-data.get.summary', 'Daftar master data');
    }

    public function test_seeder_creates_dummy_users_for_each_supported_role(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'superadmin@example.com', 'role' => UserRole::Superadmin->value]);
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => UserRole::Admin->value]);
        $this->assertDatabaseHas('users', ['email' => 'operator@example.com', 'role' => UserRole::Operator->value]);
        $this->assertDatabaseHas('users', ['email' => 'verifikator@example.com', 'role' => UserRole::Verifikator->value]);
        $this->assertDatabaseHas('users', ['email' => 'viewer@example.com', 'role' => UserRole::Viewer->value]);
    }

    public function test_swagger_assets_are_accessible(): void
    {
        $this->get('/docs/openapi/asset/swagger-ui.css')
            ->assertOk();

        $this->get('/docs/openapi/asset/swagger-ui-bundle.js')
            ->assertOk();
    }
}
