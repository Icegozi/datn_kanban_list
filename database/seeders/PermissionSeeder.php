<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert([
            [
                'name' => 'board_viewer',
                'description' => 'Quyền xem nội dung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'board_editor',
                'description' => 'Quyền chỉnh sửa nội dung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'board_member_manager',
                'description' => 'Quyền mời và quản lý thành viên',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
