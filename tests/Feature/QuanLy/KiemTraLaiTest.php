<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Services\TongKetService;

class KiemTraLaiTest extends QuanLyTestCase
{
    public function test_kiem_tra_lai_va_ren_luyen_he_xet_lai_len_lop(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hocLai = $this->taoHocSinh($lop, 'Học Lại');
        $renHe = $this->taoHocSinh($lop, 'Rèn Hè');
        $mon = collect(['TOAN', 'VAN', 'ANH', 'LY', 'HOA', 'SINH'])->map(fn ($ma) => $this->taoMon($ma));

        // "Học Lại": 4 môn 7.0, 2 môn 4.0 → Chưa đạt (chưa đủ 6 môn >= 5); "Rèn Hè": học tốt nhưng rèn luyện Chưa đạt
        foreach ([[$hocLai, [7, 7, 7, 7, 4, 4], 'Khá'], [$renHe, [8, 8, 8, 8, 8, 8], 'Chưa đạt']] as [$hs, $dsDiem, $renLuyen]) {
            foreach ($mon as $i => $m) {
                foreach (['1', '2'] as $hk) {
                    DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $m->id, 'lop_hoc_id' => $lop->id,
                        'hoc_ky' => $hk, 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => $dsDiem[$i]]);
                }
            }
            HanhKiem::create(['hoc_sinh_id' => $hs->id, 'lop_hoc_id' => $lop->id, 'hoc_ky' => 'Ca năm',
                'nam_hoc' => $lop->nam_hoc, 'xep_loai_hanh_kiem' => $renLuyen]);
        }

        $this->actingAs($admin)->get("/tong-ket/he?lop_hoc_id={$lop->id}")
            ->assertOk()->assertSee('Học Lại')->assertSee('Rèn Hè')->assertSee('HOA');

        $this->actingAs($admin)->post('/tong-ket/he', [
            'lop_hoc_id' => $lop->id,
            'kiem_tra_lai' => [$hocLai->id => [$mon[4]->id => '6,5', $mon[5]->id => '6']],
            'ren_luyen_he' => [$renHe->id => 'Chưa đạt'],
        ])->assertSessionHas('success');

        $ketQua = app(TongKetService::class)->tongKetLop($lop, 'Ca năm')['ket_qua']->keyBy(fn ($r) => $r['hoc_sinh']->ho_ten);
        $this->assertSame('Chưa đạt', $ketQua['Học Lại']['hoc_tap']);          // kết quả lần đầu giữ nguyên
        $this->assertSame('Đạt', $ketQua['Học Lại']['hoc_tap_sau_ktl']);
        $this->assertTrue($ketQua['Học Lại']['len_lop']['dat']);
        $this->assertSame('Lên lớp (sau hè)', $ketQua['Học Lại']['len_lop']['ket_qua']);
        $this->assertSame('Ở lại lớp', $ketQua['Rèn Hè']['len_lop']['ket_qua']);

        // Trang xét lên lớp chọn sẵn học sinh đạt sau kiểm tra lại
        $this->actingAs($admin)->get("/tong-ket/len-lop?lop_hoc_id={$lop->id}")->assertOk()->assertSee('Lên lớp (sau hè)');
    }

    public function test_gvcn_khong_nhap_ket_qua_he(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $this->actingAs($gv)->get('/tong-ket/he')->assertForbidden();
    }
}
