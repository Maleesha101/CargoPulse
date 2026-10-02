@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Report Queue</h2>
    @if($jobs && count($jobs) > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Filter Expression</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            <tr>
                <td>{{ $job->id }}</td>
                <td>{{ $job->report_type }}</td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $job->filter_expression }}</td>
                <td>
                    @switch($job->status)
                        @case('pending') <span style="color: #f39c12;">{{ $job->status }}</span> @break
                        @case('processing') <span style="color: #3498db;">{{ $job->status }}</span> @break
                        @case('completed') <span style="color: #27ae60;">{{ $job->status }}</span> @break
                        @default <span style="color: #7f8c8d;">{{ $job->status }}</span>
                    @endswitch
                </td>
                <td>{{ $job->created_at }}</td>
                <td>
                    <a href="{{ route('ops.reports.generate', ['job_id' => $job->id]) }}" style="font-size: 0.875rem; padding: 0.25rem 0.5rem; background: #9b59b6; color: white; border-radius: 3px;">Generate</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No reports saved yet.</p>
    @endif
</div>
@endsection