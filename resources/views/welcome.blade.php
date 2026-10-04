@extends('layouts.app')

@section('content')
<div class="card" style="text-align: center; max-width: 600px; margin: 2rem auto;">
    <h1>Welcome to CargoPulse</h1>
    <p style="color: #64748b; margin: 1rem 0;">The Phantom Shipment - SQL Injection Training Lab</p>
    <a href="{{ route('login') }}" style="background: #3498db; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none;">Login to Continue</a>
</div>
@endsection