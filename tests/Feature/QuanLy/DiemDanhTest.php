<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemDanh;
use App\Support\NamHoc;
use Carbon\Carbon;

class DiemDanhTest extends QuanLyTestCase
{
    public function test_gvcn_diem_danh_lop_minh_va_cap_nhat_lai(): void
    {
        $gv  = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop(['giao_vien_chu_nhiem_id' => $gv->id, 'nam_hoc' => NamHoc::hienTai()]);
        $hs1 = $this->taoHocSinh($lop, 'A');
        $hs2 = $this->taoHocSinh($lop, 'B');
        $ngay = today()->toDateString();

        $this->actingAs($gv)->post('/diem-danh', [
            'lop_hoc_id' => $lop->id, 'ngay' => $ngay,
            'diem_danh' => [
                $hs1->id => ['trang_thai' => 'Có mặt'],
                $hs2->id => ['trang_thai' => 'Vắng có phép', 'ghi_chu' => 'Ốm'],
            ],
        ])->assertRedirect();

        // Điểm danh lại cùng ngày → cập nhật, không tạo bản ghi trùng
        $this->actingAs($gv)->post('/diem-danh', [
            'lop_hoc_id' => $lop->id, 'ngay' => $ngay,
            'diem_danh' => [$hs2->id => ['trang_thai' => 'Vắng không phép']],
        ])->assertRedirect();

        $this->assertSame(2, DiemDanh::count());
        $this->assertSame('Vắng không phép', DiemDanh::where('hoc_sinh_id', $hs2->id)->value('trang_thai'));

        $this->actingAs($gv)->get("/diem-danh?lop_hoc_id={$lop->id}")->assertOk()->assertSee('Đã điểm danh');
    }

    public function test_gvcn_khong_diem_danh_duoc_lop_khac(): void
    {
        $gv  = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop();
        $hs  = $this->taoHocSinh($lop);

        $this->actingAs($gv)->post('/diem-danh', [
            'lop_hoc_id' => $lop->id, 'ngay' => today()->toDateString(),
            'diem_danh' => [$hs->id => ['trang_thai' => 'Có mặt']],
        ])->assertForbidden();
    }

    public function test_khong_diem_danh_ngoai_nam_hoc_cua_lop(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lopNamCu = $this->taoLop(['nam_hoc' => '2020-2021']);
        $hs = $this->taoHocSinh($lopNamCu);

        $this->actingAs($admin)->post('/diem-danh', [
            'lop_hoc_id' => $lopNamCu->id, 'ngay' => today()->toDateString(),
            'diem_danh' => [$hs->id => ['trang_thai' => 'Có mặt']],
        ])->assertSessionHas('error');

        $this->assertSame(0, DiemDanh::count());
    }

    public function test_khong_diem_danh_ngay_tuong_lai(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hs  = $this->taoHocSinh($lop);

        $this->actingAs($admin)->post('/diem-danh', [
            'lop_hoc_id' => $lop->id, 'ngay' => today()->addDay()->toDateString(),
            'diem_danh' => [$hs->id => ['trang_thai' => 'Có mặt']],
        ])->assertSessionHasErrors('ngay');
    }

    public function test_tong_hop_chuyen_can_theo_hoc_ky(): void
    {
        $lop = $this->taoLop();
        $hs  = $this->taoHocSinh($lop);

        foreach (['2025-09-08' => 'Vắng có phép', '2025-09-09' => 'Vắng không phép', '2025-09-10' => 'Đi trễ', '2026-02-02' => 'Vắng có phép'] as $ngay => $tt) {
            DiemDanh::create(['hoc_sinh_id' => $hs->id, 'lop_hoc_id' => $lop->id, 'ngay' => $ngay, 'trang_thai' => $tt]);
        }

        $hk1 = DiemDanh::tongHop($lop->id, Carbon::create(2025, 8, 1), Carbon::create(2026, 1, 15));

        $this->assertSame(3, $hk1['so_buoi']);
        $this->assertSame(['vang_co_phep' => 1, 'vang_khong_phep' => 1, 'di_tre' => 1], $hk1['hoc_sinh'][$hs->id]);
    }
}
