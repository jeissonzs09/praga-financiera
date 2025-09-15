<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReciboController;


Route::get('/', function () {
    return view('welcome');
});

// ✅ Ruta única para dashboard, sin controlador inexistente
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// ✅ Grupo de rutas protegidas para perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ✅ Rutas de login y logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
});

Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');


Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
Route::get('/prestamos/create', [PrestamoController::class, 'create'])->name('prestamos.create');
Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');

Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
Route::get('/pagos/{prestamo}/plan', [PagoController::class, 'plan'])
    ->name('pagos.plan');

Route::get('/pagos/{prestamo}/crear', [PagoController::class, 'create'])->name('pagos.create');
Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');

Route::get('pagos/{prestamo}/crear', [\App\Http\Controllers\PagoController::class, 'createPago'])
    ->name('pagos.create');

Route::post('pagos/{prestamo}', [\App\Http\Controllers\PagoController::class, 'storePago'])
    ->name('pagos.store');

    Route::get('pagos/{prestamo}/historial', [\App\Http\Controllers\PagoController::class, 'historial'])
    ->name('pagos.historial');

    Route::get('pagos/{prestamo}/plan-original', [PagoController::class, 'planOriginal'])
    ->name('pagos.plan.original');

Route::get('pagos/{pago}/recibo', [PagoController::class, 'recibo'])->name('pagos.recibo');


Route::get('/eventos-pagos', [DashboardController::class, 'eventosPagos'])
    ->middleware(['auth']);

    Route::post('/simular-plan', [PagoController::class, 'simularPlan'])
    ->name('prestamos.simular');

    Route::delete('/prestamos/{prestamo}', [PrestamoController::class, 'destroy'])
    ->name('prestamos.destroy');

    Route::get('/prestamos/{prestamo}/recibos', [ReciboController::class, 'index'])->name('recibos.index');
Route::get('/recibos/{recibo}', [ReciboController::class, 'show'])->name('recibos.show');

Route::get('/recibos/{recibo}/pdf', [ReciboController::class, 'pdf'])->name('recibos.pdf');

// web.php
Route::get('/prestamos/{prestamo}/pagos', [PagoController::class, 'listarPagos'])->name('pagos.listar');
Route::delete('/pagos/{pago}', [PagoController::class, 'eliminarPago'])->name('pagos.eliminar');
Route::delete('/recibos/{recibo}', [PagoController::class, 'eliminarRecibo'])->name('pagos.eliminarRecibo');



// ✅ Incluye rutas adicionales generadas por Breeze/Fortify/etc.
//require __DIR__.'/auth.php';

