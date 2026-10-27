<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\PhanCongGiangDay;
use App\Services\TongKetService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HocBaSoDiemTest extends QuanLyTestCase
{
    public function test_hoc_ba_gom_nhieu_nam_hoc(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop10 = $this->taoLop(['nam_hoc' => '2024-2025']);
        $lop11 = $this->taoLop(['ten_lop' => '11A1', 'khoi' => '11', 'nam_hoc' => '2025-2026']);
        $hs = $this->taoHocSinh($lop11);
        $mon = $this->taoMon();
        foreach ([[$lop10, 7.0], [$lop11, 8.0]] as [$lop, $diem]) {
            DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
                'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => $diem]);
        }

        // Năm cũ lấy đúng lớp cũ dù học sinh đã lên lớp
        $namCu = app(TongKetService::class)->ketQuaHocSinh($hs->fresh(), '2024-2025');
        $this->assertSame($lop10->id, $namCu['lop']->id);
        $this->assertEquals(7.0, $namCu['theo_ky']['1']['diem_mon'][$mon->id]);

        $this->actingAs($admin)->get("/hoc-sinh/{$hs->id}/hoc-ba")
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_so_diem_excel_va_phan_quyen(): void
    {
        $gvDay = $this->taoUser('giao-vien-bo-mon');
        $gvKhac = $this->taoUser('giao-vien-bo-mon');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop, 'Nguyễn Sổ Điểm');
        $toan = $this->taoMon('TOAN');
        $gdtc = \App\Models\MonHoc::create(['ma_mon' => 'GDTC', 'ten_mon' => 'GDTC', 'so_tiet_tuan' => 2,
            'khoi' => 'Tất cả', 'hinh_thuc_danh_gia' => 'Nhận xét']);
        PhanCongGiangDay::create(['giao_vien_id' => $gvDay->id, 'mon_hoc_id' => $toan->id, 'lop_hoc_id' => $lop->id,
            'nam_hoc' => $lop->nam_hoc, 'hoc_ky' => 'Ca năm']);
        DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $toan->id, 'lop_hoc_id' => $lop->id, 'hoc_ky' => '1',
            'nam_hoc' => $lop->nam_hoc, 'diem_tx1' => 7.5, 'diem_gk' => 8, 'diem_ck' => 9, 'diem_trung_binh' => 8.42]);
        DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $gdtc->id, 'lop_hoc_id' => $lop->id, 'hoc_ky' => '1',
            'nam_hoc' => $lop->nam_hoc, 'nhan_xet' => ['tx1' => 'Đ', 'ck' => 'Đ'], 'ket_qua_nhan_xet' => 'Đạt']);

        $this->actingAs($gvKhac)->get("/lop-hoc/{$lop->id}/so-diem")->assertForbidden();

        $response = $this->actingAs($gvDay)->get("/lop-hoc/{$lop->id}/so-diem")->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'sd') . '.xlsx';
        file_put_contents($path, $response->streamedContent());
        $wb = IOFactory::load($path);

        $this->assertSame(['Tổng hợp', 'GDTC', 'TOAN'], $wb->getSheetNames());
        $toanSheet = $wb->getSheetByName('TOAN');
        $this->assertSame('Nguyễn Sổ Điểm', $toanSheet->getCell('C7')->getValue());
        $this->assertEquals(7.5, $toanSheet->getCell('D7')->getValue());   // TX1 HK1
        $this->assertEquals(8.42, $toanSheet->getCell('J7')->getValue());  // ĐTB HK1
        $this->assertSame('Đạt', $wb->getSheetByName('GDTC')->getCell('J7')->getValue());
    }
}
