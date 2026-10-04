@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Track Shipment</h2>
    <form method="GET" action="{{ route('portal.track') }}" style="max-width: 500px;">
        <div class="form-group">
            <input type="text" name="tracking_number" placeholder="Enter tracking number (e.g., CP-AX91-4821)" required>
        </div>
        <button type="submit">Track</button>
    </form>
</div>

@if(isset($shipment) && $shipment)
<div class="card">
    <h2>Shipment Details</h2>
    <table>
        <tbody>
            <tr><td><strong>Tracking Number</strong></td><td>{{ $shipment->tracking_number }}</td></tr>
            <tr><td><strong>Customer</strong></td><td>{{ $shipment->customer_name }}</td></tr>
            <tr><td><strong>Origin</strong></td><td>{{ $shipment->origin }}</td></tr>
            <tr><td><strong>Destination</strong></td><td>{{ $shipment->destination }}</td></tr>
            <tr><td><strong>Shipped At</strong></td><td>{{ $shipment->shipped_at }}</td></tr>
        </tbody>
    </table>
</div>
@endif
@endsection