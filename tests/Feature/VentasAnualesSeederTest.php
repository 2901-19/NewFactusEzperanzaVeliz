<?php

namespace Tests\Feature;

use Database\Seeders\CategoriaSeeder;
use Database\Seeders\ClienteSeeder;
use Database\Seeders\ImpuestoSegmentSeeder;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\ProductoSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\VentasAnualesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VentasAnualesSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermisoSeeder::class,
            CategoriaSeeder::class,
            ClienteSeeder::class,
            ProductoSeeder::class,
            TasaCambioSeeder::class,
            ImpuestoSegmentSeeder::class,
            UserSeeder::class,
        ]);
    }

    public function test_simulacion_corta_genera_facturas_consistentes(): void
    {
        $stats = (new VentasAnualesSeeder)->simular('2025-08-01', '2025-08-10');

        $this->assertGreaterThan(0, $stats['facturas']);
        $this->assertGreaterThan(0, $stats['items']);
        $this->assertSame($stats['facturas'], DB::table('facturas')->count());
        $this->assertSame($stats['items'], DB::table('items_factura')->count());

        $correlativos = DB::table('facturas')->pluck('correlativo');
        $this->assertSame($correlativos->count(), $correlativos->unique()->count(), 'Los correlativos deben ser únicos.');
        $correlativos->each(function ($c) {
            $this->assertMatchesRegularExpression('/^F-\d{6}-\d{3}$/', $c);
        });

        $facturas = DB::table('facturas')->get();
        $anuladas = 0;

        foreach ($facturas as $f) {
            $this->assertContains($f->estado, ['contado', 'credito', 'anulada']);

            if ($f->estado === 'credito') {
                $this->assertNotNull($f->cliente_id, 'Todo crédito debe tener cliente.');
            }

            if ($f->estado === 'anulada') {
                $anuladas++;
            }

            $this->assertEquals(round($f->subtotal_bs + $f->iva_bs, 2), (float) $f->total_bs);
            $this->assertEquals(round((float) $f->total_bs / 58.50, 2), (float) $f->total_usd);
            $this->assertGreaterThan(0, (float) $f->total_bs);

            $fecha = strtotime($f->fecha_venta);
            $this->assertGreaterThanOrEqual(strtotime('2025-08-01 00:00:00'), $fecha);
            $this->assertLessThanOrEqual(strtotime('2025-08-10 23:59:59'), $fecha);

            $detalle = $f->detalle_pago ? json_decode($f->detalle_pago, true) : null;
            if ($f->metodo_pago === 'mixto') {
                $this->assertNotNull($detalle);
                $this->assertCount(2, $detalle);
                $this->assertEqualsWithDelta((float) $f->total_bs, array_sum(array_column($detalle, 'monto')), 0.01);
            }

            $productos = json_decode($f->productos, true);
            $this->assertIsArray($productos);
            $this->assertNotEmpty($productos);
        }

        $this->assertLessThan(0.1, $anuladas / $facturas->count(), 'Las anuladas deben ser menos del 10%.');

        $productos = DB::table('productos')->get();
        foreach ($productos as $p) {
            $this->assertGreaterThanOrEqual(0, (int) $p->stock_paquetes);
            $this->assertGreaterThanOrEqual(0, (int) $p->stock_unidades);
        }
    }

    public function test_simulacion_es_determinista(): void
    {
        $primera = (new VentasAnualesSeeder)->simular('2025-08-01', '2025-08-10');
        $segunda = (new VentasAnualesSeeder)->simular('2025-08-01', '2025-08-10');

        $this->assertSame($primera['facturas'], $segunda['facturas']);
        $this->assertSame($primera['items'], $segunda['items']);
    }
}
