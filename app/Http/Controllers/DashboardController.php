<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pago;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        \Carbon\Carbon::setLocale('es');
        // 1) Conteo de clientes
        $clientes_count = Cliente::count();

        // 2) Total de pagos del día (se “reinicia” solo porque filtra por la fecha actual)
        $pagos_dia = Pago::whereDate('created_at', Carbon::today())->sum('monto');

        // 3) Pagos por mes (del año actual) para la gráfica
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $mensual = Pago::selectRaw('MONTH(created_at) as mes, SUM(monto) as total')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Etiquetas y datos para Chart.js
        $pagos_mes_labels = $mensual->pluck('mes')->map(fn($m) => $meses[$m])->toArray();
        $pagos_mes_data   = $mensual->pluck('total')->map(fn($t) => round($t, 2))->toArray();

        return view('dashboard', compact(
            'clientes_count',
            'pagos_dia',
            'pagos_mes_labels',
            'pagos_mes_data'
        ));
    }
}