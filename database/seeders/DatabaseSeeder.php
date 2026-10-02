<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call(UsersTableSeeder::class);
        $this->call(ShipmentsTableSeeder::class);
        $this->call(ShipmentEventsTableSeeder::class);
        $this->call(WarehouseNotesTableSeeder::class);
        $this->call(ReportJobsTableSeeder::class);
        $this->call(InternalCredentialsTableSeeder::class);
        $this->call(SecurityChallengesTableSeeder::class);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}