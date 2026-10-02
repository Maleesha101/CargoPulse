<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Session::get('user');
            if (!$user || $user['role'] !== 'customer') {
                return redirect('/login')->with('error', 'Access denied.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = Session::get('user');

        $recentShipments = Shipment::where('customer_name', 'like', '%' . 'Customer' . '%')
            ->orWhere('customer_name', 'like', '%' . $user['name'] . '%')
            ->limit(5)
            ->get();

        \App\Models\AuditLog::create([
            'user_id' => $user['id'],
            'action' => 'portal_dashboard_view',
            'resource' => 'dashboard',
            'metadata' => [],
        ]);

        return view('portal.dashboard', [
            'shipments' => $recentShipments,
        ]);
    }

    public function track()
    {
        return view('portal.track_shipment');
    }

    public function history()
    {
        $user = Session::get('user');
        $shipments = Shipment::where('customer_name', 'like', '%' . 'Customer' . '%')
            ->orWhere('customer_name', 'like', '%' . $user['name'] . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.shipment_history', [
            'shipments' => $shipments,
        ]);
    }

    public function searchForm()
    {
        return view('portal.shipment_search');
    }
}