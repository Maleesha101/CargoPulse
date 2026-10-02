<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternalCredentialsTableSeeder extends Seeder
{
    public function run(): void
    {
        $credentials = [
            [
                'service_name' => 'customs-sync',
                'username' => 'customs_reporter',
                'secret_value' => 'TRAINING_ONLY_CREDENTIAL_CUSTOMS_2024',
                'environment' => 'production',
                'description' => 'Training credential for customs integration service',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_name' => 'inventory-tracker',
                'username' => 'inv_sync_bot',
                'secret_value' => 'TRAINING_ONLY_CREDENTIAL_INVENTORY_xyz',
                'environment' => 'staging',
                'description' => 'Training credential for inventory tracking system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_name' => 'risk-analyzer',
                'username' => 'risk_assessor',
                'secret_value' => 'TRAINING_ONLY_CREDENTIAL_RISK_alpha',
                'environment' => 'development',
                'description' => 'Training credential for risk analysis service',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('internal_credentials')->insert($credentials);
    }
}