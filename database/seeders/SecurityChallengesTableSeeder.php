<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecurityChallengesTableSeeder extends Seeder
{
    public function run(): void
    {
        $flag = 'CP{second_order_sqli_chain_complete}';

        $challenge = [
            [
                'challenge_name' => 'Second-Order SQLi Chain',
                'flag' => $flag,
                'description' => 'Flag obtained by chaining in-band SQLi with second-order SQLi through the report workflow',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('security_challenges')->insert($challenge);
    }
}