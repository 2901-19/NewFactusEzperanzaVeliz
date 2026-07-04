<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\TasaCambioController;
use App\Http\Controllers\FacturaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
