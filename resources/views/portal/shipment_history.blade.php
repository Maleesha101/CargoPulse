@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Shipment History</h2>
    @if(isset($shipments) && count($shipments) > 0)
    <table>
        <thead>
            <tr>
                <th>Tracking Number</th>
                <th>Customer</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Shipped</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shipments as $shipment)
            <tr>
                <td>{{ $shipment->tracking_number }}</td>
                <td>{{ $shipment->customer_name }}</td>
                <td>{{ $shipment->origin }}</td>
                <td>{{ $shipment->destination }}</td>
                <td>{{ $shipment->shipped_at }}</td>
                <td>{{ $shipment->is_restricted ? 'Restricted' : 'Standard' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No shipments found.</p>
    @endif
</div>
@endsection