<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ShipmentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $customers = [
            'Helix Medical Logistics',
            'NovaGrid Energy',
            'Vertex Electronics',
            'Apex BioSciences',
            'Quantum Defense Systems'
        ];

        $carrier = ['FedEx', 'UPS', 'DHL', 'Regional Logistics'];

        $shipments = [];

        // Regular shipments
        for ($i = 0; $i < 19; $i++) {
            $shipments[] = [
                'tracking_number' => 'CP-' . $faker->bothify('##-####') . '-' . $faker->numberBetween(1000, 9999),
                'customer_name' => $faker->randomElement($customers),
                'origin' => $faker->city(),
                'destination' => $faker->city(),
                'shipped_at' => $faker->dateTimeBetween('-30 days', 'now'),
                'is_restricted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // The special CP-VOID-7719 shipment with metadata containing report workflow hint
        $shipments[] = [
            'tracking_number' => 'CP-VOID-7719',
            'customer_name' => 'Legacy Data Systems',
            'origin' => 'Chicago, IL',
            'destination' => 'Dallas, TX',
            'shipped_at' => '2024-01-15 08:30:00',
            'is_restricted' => true,
            'created_at' => '2024-01-15 08:30:00',
            'updated_at' => '2024-01-15 08:30:00',
        ];

        DB::table('shipments')->insert($shipments);
    }
}