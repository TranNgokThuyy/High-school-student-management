<?php

namespace Tests\Feature\QuanLy;

class KhenThuongTest extends QuanLyTestCase
{
    public function test_gvcn_ghi_nhan_khen_thuong_cho_hoc_sinh_lop_minh(): void
    {
        $gv  = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop(['giao_vien_chu_nhiem_id' => $gv->id]);
        $hs  = $this->taoHocSinh($lop);

        $this->actingAs($gv)->post('/khen-thuong', [
            'hoc_sinh_id' => $hs->id, 'loai' => 'Khen thưởng', 'hinh_thuc' => 'Giấy khen',
            'noi_dung' => 'Giải Nhì HSG Toán cấp tỉnh', 'ngay_quyet_dinh' => '2026-03-10',
        ])->assertRedirect('/khen-thuong');

        $this->assertDatabaseHas('khen_thuong_ky_luat', [
            'hoc_sinh_id' => $hs->id, 'nam_hoc' => '2025-2026', 'nguoi_tao_id' => $gv->id,
        ]);

        $this->actingAs($gv)->get("/hoc-sinh/{$hs->id}")->assertOk()->assertSee('Giải Nhì HSG Toán cấp tỉnh');
    }

    public function test_gvcn_khong_ghi_nhan_cho_hoc_sinh_lop_khac_va_khong_xoa_duoc(): void
    {
        $gv  = $this->taoUser('giao-vien-chu-nhiem');
        $hs  = $this->taoHocSinh($this->taoLop());

        $this->actingAs($gv)->post('/khen-thuong', [
            'hoc_sinh_id' => $hs->id, 'loai' => 'Kỷ luật', 'hinh_thuc' => 'Khiển trách',
            'noi_dung' => 'Vi phạm', 'ngay_quyet_dinh' => '2026-03-10',
        ])->assertForbidden();

        $banGhi = \App\Models\KhenThuongKyLuat::create([
            'hoc_sinh_id' => $hs->id, 'loai' => 'Kỷ luật', 'hinh_thuc' => 'Khiển trách',
            'noi_dung' => 'Vi phạm', 'ngay_quyet_dinh' => '2026-03-10', 'nam_hoc' => '2025-2026',
        ]);
        $this->actingAs($gv)->delete("/khen-thuong/{$banGhi->id}")->assertForbidden();
        $this->assertModelExists($banGhi);
    }
}
