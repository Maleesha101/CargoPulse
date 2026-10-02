<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportJobsTableSeeder extends Seeder
{
    public function run(): void
    {
        $reportJobs = [
            [
                'user_id' => 3, // operations user
                'report_type' => 'delivery_status',
                'filter_expression' => "is_restricted = 0 AND customer_name LIKE '%Medical%'",
                'status' => 'completed',
                'result_reference' => null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'user_id' => 3,
                'report_type' => 'risk_assessment',
                'filter_expression' => "destination LIKE '%Texas%' OR origin LIKE '%Chicago%'",
                'status' => 'completed',
                'result_reference' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => 4, // compliance user
                'report_type' => 'customs_hold',
                'filter_expression' => "event_type = 'CUSTOMS_REVIEW'",
                'status' => 'pending',
                'result_reference' => null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 3,
                'report_type' => 'inventory_check',
                'filter_expression' => "1=1", // Safe filter that matches all shipments
                'status' => 'pending',
                'result_reference' => null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 3,
                'report_type' => 'special_investigation',
                'filter_expression' => "tracking_number LIKE 'CP-%'", // Matches our tracking pattern
                'status' => 'pending',
                'result_reference' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('report_jobs')->insert($reportJobs);
    }
}