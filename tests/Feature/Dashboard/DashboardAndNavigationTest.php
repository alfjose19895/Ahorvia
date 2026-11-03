<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Livewire\Settings\SettingsManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_protected_financial_routes(): void
    {
        foreach ([
            '/dashboard',
            '/cuentas',
            '/movimientos',
            '/presupuestos',
            '/deudas',
            '/tarjetas',
            '/ahorro',
            '/reportes',
            '/notificaciones',
            '/configuracion',
        ] as $route) {
            $this->get($route)->assertRedirect('/login');
        }
    }

    public function test_dashboard_renders_all_financial_kpis_empty_states_and_navigation_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Alfredo Propietario',
            'currency_code' => 'USD',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('Ahorvia')
            ->assertSee('Tu dinero bajo control. Tus metas más cerca.')
            ->assertSee('Hola, Alfredo')
            ->assertSee('Dinero disponible')
            ->assertSee('Total de activos')
            ->assertSee('Total de pasivos')
            ->assertSee('Patrimonio neto')
            ->assertSee('Ingresos mensuales')
            ->assertSee('Gastos mensuales')
            ->assertSee('Flujo de efectivo')
            ->assertSee('Ahorro acumulado')
            ->assertSee('Metas activas')
            ->assertSee('0% cumplimiento')
            ->assertSee('Próximos vencimientos')
            ->assertSee('Alertas financieras')
            ->assertSee('Distribución de gastos')
            ->assertSee('Evolución financiera mensual')
            ->assertSee('$ 0.00');
    }

    public function test_all_modular_navigation_routes_respond_successfully(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/cuentas' => 'Cuentas Financieras',
            '/movimientos' => 'Ingresos, Gastos y Transferencias',
            '/movimientos?tipo=income' => 'Nuevo ingreso seleccionado desde acceso rápido',
            '/presupuestos' => 'Presupuestos por Categoría',
            '/deudas' => 'Deudas y Préstamos',
            '/tarjetas' => 'Tarjetas de Crédito',
            '/ahorro' => 'Planificador Inteligente de Ahorro a 6 Meses',
            '/reportes' => 'Reportes y Evolución Financiera',
            '/notificaciones' => 'Centro de Notificaciones',
            '/configuracion' => 'Configuración y Seguridad',
        ] as $uri => $expectedHeading) {
            $this->actingAs($user)
                ->get($uri)
                ->assertOk()
                ->assertSee($expectedHeading);
        }
    }

    public function test_user_can_update_profile_preferences_and_password_from_settings(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ActualClave123!'),
            'currency_code' => 'USD',
        ]);

        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->set('name', 'Alfredo Ahorvia')
            ->set('email', 'alfredo@ahorvia.test')
            ->set('currency_code', 'PEN')
            ->set('timezone', 'America/Lima')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Alfredo Ahorvia', $user->name);
        $this->assertSame('alfredo@ahorvia.test', $user->email);
        $this->assertSame('PEN', $user->currency_code);
        $this->assertSame('America/Lima', $user->timezone);

        Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->set('current_password', 'ActualClave123!')
            ->set('password', 'NuevaClaveForte99!')
            ->set('password_confirmation', 'NuevaClaveForte99!')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClaveForte99!', (string) $user->fresh()?->password));
    }

    public function test_pwa_offline_route_and_manifest_use_ahorvia_branding(): void
    {
        $this->get('/offline')
            ->assertOk()
            ->assertSee('Ahorvia')
            ->assertSee('Estás sin conexión a Internet');

        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        $this->assertSame('Ahorvia — Finanzas Personales', $manifest['name']);
        $this->assertSame('Ahorvia', $manifest['short_name']);
        $this->assertSame('#10B981', $manifest['theme_color']);
        $this->assertSame('#0F172A', $manifest['background_color']);
    }

    public function test_artisan_command_creates_authorized_ahorvia_user(): void
    {
        $this->artisan('ahorvia:create-user', [
            '--name' => 'Propietario Inicial',
            '--email' => 'owner@ahorvia.test',
            '--password' => 'OwnerPassword2026!',
            '--currency' => 'EUR',
        ])->assertSuccessful();

        $created = User::query()->where('email', 'owner@ahorvia.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasActiveAccess());
        $this->assertSame('EUR', $created->currency_code);
    }
}
