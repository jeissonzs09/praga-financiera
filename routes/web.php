<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RefinanciamientoController;


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

Route::get('prestamos/{id}/plan-original/pdf', [PagoController::class, 'pdfPlanOriginal'])->name('pagos.plan.original.pdf');

// Listado de contratos
Route::get('/contratos', [ContratoController::class, 'index'])->name('contratos.index');

// Generar PDF de contrato
Route::get('/contratos/{prestamo}/pdf', [ContratoController::class, 'generarPdf'])->name('contratos.pdf');

Route::get('contratos/{prestamo}/pagare', [ContratoController::class, 'generarPagare'])->name('contratos.pagare');
Route::get('/pago-financiero/{prestamo}', [ContratoController::class, 'mostrarPago'])->name('contratos.pago');
Route::get('contratos/{prestamo}/declaracion', [ContratoController::class, 'generarDeclaracion'])->name('contratos.declaracion');
Route::get('contratos/{prestamo}/autorizacion', [ContratoController::class, 'generarAutorizacion'])->name('contratos.autorizacion');

// Paso 2: recibir datos y mostrar distribución
Route::post('pagos/{prestamo}/distribuir', [PagoController::class, 'distribuir'])->name('pagos.distribuir');

// Paso final: guardar distribución
Route::post('pagos/{prestamo}/guardar', [PagoController::class, 'guardarDistribucion'])->name('pagos.guardar');
Route::get('recibos/{prestamo}', [ReciboController::class, 'index'])->name('recibos.index');
Route::get('recibos/prestamo/{prestamo}', [ReciboController::class, 'index'])->name('recibos.index');
Route::get('recibos/pdf/{id}', [ReciboController::class, 'pdf'])->name('recibos.pdf');

Route::get('reportes/pagos', [ReporteController::class, 'pagosForm'])->name('reportes.pagosForm');
Route::get('reportes/pagos/generar', [ReporteController::class, 'generarPagos'])->name('reportes.pagos');
Route::get('reportes/pagos/descargar', [ReporteController::class, 'descargarReporte'])->name('reportes.descargar');
Route::post('reportes/pagos/eliminar/{index}', [ReporteController::class, 'eliminarReporte'])->name('reportes.eliminar');
Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

Route::get('/reportes/export', [ReporteController::class, 'exportPagos'])->name('reportes.export');

Route::get('/reportes/excel', [ReporteController::class, 'exportarExcel'])->name('reportes.excel');
Route::get('/reportes/pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.pdf');
Route::post('/reportes/generar', [ReporteController::class, 'generarReporte'])->name('reportes.generar');
Route::get('/reportes/pagos/pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.pdf');

Route::post('/reportes/pagos/eliminar/{index}', [ReporteController::class, 'eliminarReporte'])->name('reportes.eliminar');
Route::get('/pagos/calendario/{clienteId}', [PagoController::class, 'getCalendarioPagos']);

Route::get('/calendario-pagos', [PagoController::class, 'getCalendarioPagos'])->name('calendario.pagos');

Route::prefix('refinanciamientos')->group(function () {
    Route::get('/', [RefinanciamientoController::class, 'index'])->name('refinanciamientos.index');
    Route::get('/{id}/create', [RefinanciamientoController::class, 'create'])->name('refinanciamientos.create');
    Route::post('/', [RefinanciamientoController::class, 'store'])->name('refinanciamientos.store');
});

Route::get('/refinanciamientos', [RefinanciamientoController::class, 'index'])
    ->name('refinanciamientos.index');

    Route::get('/clientes/{id}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('clientes.update');

Route::post('/contratos/{prestamo}/pdf-modal', [ContratoController::class, 'generarPdfModal'])->name('contratos.generarPdfModal');

Route::post('/contratos/{prestamo}/pagare-modal', [ContratoController::class, 'generarPagareModal'])
    ->name('contratos.generarPagareModal');

    // Declaración desde modal
Route::post('/contratos/{prestamo}/declaracion-modal', [ContratoController::class, 'generarDeclaracionModal'])
    ->name('contratos.generarDeclaracionModal');

    // Autorización desde modal
Route::post('/contratos/{prestamo}/autorizacion-modal', [ContratoController::class, 'generarAutorizacionModal'])
    ->name('contratos.generarAutorizacionModal');

    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    Route::post('/reportes/creditos', [ReporteController::class, 'generarCreditos'])
    ->name('reportes.generarCreditos');

    Route::get('/reportes/creditos/excel', [ReporteController::class, 'exportExcelCreditos'])->name('reportes.excelCreditos');
Route::get('/reportes/creditos/pdf', [ReporteController::class, 'generarReporteCreditosPDF'])->name('reportes.pdfCreditos');

Route::get('/prestamos/{id}/plan', [PagoController::class, 'mostrarPlanAjax'])->name('prestamos.plan.ajax');

Route::get('/prestamos/{id}/estado-cuenta', [PagoController::class, 'descargarEstadoCuentaPDF'])
     ->name('pagos.estado.cuenta.pdf');

     // Mostrar estado de cuenta
Route::get('/prestamos/{prestamo}/estado', [PagoController::class, 'verEstadoCuenta'])->name('pagos.verEstado');

// Descargar desde la misma vista
Route::get('/prestamos/{prestamo}/estado/descargar', [PagoController::class, 'descargarEstadoCuenta'])->name('pagos.descargarEstado');

Route::get('/prestamos/{id}/estado-cuenta', [PrestamoController::class, 'verEstadoCuentaPDF'])
    ->name('prestamos.verEstadoCuentaPDF');

Route::get('/pagos/{id}/estado-cuenta/pdf', [PagoController::class, 'descargarEstadoCuentaPDF'])
    ->name('pagos.estado.cuenta.pdf');

Route::get('pagos/{prestamo}/estado-cuenta', [PagoController::class, 'verEstadoCuentaPDF'])->name('pagos.estado.cuenta.pdf');

Route::get('/pagos/hoy', [PagoController::class, 'pagosHoy'])->name('pagos.hoy');

Route::get('/clientes/{id}/imprimir', [ClienteController::class, 'imprimir'])->name('clientes.imprimir');

Route::get('/prestamos/historial', [PrestamoController::class, 'historial'])->name('prestamos.historial');

Route::patch('/prestamos/{id}/inactivar', [PrestamoController::class, 'inactivar'])->name('prestamos.inactivar');

Route::get('/prestamos/historial', [PrestamoController::class, 'historial'])->name('prestamos.historial');

Route::patch('/prestamos/{id}/activar', [PrestamoController::class, 'activar'])->name('prestamos.activar');

Route::post('contratos/{prestamo}/resumen-operacion', [ContratoController::class, 'generarResumenOperacionModal'])
    ->name('contratos.generarResumenOperacionModal');

    Route::post('/contratos/{prestamo}/resumen-operacion', [ContratoController::class, 'generarResumenOperacionModal'])->name('contratos.generarResumenOperacionModal');

    
// ✅ Incluye rutas adicionales generadas por Breeze/Fortify/etc.
//require __DIR__.'/auth.php';