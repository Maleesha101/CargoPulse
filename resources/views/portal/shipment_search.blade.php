@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Shipment Search</h2>
    <form method="GET" action="{{ route('portal.shipments.search') }}">
        <div class="form-group">
            <label for="tracking">Tracking Number</label>
            <input type="text" name="tracking" id="tracking" placeholder="Enter tracking number (e.g., CP-AX91-4821)" required>
        </div>
        <button type="submit">Search Shipment</button>
    </form>
</div>

@if(isset($query))
    <div class="card" style="margin-top: 1rem;">
        <h3>Search Results for: "{{ $query }}"</h3>
        @if(isset($results) && count($results) > 0)
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Tracking Number</th>
                            <th>Customer</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Shipped At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->customer_name }}</td>
                                <td>{{ $shipment->origin }}</td>
                                <td>{{ $shipment->destination }}</td>
                                <td>{{ $shipment->shipped_at }}</td>
                                <td>{{ $shipment->is_restricted ? 'RESTRICTED' : 'Standard' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: #95a5a6;">No shipment found.</p>
        @endif
    </div>
@endif
@endsection