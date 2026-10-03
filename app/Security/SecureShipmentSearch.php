<?php

namespace App\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\AuditLog;

/**
 * Secure version of the Shipment Search endpoint.
 * Uses parameterized queries to prevent SQL injection.
 *
 * This class replaces the vulnerable ShipmentController::search method.
 */
class SecureShipmentSearch
{
    public function search($tracking)
    {
        // SECURE: Using parameterized query - the input is never concatenated into SQL
        // The ? placeholder is replaced with a properly escaped value by PDO
        $results = DB::select("SELECT * FROM shipments WHERE tracking_number = ?", [$tracking]);

        // Log the search for audit purposes
        $user = Session::get('user');
        if ($user) {
            AuditLog::create([
                'user_id' => $user['id'],
                'action' => 'secure_shipment_search',
                'resource' => 'shipments',
                'metadata' => ['query' => $tracking],
            ]);
        }

        return $results;
    }
}