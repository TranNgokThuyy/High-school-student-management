<?php

namespace Tests\Feature\QuanLy;

use App\Models\PhanCongGiangDay;

class NhapDiemTest extends QuanLyTestCase
{
    public function test_giao_vien_chi_nhap_diem_mon_duoc_phan_cong(): void
    {
        $gv   = $this->taoUser('giao-vien-bo-mon');
        $lop  = $this->taoLop();
        $hs   = $this->taoHocSinh($lop);
        $toan = $this->taoMon('TOAN');
        $van  = $this->taoMon('VAN');

        PhanCongGiangDay::create([
            'giao_vien_id' => $gv->id, 'mon_hoc_id' => $toan->id, 'lop_hoc_id' => $lop->id,
            'nam_hoc' => '2025-2026', 'hoc_ky' => 'Ca năm',
        ]);

        $duLieu = fn($monId) => [
            'lop_hoc_id' => $lop->id, 'mon_hoc_id' => $monId, 'hoc_ky' => '1', 'nam_hoc' => '2025-2026',
            'diem' => [$hs->id => ['diem_tx1' => 8, 'diem_gk' => 7, 'diem_ck' => 9]],
        ];

        $this->actingAs($gv)->post('/diem-so/luu-diem', $duLieu($toan->id))->assertRedirect();
        $this->assertDatabaseHas('diem_so', ['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $toan->id]);

        $this->actingAs($gv)->post('/diem-so/luu-diem', $duLieu($van->id))->assertForbidden();
        $this->assertDatabaseMissing('diem_so', ['mon_hoc_id' => $van->id]);
    }

    public function test_khong_luu_diem_cho_hoc_sinh_ngoai_lop(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop   = $this->taoLop();
        $lopKhac = $this->taoLop(['ten_lop' => '10A2']);
        $hsKhac  = $this->taoHocSinh($lopKhac);
        $mon   = $this->taoMon();

        $this->actingAs($admin)->post('/diem-so/luu-diem', [
            'lop_hoc_id' => $lop->id, 'mon_hoc_id' => $mon->id, 'hoc_ky' => '1', 'nam_hoc' => '2025-2026',
            'diem' => [$hsKhac->id => ['diem_tx1' => 8]],
        ])->assertRedirect();

        $this->assertDatabaseCount('diem_so', 0);
    }

    public function test_quan_tri_vien_phan_cong_nhieu_lop_va_chan_trung(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gv1 = $this->taoUser('giao-vien-bo-mon');
        $gv2 = $this->taoUser('giao-vien-bo-mon');
        $lop1 = $this->taoLop();
        $lop2 = $this->taoLop(['ten_lop' => '10A2']);
        $mon = $this->taoMon();

        $this->actingAs($admin)->post('/phan-cong', [
            'giao_vien_id' => $gv1->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_ids' => [$lop1->id, $lop2->id], 'hoc_ky' => 'Ca năm',
        ])->assertRedirect('/phan-cong');
        $this->assertDatabaseCount('phan_cong_giang_day', 2);

        // Môn này ở lớp 1 đã có giáo viên → bỏ qua
        $this->actingAs($admin)->post('/phan-cong', [
            'giao_vien_id' => $gv2->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_ids' => [$lop1->id], 'hoc_ky' => '1',
        ])->assertSessionHas('error');
        $this->assertDatabaseCount('phan_cong_giang_day', 2);
    }

    public function test_nhap_mon_nhan_xet_va_tong_ket(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $gdtc = \App\Models\MonHoc::create(['ma_mon' => 'GDTC', 'ten_mon' => 'GDTC', 'so_tiet_tuan' => 2,
            'khoi' => 'Tất cả', 'hinh_thuc_danh_gia' => 'Nhận xét']);

        foreach (['1' => 'Đ', '2' => 'CĐ'] as $hk => $cuoiKy) {
            $this->actingAs($admin)->post('/diem-so/luu-diem', [
                'lop_hoc_id' => $lop->id, 'mon_hoc_id' => $gdtc->id, 'hoc_ky' => $hk, 'nam_hoc' => $lop->nam_hoc,
                'diem' => [$hs->id => ['nhan_xet' => ['tx1' => 'Đ', 'tx2' => 'Đ', 'gk' => 'Đ', 'ck' => $cuoiKy]]],
            ])->assertRedirect();
        }

        $hk1 = \App\Models\DiemSo::where('hoc_ky', '1')->first();
        $this->assertSame('Đạt', $hk1->ket_qua_nhan_xet);
        $this->assertNull($hk1->diem_trung_binh);

        // Cả năm lấy kết quả học kỳ II (Chưa đạt)
        $ketQua = app(\App\Services\TongKetService::class)->tongKetLop($lop, 'Ca năm')['ket_qua']->first();
        $this->assertSame('Chưa đạt', $ketQua['diem_mon'][$gdtc->id]);

        $this->actingAs($admin)->get("/diem-so/nhap-diem?lop_hoc_id={$lop->id}&mon_hoc_id={$gdtc->id}&hoc_ky=1&nam_hoc={$lop->nam_hoc}")
            ->assertOk()->assertSee('Môn đánh giá bằng nhận xét', false);
        $this->actingAs($admin)->get("/tong-ket?lop_hoc_id={$lop->id}")->assertOk()->assertSee('Chưa đạt');
    }
}
