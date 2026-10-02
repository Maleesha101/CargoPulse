<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReportJob;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Session::get('user');
            if (!$user || !in_array($user['role'], ['operations', 'compliance', 'admin'])) {
                return redirect('/login')->with('error', 'Access denied.');
            }
            return $next($request);
        });
    }

    public function createForm()
    {
        return view('ops.report_builder');
    }

    public function storeFilter(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'filter_expression' => 'required|string|max:255',
        ]);

        $user = Session::get('user');

        $reportJob = ReportJob::create([
            'user_id' => $user['id'],
            'report_type' => $request->report_type,
            'filter_expression' => $request->filter_expression,
            'status' => 'pending',
        ]);

        \App\Models\AuditLog::create([
            'user_id' => $user['id'],
            'action' => 'report_create',
            'resource' => 'report_jobs',
            'metadata' => ['report_job_id' => $reportJob->id],
        ]);

        return redirect('/ops/reports')->with('success', 'Report filter saved.');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
        ]);

        $user = Session::get('user');

        // Get the report job
        $reportJob = ReportJob::find($request->job_id);

        if (!$reportJob || $reportJob->user_id != $user['id']) {
            return redirect('/ops/reports')->with('error', 'Report not found or access denied.');
        }

        // Update status to processing
        $reportJob->status = 'processing';
        $reportJob->save();

        \App\Models\AuditLog::create([
            'user_id' => $user['id'],
            'action' => 'report_start_processing',
            'resource' => 'report_jobs',
            'metadata' => ['report_job_id' => $reportJob->id],
        ]);

        // SECOND-ORDER SQL INJECTION VULNERABILITY
        // The filter expression is stored safely but then concatenated directly into SQL
        $storedFilter = $reportJob->getFilterExpression();

        // INTENTIONALLY VULNERABLE — SQLi training lab
        // The stored filter is concatenated directly into the SQL query
        $query = "SELECT s.*, COUNT(se.id) as event_count
                 FROM shipments s
                 LEFT JOIN shipment_events se ON s.id = se.shipment_id
                 WHERE " . $storedFilter . "
                 GROUP BY s.id
                 ORDER BY s.created_at DESC";

        $results = DB::select($query);

        // Store results and mark as completed
        $reportJob->status = 'completed';
        $reportJob->result_reference = 'result_' . $reportJob->id . '_' . time();
        $reportJob->save();

        \App\Models\AuditLog::create([
            'user_id' => $user['id'],
            'action' => 'report_completed',
            'resource' => 'report_jobs',
            'metadata' => ['report_job_id' => $reportJob->id, 'result_count' => count($results)],
        ]);

        return view('ops.report_results', [
            'results' => $results,
            'job' => $reportJob,
        ]);
    }

    public function queue()
    {
        $user = Session::get('user');
        $jobs = ReportJob::where('user_id', $user['id'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('ops.report_queue', [
            'jobs' => $jobs,
        ]);
    }
}