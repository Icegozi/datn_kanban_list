<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('board_permissions')->insert([
            [
                'board_id' => 1,
                'permission_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'board_id' => 1,
                'permission_user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'board_id' => 2,
                'permission_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'board_id' => 2,
                'permission_user_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'board_id' => 3,
                'permission_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'board_id' => 3,
                'permission_user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
