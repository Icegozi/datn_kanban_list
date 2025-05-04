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
                'name' => 'view',
                'description' => 'Quyền xem nội dung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'edit',
                'description' => 'Quyền chỉnh sửa nội dung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'create',
                'description' => 'Quyền tạo mới nội dung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
