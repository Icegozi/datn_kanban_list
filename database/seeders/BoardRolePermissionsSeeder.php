<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'board_viewer', 'description' => 'Can view board content.'],
            ['name' => 'board_editor', 'description' => 'Can edit board tasks and columns.'],
            ['name' => 'board_member_manager', 'description' => 'Can invite and manage board members.'],
            // Add more as needed, e.g., 'board_settings_admin'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
