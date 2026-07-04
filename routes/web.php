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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('productos', ProductoController::class)->except('show');
    Route::post('productos/{id}/restore', [ProductoController::class, 'restore'])->name('productos.restore');
    Route::resource('clientes', ClienteController::class)->except('show');
    Route::resource('impuestos', ImpuestoController::class)->except('show')->middleware('rol:admin');
    Route::get('/tasas-cambio', [TasaCambioController::class, 'index'])->name('tasas-cambio.index')->middleware('rol:admin');
    Route::post('/tasas-cambio/actualizar', [TasaCambioController::class, 'actualizar'])->name('tasas-cambio.actualizar')->middleware('rol:admin');
    Route::resource('categorias', CategoriaController::class)->except('show')->middleware('rol:admin');

    Route::get('/pos', [FacturaController::class, 'pos'])->name('facturas.pos');
    Route::post('/facturas', [FacturaController::class, 'store'])->name('facturas.store');
    Route::get('/facturas', [FacturaController::class, 'index'])->name('facturas.index');
    Route::get('/creditos', [FacturaController::class, 'creditos'])->name('facturas.creditos');
    Route::get('/facturas/{factura}', [FacturaController::class, 'show'])->name('facturas.show');
    Route::post('/facturas/{factura}/pagar-credito', [FacturaController::class, 'pagarCredito'])->name('facturas.pagar-credito');
    Route::post('/facturas/{factura}/anular', [FacturaController::class, 'anular'])->name('facturas.anular');

    Route::middleware('rol:admin')->group(function () {
        Route::get('/reportes/facturas', [ReporteController::class, 'facturas'])->name('reportes.facturas');
        Route::get('/reportes/balance', [ReporteController::class, 'balance'])->name('reportes.balance');
        Route::get('/reportes/stock', [ReporteController::class, 'stock'])->name('reportes.stock');

        Route::get('/herramientas/datos', [HerramientasController::class, 'datos'])->name('herramientas.datos');
        Route::get('/herramientas/exportar', [HerramientasController::class, 'exportar'])->name('herramientas.exportar');
        Route::post('/herramientas/importar', [HerramientasController::class, 'importar'])->name('herramientas.importar');

        Route::get('/herramientas/impresora', [HerramientasController::class, 'imprimirConfig'])->name('herramientas.impresora');
        Route::post('/herramientas/impresora', [HerramientasController::class, 'imprimirGuardar'])->name('herramientas.impresora.guardar');
        Route::post('/herramientas/impresora/test', [HerramientasController::class, 'imprimirTest'])->name('herramientas.impresora.test');
        Route::get('/herramientas/imprimir-factura/{factura}', [HerramientasController::class, 'imprimirFactura'])->name('herramientas.imprimir-factura');

        Route::get('/herramientas/precios', [HerramientasController::class, 'precios'])->name('herramientas.precios');
        Route::get('/herramientas/precios/pdf', [HerramientasController::class, 'preciosPdf'])->name('herramientas.precios.pdf');

        Route::get('/herramientas/configuracion', [HerramientasController::class, 'configuracion'])->name('herramientas.configuracion');
        Route::post('/herramientas/configuracion', [HerramientasController::class, 'configuracionGuardar'])->name('herramientas.configuracion.guardar');
    });
});

require __DIR__.'/auth.php';
