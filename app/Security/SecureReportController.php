<?php

namespace App\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ReportJob;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Session;

/**
 * Secure version of the Report Controller.
 * Uses allowlisted filter fields instead of concatenating stored SQL.
 *
 * This class replaces the vulnerable OpsReportController methods.
 */
class SecureReportController extends Controller
{
    protected $validFilterFields = [
        'status',
        'report_type',
        'customer_name',
        'origin',
        'destination',
        'tracking_number',
        'is_restricted'
    ];

    protected $validOperators = [
        '=',
        'LIKE',
        '>',
        '<',
        '>=',
        '<=',
        'IN'
    ];

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
        return view('ops.report_builder_secure');
    }

    public function storeFilter(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'filter_expression' => 'required|string|max:255',
        ]);

        $user = Session::get('user');

        // SECURE: Validate the filter expression before storing
        $isValid = $this->validateFilterExpression($request->filter_expression);

        $reportJob = ReportJob::create([
            'user_id' => $user['id'],
            'report_type' => $request->report_type,
            'filter_expression' => $request->filter_expression,
            'status' => $isValid ? 'pending' : 'invalid',
        ]);

        \App\Models\AuditLog::create([
            'user_id' => $user['id'],
            'action' => 'secure_report_create',
            'resource' => 'report_jobs',
            'metadata' => ['report_job_id' => $reportJob->id, 'filter_valid' => $isValid],
        ]);

        if (!$isValid) {
            return redirect('/ops/reports/create')
                ->with('error', 'Invalid filter expression: unsafe SQL operators detected.')
                ->with('filter_expression', $request->filter_expression);
        }

        return redirect('/ops/reports')->with('success', 'Report filter saved.');
    }

    protected function validateFilterExpression($expression)
    {
        // Check for dangerous SQL operators and patterns
        $dangerousPatterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE)\b/i',
            '/--/', // SQL comments
            '/\/\*.*?\*\//s', // SQL block comments
            '/1\s*=\s*1/', // Always true
            "/';/", // Statement termination
            "/'\\s*OR\\s*/i", // Unsafe OR injection
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $expression)) {
                return false;
            }
        }

        // Only allow specific operators and field names
        $validExpression = $this->sanitizeFilterExpression($expression);

        // The sanitized version should be close to the original if it's valid
        // If the sanitized version is very different, reject it
        return $validExpression === $expression;
    }

    protected function sanitizeFilterExpression($expression)
    {
        // Only allow: field operator value patterns
        // Remove anything that doesn't match safe patterns
        $expression = preg_replace('/;/', '', $expression);
        $expression = preg_replace('/--.*$/m', '', $expression);
        $expression = preg_replace('/\/\*.*?\*\//s', '', $expression);

        return trim($expression);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
        ]);

        $user = Session::get('user');

        $reportJob = ReportJob::find($request->job_id);

        if (!$reportJob || $reportJob->user_id != $user['id']) {
            return redirect('/ops/reports')->with('error', 'Report not found or access denied.');
        }

        // SECURE: Use allowlisted fields only, never concatenate raw SQL
        $this->processSecureReport($reportJob);

        return redirect('/ops/reports')->with('success', 'Report generated securely.');
    }

    protected function processSecureReport(ReportJob $job)
    {
        // Parse the filter expression safely
        // Extract only allowlisted field names and values
        // Build the query using parameterized conditions

        // For this training app, we parse simple field=value patterns
        $conditions = [];
        $params = [];

        // Parse patterns like: field = value
        if (preg_match_all('/(\w+)\s*=\s*([^\s,]+)/', $job->filter_expression, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $field = $match[1];
                $value = $match[2];

                // Only use allowlisted fields
                if (in_array($field, $this->validFilterFields)) {
                    $conditions[] = "s." . $field . " = ?";
                    $params[] = $value;
                }
            }
        }

        // Build the parameterized query
        $query = "SELECT s.*, COUNT(se.id) as event_count
                 FROM shipments s
                 LEFT JOIN shipment_events se ON s.id = se.shipment_id";

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY s.id ORDER BY s.created_at DESC";

        $results = DB::select($query, $params);

        $job->status = 'completed';
        $job->result_reference = 'secure_result_' . $job->id . '_' . time();
        $job->save();
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