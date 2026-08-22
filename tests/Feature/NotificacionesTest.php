<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\TasaCambio;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisoSeeder::class);
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    private function crearTasaVencida(): TasaCambio
    {
        // Tasa de referencia (bcv por defecto) actualizada el día anterior.
        $tasa = TasaCambio::factory()->create([
            'tipo' => 'bcv',
            'monto' => 100,
            'fecha' => now()->subDay()->toDateString(),
        ]);
        $tasa->forceFill(['created_at' => now()->subDay()])->save();

        return $tasa;
    }

    public function test_recordatorio_aparece_con_la_tasa_vencida()
    {
        $this->crearTasaVencida();
        $this->travelTo(now()->copy()->setTime(10, 0));

        $this->actingAs($this->admin);
        $response = $this->getJson('/notificaciones/pendientes');

        $response->assertOk();
        $response->assertJsonPath('data.0.tipo', 'recordatorio_tasa');
        $response->assertJsonPath('data.0.texto_accion', 'Actualizar tasa');
    }

    public function test_recordatorio_no_aparece_antes_de_la_primera_hora()
    {
        $this->crearTasaVencida();
        $this->travelTo(now()->copy()->setTime(8, 0));

        $this->actingAs($this->admin);
        $response = $this->getJson('/notificaciones/pendientes');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_recordatorio_no_aparece_cuando_esta_deshabilitado()
    {
        Configuracion::updateOrCreate(['clave' => 'recordatorio_tasa_activo'], ['valor' => '0']);
        $this->crearTasaVencida();
        $this->travelTo(now()->copy()->setTime(10, 0));

        $this->actingAs($this->admin);
        $response = $this->getJson('/notificaciones/pendientes');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_recordatorio_desaparece_al_actualizar_la_tasa()
    {
        $this->crearTasaVencida();

        // Nueva fila después del inicio de la ventana de las 9:00.
        $nueva = TasaCambio::factory()->create([
            'tipo' => 'bcv',
            'monto' => 110,
            'fecha' => now()->toDateString(),
        ]);
        $nueva->forceFill(['created_at' => now()->copy()->setTime(10, 30)])->save();

        $this->travelTo(now()->copy()->setTime(11, 0));

        $this->actingAs($this->admin);
        $response = $this->getJson('/notificaciones/pendientes');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_posponer_silencia_hasta_la_proxima_ventana()
    {
        $this->crearTasaVencida();
        $this->travelTo(now()->copy()->setTime(10, 0));

        $this->actingAs($this->admin);

        $this->postJson('/notificaciones/recordatorio_tasa/posponer')->assertOk();

        $response = $this->getJson('/notificaciones/pendientes');
        $response->assertJsonCount(0, 'data');

        // Llega la segunda ventana programada y vuelve a mostrarse.
        $this->travelTo(now()->copy()->setTime(14, 5));
        $response = $this->getJson('/notificaciones/pendientes');
        $response->assertJsonPath('data.0.tipo', 'recordatorio_tasa');
    }

    public function test_posponer_de_tipo_desconocido_falla()
    {
        $this->actingAs($this->admin);

        $this->postJson('/notificaciones/tipo_inexistente/posponer')->assertNotFound();
    }

    public function test_usuario_sin_permiso_no_ve_el_recordatorio()
    {
        $rolCajero = Rol::create(['nombre' => 'Cajero', 'slug' => 'cajero']);
        $rolCajero->permisos()->sync(
            Permiso::whereIn('slug', ['ver-dashboard'])->pluck('id')
        );
        $cajero = User::factory()->create(['rol' => 'cajero']);

        $this->crearTasaVencida();
        $this->travelTo(now()->copy()->setTime(10, 0));

        $this->actingAs($cajero);
        $response = $this->getJson('/notificaciones/pendientes');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_guardar_recordatorio_persiste_interruptor_y_horas()
    {
        $this->actingAs($this->admin);

        $response = $this->from('/herramientas/configuracion')->post('/herramientas/recordatorio', [
            'recordatorio_tasa_activo' => '1',
            'recordatorio_tasa_hora1' => '08:30',
            'recordatorio_tasa_hora2' => '15:00',
        ]);

        $response->assertRedirect('/herramientas/configuracion');
        $response->assertSessionHas('success');

        $this->assertEquals('1', Configuracion::obtener('recordatorio_tasa_activo'));
        $this->assertEquals('08:30', Configuracion::obtener('recordatorio_tasa_hora1'));
        $this->assertEquals('15:00', Configuracion::obtener('recordatorio_tasa_hora2'));
    }

    public function test_guardar_recordatorio_sin_switch_lo_deshabilita()
    {
        Configuracion::updateOrCreate(['clave' => 'recordatorio_tasa_activo'], ['valor' => '1']);

        $this->actingAs($this->admin);

        // El checkbox desmarcado no envía su campo.
        $response = $this->from('/herramientas/configuracion')->post('/herramientas/recordatorio', [
            'recordatorio_tasa_hora1' => '09:00',
            'recordatorio_tasa_hora2' => '14:00',
        ]);

        $response->assertRedirect('/herramientas/configuracion');
        $this->assertEquals('0', Configuracion::obtener('recordatorio_tasa_activo'));
    }

    public function test_validacion_rechaza_segunda_hora_anterior_a_la_primera()
    {
        $this->actingAs($this->admin);

        $response = $this->from('/herramientas/configuracion')->post('/herramientas/recordatorio', [
            'recordatorio_tasa_activo' => '1',
            'recordatorio_tasa_hora1' => '09:00',
            'recordatorio_tasa_hora2' => '07:00',
        ]);

        $response->assertRedirect('/herramientas/configuracion');
        $response->assertSessionHasErrors(['recordatorio_tasa_hora2']);
        $this->assertNull(Configuracion::where('clave', 'recordatorio_tasa_hora2')->first());
    }

    public function test_configuracion_muestra_controles_del_recordatorio()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/herramientas/configuracion');

        $response->assertStatus(200);
        $response->assertSee('recordatorio_tasa_activo');
        $response->assertSee('recordatorio_tasa_hora1');
        $response->assertSee('recordatorio_tasa_hora2');
    }
}
