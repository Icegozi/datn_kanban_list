<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Nguyễn Văn A',
                'email' => 'a@example.com',
                'email_verified_at' => now(),
                'is_admin' => false,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trần Thị B',
                'email' => 'b@example.com',
                'email_verified_at' => now(),
                'is_admin' => true,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lê Văn C',
                'email' => 'c@example.com',
                'email_verified_at' => null,
                'is_admin' => false,
                'status' => 'inactive',
                'password' => Hash::make('123456'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Phạm Thị D',
                'email' => 'd@example.com',
                'email_verified_at' => now(),
                'is_admin' => false,
                'status' => 'banned',
                'password' => Hash::make('abc12345'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hoàng Văn E',
                'email' => 'e@example.com',
                'email_verified_at' => now(),
                'is_admin' => true,
                'status' => 'active',
                'password' => Hash::make('e_pass123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đỗ Thị F',
                'email' => 'f@example.com',
                'email_verified_at' => now(),
                'is_admin' => false,
                'status' => 'active',
                'password' => Hash::make('fpass!'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ngô Văn G',
                'email' => 'g@example.com',
                'email_verified_at' => null,
                'is_admin' => false,
                'status' => 'inactive',
                'password' => Hash::make('gpass1234'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đặng Thị H',
                'email' => 'h@example.com',
                'email_verified_at' => now(),
                'is_admin' => true,
                'status' => 'banned',
                'password' => Hash::make('h1234567'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vũ Văn I',
                'email' => 'i@example.com',
                'email_verified_at' => now(),
                'is_admin' => false,
                'status' => 'active',
                'password' => Hash::make('ipass_321'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lý Thị J',
                'email' => 'j@example.com',
                'email_verified_at' => now(),
                'is_admin' => true,
                'status' => 'active',
                'password' => Hash::make('jpassword'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
