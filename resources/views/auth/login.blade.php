@extends('layouts.app')

@section('content')
<div class="card" style="max-width: 400px; margin: 4rem auto;">
    <h2 style="text-align: center;">CargoPulse Login</h2>
    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required value="customer@cargopulse.test">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required value="LabPass123!">
        </div>
        <button type="submit">Login</button>
    </form>
    <div style="text-align: center; margin-top: 1rem; color: #64748b; font-size: 0.875rem;">
        <p>Training credentials:</p>
        <ul style="text-align: left; display: inline-block;">
            <li>customer@cargopulse.test / LabPass123!</li>
            <li>support@cargopulse.test / LabPass123!</li>
            <li>operations@cargopulse.test / LabPass123!</li>
            <li>compliance@cargopulse.test / LabPass123!</li>
            <li>admin@cargopulse.test / LabPass123!</li>
        </ul>
    </div>
</div>
@endsection