<?php
namespace Database\Seeders;

use App\Models\DiemDanh;
use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;
use App\Models\PhanCongGiangDay;
use App\Models\User;
use App\Support\NamHoc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Dữ liệu mẫu để chạy thử: php artisan db:seed --class=DemoDataSeeder
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026);

        $namHoc   = NamHoc::hienTai();
        $namTruoc = (substr($namHoc, 0, 4) - 1) . '-' . substr($namHoc, 0, 4);

        $gvcn = User::where('email', 'hoa.gvcn@school.com')->firstOrFail();
        $gvbm = User::firstOrCreate(['email' => 'nam.gvbm@school.com'], [
            'name' => 'Trần Văn Nam', 'password' => Hash::make('Gvbm@123456'), 'is_active' => true,
        ]);
        $gvbm->assignRole('giao-vien-bo-mon');

        // Lớp năm trước (đã học xong) và các lớp năm nay
        $lopCu = LopHoc::firstOrCreate(['ten_lop' => '10A1', 'nam_hoc' => $namTruoc],
            ['khoi' => '10', 'giao_vien_chu_nhiem_id' => $gvcn->id]);
        $lop11 = LopHoc::firstOrCreate(['ten_lop' => '11A1', 'nam_hoc' => $namHoc],
            ['khoi' => '11', 'giao_vien_chu_nhiem_id' => $gvcn->id]);
        LopHoc::firstOrCreate(['ten_lop' => '10A1', 'nam_hoc' => $namHoc], ['khoi' => '10']);
        LopHoc::firstOrCreate(['ten_lop' => '12A1', 'nam_hoc' => $namHoc], ['khoi' => '12']);

        $hoTen = [
            ['Nguyễn Văn An', 'Nam'], ['Trần Thị Bình', 'Nữ'], ['Lê Hoàng Cường', 'Nam'],
            ['Phạm Thu Dung', 'Nữ'], ['Hoàng Minh Đức', 'Nam'], ['Vũ Ngọc Hà', 'Nữ'],
            ['Đặng Quốc Huy', 'Nam'], ['Bùi Thị Lan', 'Nữ'], ['Đỗ Thành Long', 'Nam'],
            ['Ngô Phương Mai', 'Nữ'], ['Dương Văn Nam', 'Nam'], ['Lý Thị Oanh', 'Nữ'],
        ];

        $monHoc = MonHoc::where('is_active', true)->get();

        foreach ($hoTen as $i => [$ten, $gioiTinh]) {
            $hs = HocSinh::firstOrCreate(['ho_ten' => $ten, 'lop_hoc_id' => $lopCu->id], [
                'ngay_sinh' => (substr($namTruoc, 0, 4) - 15) . '-0' . (1 + $i % 9) . '-1' . ($i % 9),
                'gioi_tinh' => $gioiTinh,
            ]);

            // Học lực khác nhau: vài em giỏi, vài em yếu
            $nen = [9.0, 8.4, 7.2, 6.4, 5.2, 8.8, 4.6, 7.8, 6.9, 9.3, 5.8, 7.5][$i];

            foreach (['1', '2'] as $hk) {
                foreach ($monHoc as $mon) {
                    $d = fn() => max(0, min(10, round($nen + mt_rand(-12, 12) / 10, 1)));
                    $diem = DiemSo::firstOrNew([
                        'hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id,
                        'lop_hoc_id' => $lopCu->id, 'hoc_ky' => $hk, 'nam_hoc' => $namTruoc,
                    ]);
                    $diem->fill([
                        'diem_tx1' => $d(), 'diem_tx2' => $d(), 'diem_tx3' => $d(),
                        'diem_gk' => $d(), 'diem_ck' => $d(), 'giao_vien_id' => $gvcn->id,
                    ]);
                    $diem->diem_trung_binh = $diem->tinhDiemTrungBinh();
                    $diem->save();
                }

                HanhKiem::updateOrCreate(
                    ['hoc_sinh_id' => $hs->id, 'lop_hoc_id' => $lopCu->id, 'hoc_ky' => $hk, 'nam_hoc' => $namTruoc],
                    [
                        'xep_loai_hanh_kiem' => $nen >= 7 ? 'Tốt' : ($nen >= 5 ? 'Khá' : 'Trung bình'),
                        'so_buoi_hoc' => 90,
                        'so_buoi_vang_co_phep' => mt_rand(0, 3),
                        'so_buoi_vang_khong_phep' => $i === 6 ? 25 : mt_rand(0, 1),
                        'giao_vien_chu_nhiem_id' => $gvcn->id,
                    ]
                );
            }

            HanhKiem::updateOrCreate(
                ['hoc_sinh_id' => $hs->id, 'lop_hoc_id' => $lopCu->id, 'hoc_ky' => 'Ca năm', 'nam_hoc' => $namTruoc],
                [
                    'xep_loai_hanh_kiem' => $nen >= 7 ? 'Tốt' : ($nen >= 5 ? 'Khá' : 'Trung bình'),
                    'so_buoi_hoc' => 180,
                    'so_buoi_vang_co_phep' => 2,
                    'so_buoi_vang_khong_phep' => $i === 6 ? 50 : 1,
                    'giao_vien_chu_nhiem_id' => $gvcn->id,
                ]
            );
        }
        $lopCu->capNhatSiSo();

        // Một vài học sinh lớp 11A1 năm nay + điểm danh 5 ngày gần nhất
        foreach (['Mai Anh Tuấn' => 'Nam', 'Trịnh Bảo Ngọc' => 'Nữ', 'Phan Gia Khang' => 'Nam'] as $ten => $gt) {
            $hs = HocSinh::firstOrCreate(['ho_ten' => $ten, 'lop_hoc_id' => $lop11->id], [
                'ngay_sinh' => (substr($namHoc, 0, 4) - 16) . '-05-20', 'gioi_tinh' => $gt,
            ]);
            for ($n = 1; $n <= 5; $n++) {
                $ngay = today()->subWeekdays($n);
                if ($ngay->lt(NamHoc::khoangThoiGian($namHoc, 'Ca năm')[0])) {
                    continue;
                }
                DiemDanh::updateOrCreate(['hoc_sinh_id' => $hs->id, 'ngay' => $ngay->toDateString()], [
                    'lop_hoc_id' => $lop11->id,
                    'trang_thai' => ['Có mặt', 'Có mặt', 'Có mặt', 'Vắng có phép', 'Đi trễ'][mt_rand(0, 4)],
                ]);
            }
        }
        $lop11->capNhatSiSo();

        // Phân công: GV bộ môn dạy Toán, Vật lý lớp 11A1
        foreach (MonHoc::whereIn('ma_mon', ['TOAN', 'LY'])->get() as $mon) {
            PhanCongGiangDay::firstOrCreate([
                'giao_vien_id' => $gvbm->id, 'mon_hoc_id' => $mon->id,
                'lop_hoc_id' => $lop11->id, 'nam_hoc' => $namHoc, 'hoc_ky' => 'Ca năm',
            ]);
        }

        $this->command->info("✅ Đã tạo dữ liệu mẫu (lớp 10A1 năm {$namTruoc} có đủ điểm để thử Tổng kết & Lên lớp).");
        $this->command->info('GV bộ môn: nam.gvbm@school.com / Gvbm@123456');
    }
}
