<?php

namespace Tests\Feature\QuanLy;

use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class QuanLyTestCase extends TestCase
{
    use RefreshDatabase;

    protected function taoUser(string $vaiTro): User
    {
        $user = User::factory()->create();
        $user->assignRole($vaiTro);

        return $user;
    }

    protected function taoLop(array $thuocTinh = []): LopHoc
    {
        return LopHoc::create($thuocTinh + ['ten_lop' => '10A1', 'khoi' => '10', 'nam_hoc' => '2025-2026']);
    }

    protected function taoHocSinh(LopHoc $lop, string $ten = 'Nguyễn Văn A'): HocSinh
    {
        return HocSinh::create([
            'ho_ten' => $ten, 'ngay_sinh' => '2010-01-01', 'gioi_tinh' => 'Nam', 'lop_hoc_id' => $lop->id,
        ]);
    }

    protected function taoMon(string $ma = 'TOAN'): MonHoc
    {
        return MonHoc::create(['ma_mon' => $ma, 'ten_mon' => $ma, 'so_tiet_tuan' => 3, 'khoi' => 'Tất cả']);
    }
}
