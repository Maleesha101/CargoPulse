@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Operations Console</h2>
    <p>Welcome, {{ Session::get('user.name') }}! ({{ Session::get('user.role') }})</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <a href="#" style="background: #3498db; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Investigation</h3>
        </a>
        <a href="{{ route('ops.reports.create') }}" style="background: #9b59b6; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Report Builder</h3>
        </a>
        <a href="{{ route('ops.reports') }}" style="background: #34495e; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Report Queue</h3>
        </a>
        <a href="#" style="background: #1abc9c; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Compliance</h3>
        </a>
        <a href="#" style="background: #e67e22; color: white; padding: 1rem; border-radius: 4; text-decoration: none;">
            <h3>Audit Logs</h3>
        </a>
    </div>
</div>
@if(isset($jobs) && count($jobs) > 0)
<div class="card" style="margin-top: 2rem;">
    <h3>Recent Reports</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            <tr>
                <td>{{ $job->report_type }}</td>
                <td>{{ $job->status }}</td>
                <td>{{ $job->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection