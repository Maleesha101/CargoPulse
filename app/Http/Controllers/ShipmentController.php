<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::all();

        return view('shipments.index', [
            'shipments' => $shipments,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        // SQL-01: Vulnerable - direct concatenation in query
        $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%{$query}%'");

        return view('shipments.results', [
            'results' => $results,
            'query' => $query,
        ]);
    }
}