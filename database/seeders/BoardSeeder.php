<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('boards')->insert([
            // user_id = 1
            [
                'name' => 'Project A Planning',
                'description' => 'Lập kế hoạch cho dự án A.',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'UI Design Notes',
                'description' => 'Ghi chú về giao diện.',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 2
            [
                'name' => 'Design Ideas',
                'description' => 'Ý tưởng thiết kế sản phẩm.',
                'user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Tasks',
                'description' => 'Danh sách công việc nhóm.',
                'user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 3
            [
                'name' => 'Marketing Strategy',
                'description' => 'Chiến lược marketing 2025.',
                'user_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Campaign Schedule',
                'description' => 'Lịch trình chiến dịch.',
                'user_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 4
            [
                'name' => 'Bug Tracker',
                'description' => 'Theo dõi lỗi hệ thống.',
                'user_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sprint Review',
                'description' => 'Đánh giá sprint cuối.',
                'user_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 5
            [
                'name' => 'Learning Resources',
                'description' => 'Tài liệu học tập nội bộ.',
                'user_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Study Notes',
                'description' => 'Ghi chú khóa học.',
                'user_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 6
            [
                'name' => 'Weekly Tasks',
                'description' => 'Công việc hằng tuần.',
                'user_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Monthly Reports',
                'description' => 'Báo cáo tháng 5.',
                'user_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 7
            [
                'name' => 'Frontend Dev Board',
                'description' => 'Việc cần làm giao diện.',
                'user_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vue.js Tips',
                'description' => 'Kinh nghiệm dùng Vue.',
                'user_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 8
            [
                'name' => 'Backend APIs',
                'description' => 'Tài liệu API nội bộ.',
                'user_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PHP Tricks',
                'description' => 'Kinh nghiệm viết Laravel.',
                'user_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 9
            [
                'name' => 'Client Feedback',
                'description' => 'Góp ý từ khách hàng.',
                'user_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'UI Review',
                'description' => 'Phản hồi giao diện.',
                'user_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // user_id = 10
            [
                'name' => 'Intern Training',
                'description' => 'Tài liệu huấn luyện thực tập.',
                'user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Rules',
                'description' => 'Nội quy làm việc.',
                'user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
