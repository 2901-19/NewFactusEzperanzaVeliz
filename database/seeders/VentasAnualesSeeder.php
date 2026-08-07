<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VentasAnualesSeeder extends Seeder
{
    private array $nombres = [];
    private array $upp = [];
    private array $stock = [];
    private array $vendido = [];
    private array $precioU = [];
    private array $precioM = [];
    private array $fuentes = [];
    private array $pesos = [];
    private array $clientes = [];
    private array $clientesJ = [];
    private array $tasas = [];

    private float $tasaBcv = 58.50;

    public function run(): void
    {
        $stats = $this->simular('2025-08-01', '2026-07-31');

        $this->command->info(
            "✓ {$stats['facturas']} facturas y {$stats['items']} ítems generados ({$stats['inicio']} → {$stats['fin']})"
        );
    }

    public function simular(string $inicio, string $fin): array
    {
        mt_srand(20250801);

        DB::table('items_factura')->delete();
        DB::table('facturas')->delete();

        $this->cargarDatos();

        $fecha = new \DateTimeImmutable($inicio);
        $finDt = new \DateTimeImmutable($fin);

        $facturasCreadas = 0;
        $itemsCreados = 0;
        $bufferItems = [];

        while ($fecha <= $finDt) {
            $numFacturas = $this->facturasDelDia($fecha);

            for ($n = 0; $n < $numFacturas; $n++) {
                $generada = $this->generarFactura($fecha, $n + 1);

                $id = DB::table('facturas')->insertGetId($generada['factura']);

                foreach ($generada['items'] as $item) {
                    $fila = $item;
                    unset($fila['nombre']);
                    $fila['factura_id'] = $id;
                    $bufferItems[] = $fila;
                    $itemsCreados++;
                }

                if (count($bufferItems) >= 500) {
                    DB::table('items_factura')->insert($bufferItems);
                    $bufferItems = [];
                }

                $facturasCreadas++;
            }

            if ($this->esDiaRecarga($fecha)) {
                $this->recargarStock();
            }

            $fecha = $fecha->modify('+1 day');
        }

        if ($bufferItems) {
            DB::table('items_factura')->insert($bufferItems);
        }

        $this->persistirStock();

        return [
            'facturas' => $facturasCreadas,
            'items' => $itemsCreados,
            'inicio' => $inicio,
            'fin' => $fin,
        ];
    }

    private function cargarDatos(): void
    {
        $productos = DB::table('productos')
            ->select('id', 'nombre', 'unidades_por_paquete', 'stock_paquetes', 'stock_unidades', 'precio_unitario_usd', 'precio_mayor_usd', 'fuente_tasa')
            ->whereNull('deleted_at')
            ->get();

        foreach ($productos as $p) {
            $id = (int) $p->id;
            $this->nombres[$id] = $p->nombre;
            $this->upp[$id] = (int) $p->unidades_por_paquete;
            $this->stock[$id] = $this->upp[$id] * mt_rand(15, 40) + mt_rand(0, $this->upp[$id] - 1);
            $this->vendido[$id] = 0;
            $this->precioU[$id] = (float) $p->precio_unitario_usd;
            $this->precioM[$id] = (float) $p->precio_mayor_usd;
            $this->fuentes[$id] = $p->fuente_tasa;
            $this->pesos[$id] = $this->pesoDelProducto($p->nombre);
        }

        $clientes = DB::table('clientes')->select('id', 'ci')->get();
        foreach ($clientes as $c) {
            $id = (int) $c->id;
            $this->clientes[] = $id;
            if (str_starts_with($c->ci, 'J-')) {
                $this->clientesJ[$id] = true;
            }
        }

        $tasas = DB::table('tasa_cambios')->select('tipo', 'monto')->get();
        foreach ($tasas as $t) {
            $this->tasas[$t->tipo] = (float) $t->monto;
        }
        $this->tasas['promedio'] ??= 59.25;
        $this->tasas['dolar'] ??= 60.00;
        $this->tasas['bcv'] ??= 58.50;
        $this->tasaBcv = $this->tasas['bcv'] > 0 ? $this->tasas['bcv'] : 58.50;
    }

    private function pesoDelProducto(string $nombre): int
    {
        $populares = ['Arroz', 'Harina PAN', 'Huevos', 'Leche Completa', 'Coca-Cola', 'Aceite de Maíz', 'Café La Fina', 'Azúcar', 'Atún', 'Pasta Espagueti'];
        $medianos = ['Papas Lays', 'Galletas', 'Queso', 'Jugo', 'Agua Mineral', 'Malta', 'Margarina', 'Mayonesa', 'Detergente Fab', 'Cloro', 'Jabón', 'Pasta Dental', 'Sardinas', 'Frijoles'];

        foreach ($populares as $patron) {
            if (str_starts_with($nombre, $patron)) {
                return 5;
            }
        }
        foreach ($medianos as $patron) {
            if (str_starts_with($nombre, $patron)) {
                return 3;
            }
        }

        return 1;
    }

    private function facturasDelDia(\DateTimeImmutable $fecha): int
    {
        $mes = (int) $fecha->format('n');
        $factoresMes = [1 => 0.80, 2 => 0.88, 3 => 1.00, 4 => 1.05, 5 => 1.00, 6 => 0.95, 7 => 1.05, 8 => 1.20, 9 => 1.15, 10 => 1.10, 11 => 1.25, 12 => 1.55];
        $factorMes = $factoresMes[$mes] ?? 1.0;

        $factorSemana = match ((int) $fecha->format('w')) {
            6 => 1.30,
            0 => 0.80,
            default => 1.0,
        };

        $md = $fecha->format('m-d');
        $factorEvento = 1.0;
        if ($md === '12-24' || $md === '12-31') {
            $factorEvento = 2.0;
        } elseif ($md === '01-01') {
            $factorEvento = 0.25;
        } elseif (in_array($fecha->format('Y-m-d'), ['2026-04-02', '2026-04-04'])) {
            $factorEvento = 1.4;
        }

        if ((int) $fecha->format('Y') === 2025 && $mes === 8 && (int) $fecha->format('j') >= 15) {
            $factorEvento *= 1.15;
        }

        $ruido = mt_rand(-20, 20) / 100;
        $n = (int) round(65 * $factorMes * $factorSemana * $factorEvento * (1 + $ruido));

        return max(5, min(130, $n));
    }

    private function generarFactura(\DateTimeImmutable $fecha, int $secuencia): array
    {
        $roll = mt_rand(1, 100);
        $anulada = $roll <= 5;
        $esCredito = !$anulada && $roll <= 23 && $this->clientes !== [];

        $clienteId = null;
        $estado = $anulada ? 'anulada' : ($esCredito ? 'credito' : 'contado');
        $estadoCredito = null;

        if ($esCredito) {
            $clienteId = $this->clientes[array_rand($this->clientes)];
            $estadoCredito = mt_rand(1, 100) <= 60 ? 'cancelado' : 'pendiente';
        } elseif (!$anulada && mt_rand(1, 100) > 55) {
            $clienteId = $this->clientes[array_rand($this->clientes)];
        }

        $clienteMayorista = $clienteId !== null && isset($this->clientesJ[$clienteId]);

        $metodo = $this->metodoPago();

        $r = mt_rand(1, 100);
        if ($r <= 35) {
            $numItems = 1;
        } elseif ($r <= 85) {
            $numItems = mt_rand(2, 3);
        } else {
            $numItems = mt_rand(4, 5);
        }

        $items = $this->generarItems($numItems, $anulada, $clienteMayorista);

        $subtotalBs = 0.0;
        foreach ($items as $it) {
            $subtotalBs += $it['subtotal'];
        }
        $totalBs = round($subtotalBs, 2);
        $totalUsd = round($totalBs / $this->tasaBcv, 2);

        $detallePago = null;
        if ($metodo === 'mixto') {
            $metodos = ['efectivo', 'punto', 'biopago', 'divisas', 'pago_movil', 'transferencia'];
            $i = array_rand($metodos);
            $m1 = $metodos[$i];
            unset($metodos[$i]);
            $metodos = array_values($metodos);
            $m2 = $metodos[array_rand($metodos)];
            $monto1 = round($totalBs * mt_rand(30, 70) / 100, 2);
            $detallePago = [
                ['metodo' => $m1, 'monto' => $monto1],
                ['metodo' => $m2, 'monto' => round($totalBs - $monto1, 2)],
            ];
        }

        $fechaVenta = $fecha->format('Y-m-d') . ' ' . $this->horaVenta();

        return [
            'factura' => [
                'correlativo' => 'F-' . $fecha->format('ymd') . '-' . str_pad($secuencia, 3, '0', STR_PAD_LEFT),
                'cliente_id' => $clienteId,
                'user_id' => mt_rand(1, 100) <= 70 ? 2 : 1,
                'productos' => json_encode($items),
                'tasa_cambio' => round($this->tasaBcv, 2),
                'metodo_pago' => $metodo,
                'detalle_pago' => $detallePago !== null ? json_encode($detallePago) : null,
                'subtotal_bs' => $totalBs,
                'iva_bs' => 0,
                'total_bs' => $totalBs,
                'total_usd' => $totalUsd,
                'estado' => $estado,
                'estado_credito' => $estadoCredito,
                'fecha_venta' => $fechaVenta,
                'created_at' => $fechaVenta,
                'updated_at' => $fechaVenta,
            ],
            'items' => $items,
        ];
    }

    private function metodoPago(): string
    {
        $m = mt_rand(1, 100);

        return match (true) {
            $m <= 40 => 'efectivo',
            $m <= 55 => 'mixto',
            $m <= 70 => 'punto',
            $m <= 85 => 'pago_movil',
            $m <= 93 => 'divisas',
            $m <= 98 => 'transferencia',
            default => 'biopago',
        };
    }

    private function horaVenta(): string
    {
        $r = mt_rand(1, 100);
        if ($r <= 25) {
            $hora = mt_rand(7, 11);
        } elseif ($r <= 45) {
            $hora = mt_rand(12, 14);
        } elseif ($r <= 80) {
            $hora = mt_rand(15, 18);
        } else {
            $hora = mt_rand(19, 20);
        }

        return sprintf('%02d:%02d:00', $hora, mt_rand(0, 59));
    }

    private function generarItems(int $numItems, bool $anulada, bool $clienteMayorista): array
    {
        $items = [];
        $intentos = 0;
        $maxIntentos = $numItems * 8;

        while (count($items) < $numItems && $intentos < $maxIntentos) {
            $intentos++;
            $id = $this->seleccionarProducto();

            [$tipo, $cantidad] = $this->tipoYCantidad($clienteMayorista);

            if (!$anulada) {
                $disponible = $this->stock[$id];
                if ($disponible <= 0) {
                    continue;
                }
                $cantidad = min($cantidad, $disponible);
            }

            $precioUsd = $tipo === 'mayor' ? $this->precioM[$id] : $this->precioU[$id];
            $tasa = $this->tasas[$this->fuentes[$id]];
            $precioBs = round($precioUsd * $tasa, 2);
            $subtotal = round($precioBs * $cantidad, 2);

            $items[] = [
                'producto_id' => $id,
                'nombre' => $this->nombres[$id],
                'cantidad' => $cantidad,
                'tipo_venta' => $tipo,
                'precio_unitario_usd' => $precioUsd,
                'precio_unitario_bs' => $precioBs,
                'subtotal' => $subtotal,
            ];

            if (!$anulada) {
                $this->stock[$id] -= $cantidad;
                $this->vendido[$id] += $cantidad;
            }
        }

        return $items;
    }

    private function tipoYCantidad(bool $clienteMayorista): array
    {
        if ($clienteMayorista) {
            if (mt_rand(1, 100) <= 85) {
                return ['mayor', mt_rand(12, 48)];
            }
            return ['unitario', mt_rand(1, 6)];
        }

        if (mt_rand(1, 100) <= 80) {
            return ['unitario', mt_rand(1, 6)];
        }

        return ['mayor', mt_rand(6, 12)];
    }

    private function seleccionarProducto(): int
    {
        $total = array_sum($this->pesos);
        $roll = mt_rand(1, $total);

        foreach ($this->pesos as $id => $w) {
            $roll -= $w;
            if ($roll <= 0) {
                return $id;
            }
        }

        return (int) array_key_first($this->pesos);
    }

    private function esDiaRecarga(\DateTimeImmutable $fecha): bool
    {
        return in_array((int) $fecha->format('N'), [1, 4]);
    }

    private function recargarStock(): void
    {
        foreach ($this->stock as $id => $unidades) {
            $velocidad = $this->vendido[$id] ?? 0;
            $minimo = $this->upp[$id] * 2;
            $nuevo = (int) ceil(max($velocidad * 1.5, $minimo));
            $this->stock[$id] = min($nuevo, 500);
            $this->vendido[$id] = 0;
        }
    }

    private function persistirStock(): void
    {
        foreach ($this->stock as $id => $unidades) {
            $upp = $this->upp[$id];
            DB::table('productos')->where('id', $id)->update([
                'stock_paquetes' => intdiv($unidades, $upp),
                'stock_unidades' => $unidades % $upp,
            ]);
        }
    }
}
