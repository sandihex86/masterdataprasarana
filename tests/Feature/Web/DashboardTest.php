<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.type' => 'v3',
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Master Data Prasarana DJKA')
            ->assertSee('Masuk')
            ->assertSee('reCAPTCHA v3 berjalan otomatis')
            ->assertDontSee('example.com')
            ->assertSee('action="/login"', false)
            ->assertDontSee('https://prasarana.labdata.id')
            ->assertDontSee('Masuk ke sistem.');
    }

    public function test_user_can_login_and_access_dashboard(): void
    {
        $this->seed();
        $user = User::factory()->admin()->create();
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.type' => 'v3',
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
            'services.recaptcha.login_action' => 'login',
            'services.recaptcha.score_threshold' => 0.5,
            'services.recaptcha.verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'action' => 'login',
                'score' => 0.9,
                'challenge_ts' => now()->toIso8601String(),
                'hostname' => 'localhost',
            ]),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'test-recaptcha-token',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Status Modul')
            ->assertSee('Swagger Docs')
            ->assertSee('Admin')
            ->assertSee('Client API');
    }

    public function test_dashboard_system_route_requires_authentication(): void
    {
        $this->get('/dashboard/system')
            ->assertRedirect('/login');
    }

    public function test_dashboard_system_route_returns_json_overview_for_authenticated_user(): void
    {
        $this->seed();
        $user = User::factory()->admin()->create();

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
        $user = User::factory()->admin()->create();

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

    public function test_seeder_does_not_create_dummy_users(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_swagger_assets_are_accessible(): void
    {
        $this->get('/docs/openapi/asset/swagger-ui.css')
            ->assertOk();

        $this->get('/docs/openapi/asset/swagger-ui-bundle.js')
            ->assertOk();
    }
}
