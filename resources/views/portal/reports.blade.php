@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Reports</h2>
    <p>Welcome, {{ Session::get('user.name') }}!</p>
    <p>Shipment reports are available through the Operations Console.</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <a href="#" style="background: #9b59b6; color: white; padding: 0.75rem; border-radius: 4; text-decoration: none;">View My Reports</a>
    </div>
</div>
@endsection