<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\ItemFactura;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    private const METODOS = [
        'efectivo' => 'Efectivo',
        'punto' => 'Punto de Venta',
        'biopago' => 'Biopago',
        'divisas' => 'Divisas',
        'pago_movil' => 'Pago Móvil',
        'transferencia' => 'Transferencia',
        'mixto' => 'Mixto',
    ];

    // ===== REPORTE DE VENTAS =====

    public function facturas(Request $request)
    {
        [$desde, $hasta, $desdePrev, $hastaPrev] = $this->rangoPeriodo($request);

        $facturas = $this->queryFacturas($request, $desde, $hasta)
            ->orderBy('fecha_venta', 'desc')
            ->get();

        $kpis = $this->conVariacion(
            $this->kpis($request, $desde, $hasta),
            $this->kpis($request, $desdePrev, $hastaPrev)
        );

        $desglose = $this->desgloseMetodo(
            $this->queryFacturas($request, $desde, $hasta, false)->get()
        );

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('reportes.export.facturas-pdf', compact('facturas', 'kpis', 'desglose', 'desde', 'hasta'));
            return $pdf->setPaper('letter', 'portrait')
                ->download('reporte_facturas_' . date('Ymd') . '.pdf');
        }

        if ($request->get('export') === 'csv') {
            return $this->facturasCsv($facturas);
        }

        $vendedores = User::orderBy('name')->get();
        $clientes = Cliente::orderBy('nombre')->get();
        $nombresMetodo = self::METODOS;

        return view('reportes.facturas', compact('facturas', 'kpis', 'desglose', 'desde', 'hasta', 'vendedores', 'clientes', 'nombresMetodo'));
    }

    // ===== ESTADÍSTICAS =====

    public function estadisticas(Request $request)
    {
        [$desde, $hasta, $desdePrev, $hastaPrev] = $this->rangoPeriodo($request);

        $kpis = $this->conVariacion(
            $this->kpis($request, $desde, $hasta),
            $this->kpis($request, $desdePrev, $hastaPrev)
        );

        $facturas = Factura::with('cliente')
            ->where('estado', '!=', 'anulada')
            ->whereDate('fecha_venta', '>=', $desde)
            ->whereDate('fecha_venta', '<=', $hasta)
            ->get();

        $porDia = $this->ventasPorDia($facturas, $desde, $hasta);
        $porMetodo = $this->desgloseMetodo($facturas);

        [$semanaLabels, $detalSeries, $mayorSeries, $detalResumen, $mayorResumen] = $this->detalVsMayor($desde, $hasta);
        $topProductos = $this->topProductos($desde, $hasta);
        $porVendedor = $this->ventasPorVendedor($desde, $hasta);
        $topClientes = $this->topClientes($facturas);

        $creditos = Factura::where('estado', 'credito')
            ->where('estado_credito', 'pendiente')
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total_bs), 0) as total_bs')
            ->first();

        $nombresMetodo = self::METODOS;

        return view('reportes.estadisticas', compact(
            'kpis', 'desde', 'hasta', 'porDia', 'porMetodo',
            'semanaLabels', 'detalSeries', 'mayorSeries', 'detalResumen', 'mayorResumen',
            'topProductos', 'porVendedor', 'topClientes', 'creditos', 'nombresMetodo'
        ));
    }

    // ===== RENTABILIDAD =====

    public function rentabilidad(Request $request)
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());
        $tipoVenta = $request->get('tipo_venta');

        $query = ItemFactura::whereHas('factura', fn ($q) => $q
            ->where('estado', '!=', 'anulada')
            ->whereDate('fecha_venta', '>=', $desde)
            ->whereDate('fecha_venta', '<=', $hasta))
            ->when($tipoVenta, fn ($q) => $q->where('items_factura.tipo_venta', $tipoVenta))
            ->join('productos', 'items_factura.producto_id', '=', 'productos.id')
            ->select(
                'productos.id as producto_id',
                'productos.nombre',
                'productos.costo_usd',
                'productos.fuente_tasa',
                DB::raw('SUM(items_factura.cantidad) as unidades'),
                DB::raw('SUM(items_factura.subtotal) as ingreso_bs'),
                DB::raw('SUM(items_factura.precio_unitario_usd * items_factura.cantidad) as ingreso_usd')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.costo_usd', 'productos.fuente_tasa')
            ->get();

        $tasas = TasaCambio::query()->latest('fecha')->get()->keyBy('tipo');

        $filas = $query->map(function ($r) use ($tasas) {
            $tasa = isset($tasas[$r->fuente_tasa]) ? (float) $tasas[$r->fuente_tasa]->monto : 1;
            $unidades = (float) $r->unidades;
            $ingresoBs = (float) $r->ingreso_bs;
            $costoBs = round((float) $r->costo_usd * $unidades * $tasa, 2);
            $gananciaBs = round($ingresoBs - $costoBs, 2);
            $margen = $ingresoBs > 0 ? round(($gananciaBs / $ingresoBs) * 100, 1) : 0;

            return (object) [
                'producto_id' => $r->producto_id,
                'nombre' => $r->nombre,
                'unidades' => $unidades,
                'ingreso_bs' => round($ingresoBs, 2),
                'ingreso_usd' => round((float) $r->ingreso_usd, 2),
                'costo_bs' => $costoBs,
                'ganancia_bs' => $gananciaBs,
                'margen' => $margen,
            ];
        })->sortByDesc('ganancia_bs')->values();

        $topGanancia = $filas->take(10);

        if ($request->get('export') === 'csv') {
            $csv = "\xEF\xBB\xBF";
            $csv .= "Producto;Unidades;Ingreso Bs;Ingreso USD;Costo est. Bs;Ganancia est. Bs;Margen %\n";
            foreach ($filas as $f) {
                $csv .= implode(';', [
                    $f->nombre,
                    $f->unidades,
                    number_format($f->ingreso_bs, 2, ',', ''),
                    number_format($f->ingreso_usd, 2, ',', ''),
                    number_format($f->costo_bs, 2, ',', ''),
                    number_format($f->ganancia_bs, 2, ',', ''),
                    number_format($f->margen, 1, ',', ''),
                ]) . "\n";
            }

            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, 'rentabilidad_' . date('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reportes.rentabilidad', compact('filas', 'topGanancia', 'desde', 'hasta', 'tipoVenta'));
    }

    // ===== BALANCE MENSUAL =====

    public function balance(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);

        $facturas = Factura::where('estado', '!=', 'anulada')
            ->whereYear('fecha_venta', $anio)
            ->get(['fecha_venta', 'total_bs', 'total_usd']);

        $mensual = [];
        foreach ($facturas as $f) {
            $mes = (int) $f->fecha_venta->format('n');
            if (!isset($mensual[$mes])) {
                $mensual[$mes] = ['cantidad' => 0, 'total_bs' => 0.0, 'total_usd' => 0.0];
            }
            $mensual[$mes]['cantidad']++;
            $mensual[$mes]['total_bs'] += (float) $f->total_bs;
            $mensual[$mes]['total_usd'] += (float) $f->total_usd;
        }
        ksort($mensual);

        foreach ($mensual as &$m) {
            $m['total_bs'] = round($m['total_bs'], 2);
            $m['total_usd'] = round($m['total_usd'], 2);
        }

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $serieBs = array_map(fn ($n) => $mensual[$n]['total_bs'] ?? 0, range(1, 12));

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('reportes.export.balance-pdf', compact('mensual', 'meses', 'anio'));
            return $pdf->setPaper('letter', 'portrait')
                ->download('balance_mensual_' . $anio . '.pdf');
        }

        return view('reportes.balance', compact('mensual', 'meses', 'anio', 'serieBs'));
    }

    // ===== STOCK BAJO =====

    public function stock()
    {
        $productos = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->get()
            ->filter(fn ($p) => $p->stock_total <= 10)
            ->sortBy('stock_total');

        return view('reportes.stock', compact('productos'));
    }

    // ===== HELPERS =====

    private function rangoPeriodo(Request $request): array
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        if (strtotime($desde) > strtotime($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $dias = Carbon::parse($desde)->diffInDays(Carbon::parse($hasta)) + 1;
        $desdePrev = Carbon::parse($desde)->subDays($dias)->toDateString();
        $hastaPrev = Carbon::parse($desde)->subDay()->toDateString();

        return [$desde, $hasta, $desdePrev, $hastaPrev];
    }

    private function queryFacturas(Request $request, string $desde, string $hasta, bool $conMetodo = true)
    {
        return Factura::with('cliente', 'user')
            ->where('estado', '!=', 'anulada')
            ->whereDate('fecha_venta', '>=', $desde)
            ->whereDate('fecha_venta', '<=', $hasta)
            ->when($conMetodo && $request->filled('metodo_pago'), fn ($q) => $q->where('metodo_pago', $request->metodo_pago))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('cliente_id'), fn ($q) => $q->where('cliente_id', $request->cliente_id));
    }

    private function kpis(Request $request, string $desde, string $hasta): array
    {
        $row = $this->queryFacturas($request, $desde, $hasta)
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total_bs), 0) as total_bs, COALESCE(SUM(total_usd), 0) as total_usd, COALESCE(SUM(iva_bs), 0) as iva_bs')
            ->first();

        $cantidad = (float) $row->cantidad;
        $totalBs = (float) $row->total_bs;

        return [
            'cantidad' => $cantidad,
            'total_bs' => round($totalBs, 2),
            'total_usd' => round((float) $row->total_usd, 2),
            'iva_bs' => round((float) $row->iva_bs, 2),
            'ticket_promedio' => $cantidad > 0 ? round($totalBs / $cantidad, 2) : 0,
        ];
    }

    private function conVariacion(array $actual, array $anterior): array
    {
        $resultado = [];
        foreach ($actual as $clave => $valor) {
            $previo = $anterior[$clave] ?? 0;
            $variacion = $previo > 0 ? (($valor - $previo) / $previo) * 100 : 0;
            $resultado[$clave] = ['valor' => $valor, 'variacion' => round($variacion, 1)];
        }

        return $resultado;
    }

    private function desgloseMetodo(Collection $facturas): array
    {
        $desglose = array_fill_keys(array_keys(self::METODOS), 0.0);

        foreach ($facturas as $f) {
            if ($f->metodo_pago === 'mixto') {
                foreach ($f->detalle_pago ?? [] as $pago) {
                    if (isset($desglose[$pago['metodo']])) {
                        $desglose[$pago['metodo']] += (float) $pago['monto'];
                    }
                }
            } elseif (isset($desglose[$f->metodo_pago])) {
                $desglose[$f->metodo_pago] += (float) $f->total_bs;
            }
        }

        foreach ($desglose as &$monto) {
            $monto = round($monto, 2);
        }

        return $desglose;
    }

    private function ventasPorDia(Collection $facturas, string $desde, string $hasta): array
    {
        $porDia = [];
        $cursor = Carbon::parse($desde);
        $fin = Carbon::parse($hasta);
        while ($cursor->lte($fin)) {
            $porDia[$cursor->format('Y-m-d')] = 0.0;
            $cursor->addDay();
        }

        foreach ($facturas as $f) {
            $clave = $f->fecha_venta?->format('Y-m-d');
            if ($clave && isset($porDia[$clave])) {
                $porDia[$clave] += (float) $f->total_bs;
            }
        }

        foreach ($porDia as &$valor) {
            $valor = round($valor, 2);
        }

        return $porDia;
    }

    private function detalVsMayor(string $desde, string $hasta): array
    {
        $items = ItemFactura::with('factura')
            ->whereHas('factura', fn ($q) => $q
                ->where('estado', '!=', 'anulada')
                ->whereDate('fecha_venta', '>=', $desde)
                ->whereDate('fecha_venta', '<=', $hasta))
            ->get(['factura_id', 'cantidad', 'tipo_venta', 'subtotal']);

        $detal = [];
        $mayor = [];
        $unidadesDetal = 0;
        $unidadesMayor = 0;

        foreach ($items as $item) {
            $inicioSemana = $item->factura?->fecha_venta?->startOfWeek()->format('Y-m-d') ?? '';
            if ($item->tipo_venta === 'mayor') {
                $mayor[$inicioSemana] = ($mayor[$inicioSemana] ?? 0) + (float) $item->subtotal;
                $unidadesMayor += (int) $item->cantidad;
            } else {
                $detal[$inicioSemana] = ($detal[$inicioSemana] ?? 0) + (float) $item->subtotal;
                $unidadesDetal += (int) $item->cantidad;
            }
        }

        ksort($detal);
        ksort($mayor);

        $semanas = array_unique(array_merge(array_keys($detal), array_keys($mayor)));
        sort($semanas);

        $semanaLabels = array_map(fn ($s) => 'Sem ' . Carbon::parse($s)->format('d/m'), $semanas);
        $detalSeries = array_map(fn ($s) => round($detal[$s] ?? 0, 2), $semanas);
        $mayorSeries = array_map(fn ($s) => round($mayor[$s] ?? 0, 2), $semanas);

        return [
            $semanaLabels,
            $detalSeries,
            $mayorSeries,
            ['bs' => round(array_sum($detal), 2), 'unidades' => $unidadesDetal],
            ['bs' => round(array_sum($mayor), 2), 'unidades' => $unidadesMayor],
        ];
    }

    private function topProductos(string $desde, string $hasta): Collection
    {
        return ItemFactura::whereHas('factura', fn ($q) => $q
            ->where('estado', '!=', 'anulada')
            ->whereDate('fecha_venta', '>=', $desde)
            ->whereDate('fecha_venta', '<=', $hasta))
            ->join('productos', 'items_factura.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                DB::raw('SUM(items_factura.cantidad) as unidades'),
                DB::raw('SUM(items_factura.subtotal) as ingreso_bs')
            )
            ->groupBy('productos.nombre')
            ->orderByDesc('ingreso_bs')
            ->take(10)
            ->get();
    }

    private function ventasPorVendedor(string $desde, string $hasta): Collection
    {
        return Factura::with('user')
            ->where('estado', '!=', 'anulada')
            ->whereDate('fecha_venta', '>=', $desde)
            ->whereDate('fecha_venta', '<=', $hasta)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($grupo) => [
                'nombre' => $grupo->first()->user?->name ?? '—',
                'facturas' => $grupo->count(),
                'total_bs' => round($grupo->sum('total_bs'), 2),
            ])
            ->sortByDesc('total_bs')
            ->values();
    }

    private function topClientes(Collection $facturas): Collection
    {
        return $facturas->whereNotNull('cliente_id')
            ->groupBy('cliente_id')
            ->map(fn ($grupo) => [
                'nombre' => $grupo->first()->cliente?->nombre ?? '—',
                'facturas' => $grupo->count(),
                'total_bs' => round($grupo->sum('total_bs'), 2),
            ])
            ->sortByDesc('total_bs')
            ->take(5)
            ->values();
    }

    private function facturasCsv(Collection $facturas)
    {
        $csv = "\xEF\xBB\xBF";
        $csv .= "Correlativo;Cliente;Metodo;Estado;Fecha;Subtotal Bs;IVA Bs;Total Bs;Total USD\n";

        foreach ($facturas as $f) {
            $csv .= implode(';', [
                $f->correlativo,
                $f->cliente->nombre ?? 'Contado',
                self::METODOS[$f->metodo_pago] ?? $f->metodo_pago,
                $f->estado,
                $f->fecha_venta?->format('Y-m-d') ?? $f->fecha_venta,
                number_format($f->subtotal_bs, 2, ',', ''),
                number_format($f->iva_bs, 2, ',', ''),
                number_format($f->total_bs, 2, ',', ''),
                number_format($f->total_usd, 2, ',', ''),
            ]) . "\n";
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'reporte_facturas_' . date('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
