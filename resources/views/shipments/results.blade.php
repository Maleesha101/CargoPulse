@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Shipment Lookup</h2>
    <form method="GET" action="{{ route('portal.shipments.search') }}" style="max-width: 500px; margin-bottom: 1rem;">
        <div class="form-group">
            <input type="text" name="tracking" placeholder="Tracking number" value="{{ $query ?? '' }}" style="width: 75%; display: inline-block; vertical-align: middle;">
            <button type="submit" style="width: 23%; padding: 0 0.5rem; vertical-align: middle;">Lookup</button>
        </div>
    </form>
</div>

@if(isset($results) && count($results) > 0)
<div class="card">
    <h2>Search Results</h2>
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
            @foreach($results as $shipment)
            <tr>
                <td>{{ $shipment->tracking_number ?? '' }}</td>
                <td>{{ $shipment->customer_name ?? '' }}</td>
                <td>{{ $shipment->origin ?? '' }}</td>
                <td>{{ $shipment->destination ?? '' }}</td>
                <td>{{ $shipment->shipped_at ?? '' }}</td>
                <td>{{ $shipment->is_restricted ? 'RESTRICTED' : 'Standard' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@elseif(isset($query) && $query !== '')
<div class="card">
    <p style="color: #95a5a6;">No shipment found.</p>
</div>
@else
<div class="card">
    <p style="color: #95a5a6;">Enter a tracking number to look up shipment details.</p>
</div>
@endif
@endsection