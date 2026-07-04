<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\TasaCambioController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReporteController;
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
    Route::resource('impuestos', ImpuestoController::class)->except('show');
    Route::resource('tasas-cambio', TasaCambioController::class)->except('show');

    Route::get('/pos', [FacturaController::class, 'pos'])->name('facturas.pos');
    Route::post('/facturas', [FacturaController::class, 'store'])->name('facturas.store');
    Route::get('/creditos', [FacturaController::class, 'creditos'])->name('facturas.creditos');
    Route::get('/facturas/{factura}', [FacturaController::class, 'show'])->name('facturas.show');
    Route::post('/facturas/{factura}/pagar-credito', [FacturaController::class, 'pagarCredito'])->name('facturas.pagar-credito');

    Route::get('/reportes/facturas', [ReporteController::class, 'facturas'])->name('reportes.facturas');
    Route::get('/reportes/balance', [ReporteController::class, 'balance'])->name('reportes.balance');
    Route::get('/reportes/stock', [ReporteController::class, 'stock'])->name('reportes.stock');
});

require __DIR__.'/auth.php';
