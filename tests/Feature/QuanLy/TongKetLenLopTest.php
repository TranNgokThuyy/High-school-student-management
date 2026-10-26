<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;

class TongKetLenLopTest extends QuanLyTestCase
{
    private function nhapDiemCaNam(HocSinh $hs, LopHoc $lop, float $diem, string $hanhKiem): void
    {
        foreach (MonHoc::all() as $mon) {
            foreach (['1', '2'] as $hk) {
                DiemSo::create([
                    'hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
                    'hoc_ky' => $hk, 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => $diem,
                ]);
            }
        }
        HanhKiem::create([
            'hoc_sinh_id' => $hs->id, 'lop_hoc_id' => $lop->id, 'hoc_ky' => 'Ca năm',
            'nam_hoc' => $lop->nam_hoc, 'xep_loai_hanh_kiem' => $hanhKiem,
        ]);
    }

    public function test_tong_ket_va_len_lop(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        foreach (['TOAN', 'VAN', 'ANH', 'LY', 'HOA', 'SINH'] as $ma) {
            $this->taoMon($ma);
        }

        $lop10  = $this->taoLop();
        $lop11  = $this->taoLop(['ten_lop' => '11A1', 'khoi' => '11', 'nam_hoc' => '2026-2027']);
        $lop10b = $this->taoLop(['ten_lop' => '10A1', 'nam_hoc' => '2026-2027']);

        $gioi = $this->taoHocSinh($lop10, 'Giỏi');
        $yeu  = $this->taoHocSinh($lop10, 'Yếu');
        $this->nhapDiemCaNam($gioi, $lop10, 9.2, 'Tốt');
        $this->nhapDiemCaNam($yeu, $lop10, 3.0, 'Khá');

        $this->actingAs($admin)->get("/tong-ket?lop_hoc_id={$lop10->id}&hoc_ky=Ca năm")
            ->assertOk()
            ->assertSee('Học sinh Xuất sắc')
            ->assertSee('Chưa được lên lớp');

        $this->actingAs($admin)->get("/tong-ket/len-lop?lop_hoc_id={$lop10->id}")->assertOk();

        $this->actingAs($admin)->post('/tong-ket/len-lop', [
            'lop_hoc_id' => $lop10->id, 'lop_dich_id' => $lop11->id, 'lop_o_lai_id' => $lop10b->id,
            'hoc_sinh_ids' => [$gioi->id],
        ])->assertRedirect();

        $this->assertEquals($lop11->id, $gioi->fresh()->lop_hoc_id);
        $this->assertEquals($lop10b->id, $yeu->fresh()->lop_hoc_id);
        $this->assertSame(1, $lop11->fresh()->si_so);

        // Tổng kết năm cũ vẫn giữ học sinh đã chuyển lớp
        $this->actingAs($admin)->get("/tong-ket?lop_hoc_id={$lop10->id}")->assertSee('Giỏi')->assertSee('đã chuyển');
    }

    public function test_khong_cho_chuyen_vao_lop_sai_khoi(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop10 = $this->taoLop();
        $lopSai = $this->taoLop(['ten_lop' => '12A1', 'khoi' => '12', 'nam_hoc' => '2026-2027']);
        $hs = $this->taoHocSinh($lop10);

        $this->actingAs($admin)->post('/tong-ket/len-lop', [
            'lop_hoc_id' => $lop10->id, 'lop_dich_id' => $lopSai->id, 'hoc_sinh_ids' => [$hs->id],
        ])->assertSessionHas('error');

        $this->assertEquals($lop10->id, $hs->fresh()->lop_hoc_id);
    }

    public function test_khoi_12_cong_nhan_tot_nghiep(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop12 = $this->taoLop(['ten_lop' => '12A1', 'khoi' => '12']);
        $hs = $this->taoHocSinh($lop12);

        $this->actingAs($admin)->post('/tong-ket/len-lop', [
            'lop_hoc_id' => $lop12->id, 'hoc_sinh_ids' => [$hs->id],
        ])->assertRedirect();

        $this->assertSame('Đã tốt nghiệp', $hs->fresh()->trang_thai);
    }

    public function test_gvcn_khong_xem_tong_ket_lop_khac(): void
    {
        $gv  = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop();

        $this->actingAs($gv)->get("/tong-ket?lop_hoc_id={$lop->id}")->assertForbidden();
        $this->actingAs($gv)->get('/tong-ket/len-lop')->assertForbidden();
    }
}
