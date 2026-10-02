<?php

namespace Database\Seeders;

use Illuminate\DatabaseSeeder;
use Faker\Factory as Faker;

class WarehouseNotesTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $notes = [
            [
                'shipment_id' => 1,
                'author_id' => 3, // operations
                'note' => 'Package received with minor exterior damage. Contents verified intact.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 2,
                'author_id' => 3,
                'note' => 'High-value electronics held for customs clearance. Requires secondary inspection.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 3,
                'author_id' => 3,
                'note' => 'Temperature-sensitive shipment. Cold chain maintained throughout transit.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 4,
                'author_id' => 3,
                'note' => 'Customer requested hold for 48 hours. Package stored in secure area.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 5,
                'author_id' => 3,
                'note' => 'Standard delivery completed. No issues reported.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 6,
                'author_id' => 3,
                'note' => 'Package returned to sender due to incorrect address.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 7,
                'author_id' => 3,
                'note' => 'Customs inspection completed. All documentation verified.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 8,
                'author_id' => 3,
                'note' => 'Package held for payment verification. Customer contacted.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 9,
                'author_id' => 3,
                'note' => 'Delivery attempted twice. Recipient not available. Scheduled for retry.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 10,
                'author_id' => 3,
                'note' => 'Package transferred to secondary facility for processing.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 11,
                'author_id' => 3,
                'note' => 'Standard delivery completed. No issues reported.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 12,
                'author_id' => 3,
                'note' => 'Package returned to sender due to incorrect address.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 13,
                'author_id' => 3,
                'note' => 'Customs inspection completed. All documentation verified.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 14,
                'author_id' => 3,
                'note' => 'Package held for payment verification. Customer contacted.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shipment_id' => 15,
                'author_id' => 3,
                'note' => 'Delivery attempted twice. Recipient not available. Scheduled for retry.',
                'visibility' => 'internal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('warehouse_notes')->insert($notes);
    }
}