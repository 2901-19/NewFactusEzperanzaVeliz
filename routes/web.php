<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\TasaCambioController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\HerramientasController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'permiso:ver-dashboard'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('productos', ProductoController::class)->except('show')->middleware('permiso:gestionar-productos');
    Route::post('productos/{id}/restore', [ProductoController::class, 'restore'])->name('productos.restore')->middleware('permiso:gestionar-productos');
    Route::resource('clientes', ClienteController::class)->except('show')->middleware('permiso:gestionar-clientes');
    Route::post('clientes/rapido', [ClienteController::class, 'storeRapido'])->name('clientes.rapido')->middleware('permiso:gestionar-clientes');
    Route::resource('impuestos', ImpuestoController::class)->except('show')->middleware('permiso:gestionar-impuestos');
    Route::get('/tasas-cambio', [TasaCambioController::class, 'index'])->name('tasas-cambio.index')->middleware('permiso:gestionar-tasas');
    Route::post('/tasas-cambio/actualizar', [TasaCambioController::class, 'actualizar'])->name('tasas-cambio.actualizar')->middleware('permiso:gestionar-tasas');
    Route::resource('categorias', CategoriaController::class)->except('show')->middleware('permiso:gestionar-categorias');

    Route::get('/pos', [FacturaController::class, 'pos'])->name('facturas.pos')->middleware('permiso:usar-pos');
    Route::post('/facturas', [FacturaController::class, 'store'])->name('facturas.store')->middleware('permiso:crear-facturas');
    Route::get('/facturas', [FacturaController::class, 'index'])->name('facturas.index')->middleware('permiso:ver-facturas');
    Route::get('/creditos', [FacturaController::class, 'creditos'])->name('facturas.creditos')->middleware('permiso:gestionar-creditos');
    Route::get('/facturas/{factura}', [FacturaController::class, 'show'])->name('facturas.show')->middleware('permiso:ver-facturas');
    Route::post('/facturas/{factura}/pagar-credito', [FacturaController::class, 'pagarCredito'])->name('facturas.pagar-credito')->middleware('permiso:gestionar-creditos');
    Route::post('/facturas/{factura}/anular', [FacturaController::class, 'anular'])->name('facturas.anular')->middleware('permiso:anular-facturas');

    Route::get('/reportes/facturas', [ReporteController::class, 'facturas'])->name('reportes.facturas')->middleware('permiso:ver-reporte-facturas');
    Route::get('/reportes/balance', [ReporteController::class, 'balance'])->name('reportes.balance')->middleware('permiso:ver-balance');
    Route::get('/reportes/stock', [ReporteController::class, 'stock'])->name('reportes.stock')->middleware('permiso:ver-stock-bajo');

    Route::get('/herramientas/datos', [HerramientasController::class, 'datos'])->name('herramientas.datos')->middleware('permiso:exportar-datos');
    Route::get('/herramientas/exportar', [HerramientasController::class, 'exportar'])->name('herramientas.exportar')->middleware('permiso:exportar-datos');
    Route::post('/herramientas/importar', [HerramientasController::class, 'importar'])->name('herramientas.importar')->middleware('permiso:importar-datos');

    Route::get('/herramientas/impresora', [HerramientasController::class, 'imprimirConfig'])->name('herramientas.impresora')->middleware('permiso:configurar-impresora');
    Route::post('/herramientas/impresora', [HerramientasController::class, 'imprimirGuardar'])->name('herramientas.impresora.guardar')->middleware('permiso:configurar-impresora');
    Route::post('/herramientas/impresora/test', [HerramientasController::class, 'imprimirTest'])->name('herramientas.impresora.test')->middleware('permiso:configurar-impresora');
    Route::get('/herramientas/imprimir-factura/{factura}', [HerramientasController::class, 'imprimirFactura'])->name('herramientas.imprimir-factura')->middleware('permiso:configurar-impresora');

    Route::get('/herramientas/precios', [HerramientasController::class, 'precios'])->name('herramientas.precios')->middleware('permiso:ver-lista-precios');
    Route::get('/herramientas/precios/pdf', [HerramientasController::class, 'preciosPdf'])->name('herramientas.precios.pdf')->middleware('permiso:ver-lista-precios');

    Route::get('/herramientas/configuracion', [HerramientasController::class, 'configuracion'])->name('herramientas.configuracion')->middleware('permiso:configuracion');
    Route::post('/herramientas/configuracion', [HerramientasController::class, 'configuracionGuardar'])->name('herramientas.configuracion.guardar')->middleware('permiso:configuracion');

    Route::resource('usuarios', UserController::class)->except('show')->middleware('permiso:gestionar-usuarios');
});

require __DIR__.'/auth.php';
