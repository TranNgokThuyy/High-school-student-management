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
}
