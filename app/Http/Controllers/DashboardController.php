<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();

        $ventasHoy = Factura::whereDate('fecha_venta', $hoy)->count();
        $totalHoyBs = Factura::whereDate('fecha_venta', $hoy)->sum('total_bs');
        $totalHoyUsd = Factura::whereDate('fecha_venta', $hoy)->sum('total_usd');

        $ventasMes = Factura::whereDate('fecha_venta', '>=', $inicioMes)->count();
        $totalMesBs = Factura::whereDate('fecha_venta', '>=', $inicioMes)->sum('total_bs');
        $totalMesUsd = Factura::whereDate('fecha_venta', '>=', $inicioMes)->sum('total_usd');

        $creditosPendientes = Factura::where('estado', 'credito')
            ->where('estado_credito', 'pendiente')
            ->count();

        $totalCreditosPendientesBs = Factura::where('estado', 'credito')
            ->where('estado_credito', 'pendiente')
            ->sum('total_bs');

        $productosStockBajo = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->get()
            ->filter(fn ($p) => $p->stock_total <= 10)
            ->take(10);

        $totalProductos = Producto::whereNull('deleted_at')->count();
        $totalClientes = Cliente::count();

        $masVendidos = DB::table('items_factura')
            ->join('productos', 'items_factura.producto_id', '=', 'productos.id')
            ->select('productos.nombre', DB::raw('SUM(items_factura.cantidad) as total'))
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'ventasHoy', 'totalHoyBs', 'totalHoyUsd',
            'ventasMes', 'totalMesBs', 'totalMesUsd',
            'creditosPendientes', 'totalCreditosPendientesBs',
            'productosStockBajo', 'totalProductos', 'totalClientes',
            'masVendidos'
        ));
    }
}
