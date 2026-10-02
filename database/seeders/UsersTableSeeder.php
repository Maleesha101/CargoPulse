<?php

namespace Database\Seeders;

use Illuminate\Database Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Alice Chen',
                'email' => 'customer@cargopulse.test',
                'password_hash' => Hash::make('LabPass123!'),
                'role' => 'customer',
                'department' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marcus Webb',
                'email' => 'support@cargopulse.test',
                'password_hash' => Hash::make('LabPass123!'),
                'role' => 'support',
                'department' => 'Customer Support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dana Park',
                'email' => 'operations@cargopulse.test',
                'password_hash' => Hash::make('LabPass123!'),
                'role' => 'operations',
                'department' => 'Operations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Priya Nair',
                'email' => 'compliance@cargopulse.test',
                'password_hash' => Hash::make('LabPass123!'),
                'role' => 'compliance',
                'department' => 'Compliance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Robert Vance',
                'email' => 'admin@cargopulse.test',
                'password_hash' => Hash::make('LabPass123!'),
                'role' => 'admin',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}