<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\AuditLog;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = \App\Models\Shipment::all();

        return view('shipments.index', [
            'shipments' => $shipments,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('tracking');

        // INTENTIONALLY VULNERABLE — SQLi training lab
        // The user input is concatenated directly into the SQL query
        // This is the primary vulnerability for the lab (SQL-01)
        $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%{$query}%'");

        // Log the search for audit purposes
        $user = Session::get('user');
        if ($user) {
            AuditLog::create([
                'user_id' => $user['id'],
                'action' => 'shipment_search',
                'resource' => 'shipments',
                'metadata' => ['query' => $query],
            ]);
        }

        return view('shipments.results', [
            'results' => $results,
            'query' => $query,
        ]);
    }
}