<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ShipmentEventsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $shipmentIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // First 10 shipment IDs

        $eventTypes = [
            'ARRIVED_WAREHOUSE',
            'CUSTOMS_REVIEW',
            'DEPARTED',
            'DELIVERY_ATTEMPT',
            'HOLD',
        ];

        $eventDescriptions = [
            'Shipment arrived at destination warehouse',
            'Package undergoing customs inspection',
            'Departed origin facility',
            'Delivery attempt made, recipient not available',
            'Shipment placed on hold for inspection',
        ];

        $events = [];

        foreach ($shipmentIds as $shipmentId) {
            // 5-8 events per shipment
            $count = $faker->numberBetween(5, 8);

            for ($i = 0; $i < $count; $i++) {
                $events[] = [
                    'shipment_id' => $shipmentId,
                    'event_type' => $faker->randomElement($eventTypes),
                    'location' => $faker->city(),
                    'description' => $faker->randomElement($eventDescriptions),
                    'event_time' => $faker->dateTimeBetween('-30 days', 'now'),
                ];
            }
        }

        DB::table('shipment_events')->insert($events);
    }
}