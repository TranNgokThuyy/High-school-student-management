<?php

namespace Tests\Feature\QuanLy;

use App\Models\HanhKiem;

class RenLuyenTest extends QuanLyTestCase
{
    public function test_nhap_ren_luyen_tt22_va_tu_tinh_ca_nam(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop(['giao_vien_chu_nhiem_id' => $gv->id]);
        $hs = $this->taoHocSinh($lop);
        $luu = fn ($hk, $muc) => $this->actingAs($gv)->post('/hanh-kiem/luu', [
            'lop_hoc_id' => $lop->id, 'hoc_ky' => $hk, 'nam_hoc' => $lop->nam_hoc,
            'hanh_kiem' => [$hs->id => ['xep_loai' => $muc, 'so_buoi_hoc' => 90]],
        ]);

        $luu('1', 'Trung bình')->assertSessionHasErrors();   // thang cũ không còn dùng
        $luu('1', 'Chưa đạt')->assertRedirect();
        $luu('2', 'Tốt')->assertRedirect();

        $this->assertSame('Khá', HanhKiem::where('hoc_ky', 'Ca năm')->value('xep_loai_hanh_kiem'));
    }
}
