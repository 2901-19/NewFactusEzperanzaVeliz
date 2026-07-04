<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function facturas(Request $request)
    {
        $query = Factura::with('cliente')->orderBy('fecha_venta', 'desc');

        if ($request->filled('desde')) {
            $query->whereDate('fecha_venta', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->hasta);
        }

        $facturas = $query->get();
        $totalBs = $facturas->sum('total_bs');
        $totalUsd = $facturas->sum('total_usd');

        return view('reportes.facturas', compact('facturas', 'totalBs', 'totalUsd'));
    }

    public function balance(Request $request)
    {
        $anio = $request->get('anio', now()->year);

        $mensual = Factura::select(
            DB::raw('EXTRACT(MONTH FROM fecha_venta) as mes'),
            DB::raw('SUM(total_bs) as total_bs'),
            DB::raw('SUM(total_usd) as total_usd'),
            DB::raw('COUNT(*) as cantidad')
        )
            ->whereYear('fecha_venta', $anio)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return view('reportes.balance', compact('mensual', 'meses', 'anio'));
    }

    public function stock()
    {
        $productos = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->get()
            ->filter(fn ($p) => $p->stock_total <= 10)
            ->sortBy('stock_total');

        return view('reportes.stock', compact('productos'));
    }
}
