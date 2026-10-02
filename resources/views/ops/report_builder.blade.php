@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Report Builder</h2>
    <form method="POST" action="{{ route('ops.reports.store') }}">
        @csrf
        <div class="form-group">
            <label for="report_type">Report Type</label>
            <select name="report_type" id="report_type">
                <option value="delivery_status">Delivery Status</option>
                <option value="risk_assessment">Risk Assessment</option>
                <option value="customs_hold">Customs Hold</option>
                <option value="inventory_check">Inventory Check</option>
                <option value="special_investigation">Special Investigation</option>
            </select>
        </div>
        <div class="form-group">
            <label for="filter_expression">Filter Expression</label>
            <textarea name="filter_expression" id="filter_expression" rows="3" placeholder="Enter filter criteria (e.g., is_restricted = 0 AND destination LIKE '%Dallas%')">is_restricted = 0</textarea>
            <small style="color: #64748b;">The filter expression is stored and later used to generate reports.</small>
        </div>
        <button type="submit">Save Report Filter</button>
    </form>
</div>

<div class="card">
    <h3>Report Generation</h3>
    <p>After saving a report filter, generate the report to see the results.</p>
    <form method="GET" action="{{ route('ops.reports.generate') }}">
        <div class="form-group">
            <label for="job_id">Report Job ID</label>
            <input type="number" name="job_id" id="job_id" min="1" required>
            <small style="color: #64748b;">Enter the job ID of the saved report filter to generate results.</small>
        </div>
        <button type="submit">Generate Report</button>
    </form>
</div>
@if(isset($result) && $result !== null)
<div class="card" style="margin-top: 2rem;">
    <h3>Report Results</h3>
    <p><strong>Status:</strong> {{ $result->status }}</p>
    <p><strong>Result Reference:</strong> {{ $result->result_reference }}</p>
</div>
@endif
@endsection