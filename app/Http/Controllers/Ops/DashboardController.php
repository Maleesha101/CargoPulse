<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Shipment;
use App\Models\ReportJob;
use App\Models\AuditLog;

class DashboardController extends Controller
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

    public function index()
    {
        $user = Session::get('user');
        $jobs = ReportJob::where('user_id', $user['id'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        \App\Models\AuditLog::create([
            'user_id' => $user['id'],
            'action' => 'ops_dashboard_view',
            'resource' => 'dashboard',
            'metadata' => [],
        ]);

        return view('ops.dashboard', [
            'jobs' => $jobs,
        ]);
    }

    public function investigation($trackingNumber)
    {
        $user = Session::get('user');
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();

        if (!$shipment) {
            return view('ops.shipment_investigation', [
                'shipment' => null,
                'tracking_number' => $trackingNumber,
                'error' => 'Shipment not found.',
            ]);
        }

        return view('ops.shipment_investigation', [
            'shipment' => $shipment,
            'tracking_number' => $trackingNumber,
        ]);
    }
}