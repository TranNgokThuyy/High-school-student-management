<?php
namespace Database\Seeders;

use App\Models\MonHoc;
use Illuminate\Database\Seeder;

class MonHocSeeder extends Seeder
{
    public function run(): void
    {
        $monHoc = [
            ['TOAN',  'Toán',           4, 'Tất cả'],
            ['VAN',   'Ngữ văn',        3, 'Tất cả'],
            ['ANH',   'Tiếng Anh',      3, 'Tất cả'],
            ['LY',    'Vật lý',         2, 'Tất cả'],
            ['HOA',   'Hóa học',        2, 'Tất cả'],
            ['SINH',  'Sinh học',       2, 'Tất cả'],
            ['SU',    'Lịch sử',        2, 'Tất cả'],
            ['DIA',   'Địa lý',         2, 'Tất cả'],
            ['GDCD',  'GDCD',           1, 'Tất cả'],
            ['TIN',   'Tin học',        2, 'Tất cả'],
            ['TD',    'Giáo dục thể chất', 2, 'Tất cả'],
            ['CNGT',  'Công nghệ',      1, 'Tất cả'],
        ];

        // Môn đánh giá bằng nhận xét theo Thông tư 22/2021
        $monNhanXet = ['TD', 'HDTN'];
        $monHoc[] = ['HDTN', 'HĐ trải nghiệm, hướng nghiệp', 3, 'Tất cả'];

        foreach ($monHoc as [$ma, $ten, $tiet, $khoi]) {
            MonHoc::firstOrCreate(
                ['ma_mon' => $ma],
                [
                    'ten_mon'      => $ten,
                    'so_tiet_tuan' => $tiet,
                    'khoi'         => $khoi,
                    'hinh_thuc_danh_gia' => in_array($ma, $monNhanXet) ? 'Nhận xét' : 'Điểm số',
                    'is_active'    => true,
                ]
            );
        }

        $this->command->info('✅ Đã tạo ' . count($monHoc) . ' môn học mặc định!');
    }
}