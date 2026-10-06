<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // PostgreSQL doesn't require disabling foreign key checks
        // The seeders respect foreign key ordering via proper model relationships
        $this->call(UsersTableSeeder::class);
        $this->call(ShipmentsTableSeeder::class);
        $this->call(ShipmentEventsTableSeeder::class);
        $this->call(WarehouseNotesTableSeeder::class);
        $this->call(ReportJobsTableSeeder::class);
        $this->call(InternalCredentialsTableSeeder::class);
        $this->call(SecurityChallengesTableSeeder::class);
    }
}