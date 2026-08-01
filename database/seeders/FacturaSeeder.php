<?php

namespace Database\Seeders;

use App\Models\Factura;
use App\Models\ItemFactura;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\TasaCambio;
use App\Models\Impuesto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        $productos = Producto::all();
        $clientes = Cliente::all();
        $iva = Impuesto::latest('fecha')->first();
        $ivaPorcentaje = $iva ? (float) $iva->porcentaje / 100 : 0.16;

        // Generar facturas de los últimos 3 días
        $inicio = now()->subDays(3);
        $totalFacturas = 0;

        for ($dia = 0; $dia < 3; $dia++) {
            $fecha = $inicio->copy()->addDays($dia);
            $facturasDelDia = rand(2, 4);

            for ($f = 0; $f < $facturasDelDia; $f++) {
                $esCredito = rand(1, 100) <= 25; // 25% chance de crédito
                $cliente = $esCredito ? $clientes->random() : null;

                $correlativo = 'F-' . $fecha->format('ymd') . '-' . str_pad($f + 1, 3, '0', STR_PAD_LEFT);

                $itemsData = [];
                $subtotalBs = 0;
                $ivaBs = 0;
                $itemsFactura = [];
                $cantItems = rand(1, 8);

                for ($i = 0; $i < $cantItems; $i++) {
                    $producto = $productos->random();
                    $cantidad = rand(1, 10);
                    $esMayor = rand(1, 100) <= 30;
                    $tipoVenta = $esMayor ? 'mayor' : 'unitario';
                    $precioUsd = $esMayor ? $producto->precio_mayor_usd : $producto->precio_unitario_usd;

                    $tasa = TasaCambio::where('tipo', $producto->fuente_tasa)
                        ->whereDate('fecha', '<=', $fecha)
                        ->latest()
                        ->first();
                    $montoTasa = $tasa ? (float) $tasa->monto : 60;
                    $precioBs = $precioUsd * $montoTasa;
                    $subtotalItemBs = $precioBs * $cantidad;

                    $subtotalBs += $subtotalItemBs;

                    if ($producto->tiene_iva) {
                        $ivaBs += $subtotalItemBs * $ivaPorcentaje;
                    }

                    $itemsFactura[] = [
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'tipo_venta' => $tipoVenta,
                        'precio_unitario_usd' => $precioUsd,
                        'precio_unitario_bs' => $precioBs,
                        'subtotal' => $subtotalItemBs,
                    ];

                    $itemsData[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'cantidad' => $cantidad,
                        'tipo_venta' => $tipoVenta,
                        'precio_unitario_usd' => $precioUsd,
                    ];
                }

                $totalBs = $subtotalBs + $ivaBs;

                $tasaBcv = TasaCambio::where('tipo', 'bcv')
                    ->whereDate('fecha', '<=', $fecha)
                    ->latest()
                    ->first();
                $montoBcv = $tasaBcv ? (float) $tasaBcv->monto : 1;
                $totalUsd = round($totalBs / $montoBcv, 2);
                $tasaEfectiva = $montoBcv;
                $metodos = ['efectivo', 'punto', 'biopago', 'divisas', 'pago_movil', 'transferencia'];
                $esMixto = rand(1, 100) <= 15;
                $metodoPago = $esMixto ? 'mixto' : $metodos[array_rand($metodos)];

                $detallePago = null;
                if ($esMixto) {
                    $opciones = $metodos;
                    $indice1 = array_rand($opciones);
                    $metodo1 = $opciones[$indice1];
                    unset($opciones[$indice1]);
                    $metodo2 = $opciones[array_rand($opciones)];
                    $monto1 = round($totalBs * (rand(30, 70) / 100), 2);
                    $detallePago = [
                        ['metodo' => $metodo1, 'monto' => $monto1],
                        ['metodo' => $metodo2, 'monto' => round($totalBs - $monto1, 2)],
                    ];
                }

                Factura::firstOrCreate(
                    ['correlativo' => $correlativo],
                    [
                        'cliente_id' => $cliente?->id,
                        'user_id' => rand(1, 2),
                        'productos' => $itemsData,
                        'tasa_cambio' => round($tasaEfectiva, 2),
                        'metodo_pago' => $metodoPago,
                        'detalle_pago' => $detallePago,
                        'subtotal_bs' => $subtotalBs,
                        'iva_bs' => $ivaBs,
                        'total_bs' => $totalBs,
                        'total_usd' => $totalUsd,
                        'estado' => $esCredito ? 'credito' : 'contado',
                        'estado_credito' => $esCredito ? (rand(1, 100) <= 60 ? 'cancelado' : 'pendiente') : null,
                        'fecha_venta' => $fecha,
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ]
                );

                $totalFacturas++;
            }
        }

        // Recuperar las facturas creadas para insertar items_factura
        $this->command->info("✓ $totalFacturas facturas creadas (con items insertados vía trigger post-create)");

        // Ahora insertar items_factura directamente desde los datos guardados en JSON
        $facturas = Factura::all();
        $itemsInsertados = 0;

        foreach ($facturas as $factura) {
            $productosData = $factura->productos;
            if (!is_array($productosData)) continue;

            foreach ($productosData as $item) {
                $producto = Producto::find($item['producto_id']);
                $tasa = TasaCambio::where('tipo', $producto?->fuente_tasa ?? 'promedio')->latest()->first()?->monto ?? 60;

                ItemFactura::firstOrCreate(
                    ['factura_id' => $factura->id, 'producto_id' => $item['producto_id']],
                    [
                        'cantidad' => $item['cantidad'],
                        'tipo_venta' => $item['tipo_venta'] ?? 'unitario',
                        'precio_unitario_usd' => $item['precio_unitario_usd'],
                        'precio_unitario_bs' => $item['precio_unitario_usd'] * $tasa,
                        'subtotal' => $item['precio_unitario_usd'] * $item['cantidad'] * $tasa,
                    ]
                );
                $itemsInsertados++;
            }
        }

        $this->command->info("✓ $itemsInsertados items de factura insertados");
    }
}
