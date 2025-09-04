@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Recibo #{{ $recibo->id_recibo }}</h4>
    <p><strong>Fecha:</strong> {{ $recibo->created_at->format('d/m/Y H:i') }}</p>
    <p><strong>Monto total:</strong> L. {{ number_format($recibo->monto_total, 2) }}</p>
    <p><strong>Observaciones:</strong> {{ $recibo->observaciones }}</p>

    <h5>Detalle de cuotas pagadas</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Cuota</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Recargo</th>
                <th>Mora</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recibo->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cuota_numero }}</td>
                    <td>L. {{ number_format($detalle->capital, 2) }}</td>
                    <td>L. {{ number_format($detalle->interes, 2) }}</td>
                    <td>L. {{ number_format($detalle->recargo, 2) }}</td>
                    <td>L. {{ number_format($detalle->mora, 2) }}</td>
                    <td>L. {{ number_format($detalle->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection