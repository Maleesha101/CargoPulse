@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Shipment Investigation</h2>
    <form method="GET" action="{{ route('ops.investigation', ['tracking_number' => '']) }}" style="max-width: 500px; margin-bottom: 1rem;">
        <div class="form-group">
            <input type="text" name="tracking_number" placeholder="Tracking number" value="{{ $tracking_number ?? '' }}" style="width: 75%; display: inline-block; vertical-align: middle;" required>
            <button type="submit" style="width: 23%; padding: 0 0.5rem; vertical-align: middle;">Investigate</button>
        </div>
    </form>
</div>

@if(isset($error))
<div class="card" style="border-left: 4px solid #e74c3c;">
    <p style="color: #e74c3c;">{{ $error }}</p>
</div>
@elseif(isset($shipment) && $shipment)
<div class="card">
    <h3>{{ $shipment->tracking_number }}</h3>
    <table>
        <tbody>
            <tr><td><strong>Customer</strong></td><td>{{ $shipment->customer_name }}</td></tr>
            <tr><td><strong>Origin</strong></td><td>{{ $shipment->origin }}</td></tr>
            <tr><td><strong>Destination</strong></td><td>{{ $shipment->destination }}</td></tr>
            <tr><td><strong>Shipped</strong></td><td>{{ $shipment->shipped_at }}</td></tr>
            <tr><td><strong>Restricted</strong></td><td>{{ $shipment->is_restricted ? 'Yes' : 'No' }}</td></tr>
        </tbody>
    </table>
</div>
@else
<div class="card">
    <p style="color: #95a5a6;">Enter a tracking number to investigate a shipment.</p>
</div>
@endif
@endsection