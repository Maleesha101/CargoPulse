@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Report Results</h2>
    <div style="margin-bottom: 1rem; color: #64748b;">
        <p>Report Type: <strong>{{ $job->report_type }}</strong></p>
        <p>Status: <span style="color: #27ae60;">{{ $job->status }}</span></p>
        <p>Generated: {{ $job->updated_at }}</p>
    </div>
</div>

@if($results && count($results) > 0)
<div class="card">
    <h3>Results ({{ count($results) }} rows)</h3>
    <table>
        <thead>
            <tr>
                @foreach($results[0] as $key => $value)
                    <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($results as $row)
            <tr>
                @foreach($row as $key => $value)
                    <td>{{ $value ?? '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card">
    <p style="color: #95a5a6;">No results returned.</p>
</div>
@endif
@endsection