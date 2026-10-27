<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Models\KhoanThu;

class ThongKeTest extends QuanLyTestCase
{
    public function test_trang_thong_ke(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        foreach (['TOAN', 'VAN', 'ANH', 'LY', 'HOA', 'SINH'] as $ma) {
            $mon = $this->taoMon($ma);
            foreach (['1', '2'] as $hk) {
                DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
                    'hoc_ky' => $hk, 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 9]);
            }
        }
        HanhKiem::create(['hoc_sinh_id' => $hs->id, 'lop_hoc_id' => $lop->id, 'hoc_ky' => 'Ca năm',
            'nam_hoc' => $lop->nam_hoc, 'xep_loai_hanh_kiem' => 'Tốt']);
        KhoanThu::create(['ten' => 'Học phí', 'nam_hoc' => $lop->nam_hoc, 'so_tien' => 500000]);

        $this->actingAs($admin)->get("/bao-cao/thong-ke?nam_hoc={$lop->nam_hoc}")
            ->assertOk()
            ->assertSee('Khối 10 · Tốt: 1 (100,0%)')   // tooltip học tập & rèn luyện
            ->assertSee('100%')                         // tỷ lệ lên lớp
            ->assertSee('Thu học phí');

        // GVCN xem được thống kê nhưng không thấy học phí toàn trường
        $this->actingAs($this->taoUser('giao-vien-chu-nhiem'))->get("/bao-cao/thong-ke?nam_hoc={$lop->nam_hoc}")
            ->assertOk()->assertDontSee('Thu học phí');
        $this->actingAs($this->taoUser('giao-vien-bo-mon'))->get('/bao-cao/thong-ke')->assertForbidden();
    }
}
