<?php

namespace Tests\Feature\QuanLy;

use App\Models\MonHoc;
use App\Models\PhanCongGiangDay;
use App\Services\GioDayService;

class GiaoVienTest extends QuanLyTestCase
{
    public function test_ho_so_va_tinh_gio_day(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop(['giao_vien_chu_nhiem_id' => $gv->id]);
        $lop2 = $this->taoLop(['ten_lop' => '10A2']);
        $toan = MonHoc::create(['ma_mon' => 'TOAN', 'ten_mon' => 'Toán', 'so_tiet_tuan' => 4, 'khoi' => 'Tất cả']);
        foreach ([$lop, $lop2] as $l) {
            PhanCongGiangDay::create(['giao_vien_id' => $gv->id, 'mon_hoc_id' => $toan->id, 'lop_hoc_id' => $l->id,
                'nam_hoc' => $lop->nam_hoc, 'hoc_ky' => 'Ca năm']);
        }

        $this->actingAs($admin)->put("/giao-vien/{$gv->id}/ho-so", [
            'chuyen_mon' => 'Toán', 'trinh_do' => 'Thạc sĩ', 'chuc_vu' => 'Tổ trưởng',
            'dinh_muc_tiet' => 17, 'giam_tru_tiet' => 3, 'ly_do_giam_tru' => 'Tổ trưởng',
        ])->assertRedirect('/giao-vien');

        // 2 lớp × 4 tiết = 8; định mức thực = 17 − 3 (tổ trưởng) − 4 (chủ nhiệm) = 10 → thiếu 2 tiết/tuần
        $r = app(GioDayService::class)->bangGioDay($lop->nam_hoc)->first(fn ($x) => $x['giao_vien']->id === $gv->id);
        $this->assertSame(10, $r['dinh_muc']);
        $this->assertSame(8, $r['theo_ky']['1']['tiet_tuan']);
        $this->assertSame(-36, $r['theo_ky']['1']['chenh_ky']);    // −2 × 18 tuần

        $this->actingAs($admin)->get("/giao-vien?nam_hoc={$lop->nam_hoc}")->assertOk()->assertSee('Thạc sĩ')->assertSee('-36 tiết');
        $this->actingAs($gv)->get('/giao-vien')->assertForbidden();
    }
}
