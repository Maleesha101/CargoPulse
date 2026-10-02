@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Dashboard</h2>
    <p>Welcome, {{ Session::get('user.name') }}!</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <a href="{{ route('portal.track') }}" style="background: #3498db; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Track Shipment</h3>
        </a>
        <a href="{{ route('portal.history') }}" style="background: #7f8c8d; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Shipment History</h3>
        </a>
        <a href="{{ route('portal.search.form') }}" style="background: #9b59b6; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Shipment Search</h3>
        </a>
        <a href="#" style="background: #e74c3c; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Reports</h3>
        </a>
    </div>
</div>

<h3 style="margin-top: 2rem;">Shipment Search</h3>
<div style="background: #f8f9fa; padding: 1rem; border-radius: 4; margin-top: 1rem;">
    <form action="{{ route('portal.shipments.search') }}" method="GET" style="max-width: 400px;">
        <div class="form-group">
            <input type="text" name="tracking" placeholder="Enter tracking number (e.g. CP-AX91-4821)" style="width: 70%; display: inline-block; vertical-align: middle;" required>
            <button type="submit" style="width: 28%; padding: 0 1rem; vertical-align: middle;">Lookup</button>
        </div>
    </form>
</div>
@if(isset($query))
    <div style="margin-top: 1rem;">
        <p><strong>Search query:</strong> {{ $query }}</p>
    </div>
@endif
@endsection