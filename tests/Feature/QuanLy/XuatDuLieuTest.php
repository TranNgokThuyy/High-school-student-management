<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XuatDuLieuTest extends QuanLyTestCase
{
    public function test_xuat_du_lieu_toan_truong(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop, 'Học Sinh Xuất');
        $hs->update(['cccd' => '001234567890']);
        $mon = $this->taoMon();
        DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'hoc_ky' => '2', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 8]);

        $this->actingAs($this->taoUser('giao-vien-chu-nhiem'))->get("/bao-cao/xuat-du-lieu?nam_hoc={$lop->nam_hoc}")->assertForbidden();

        $res = $this->actingAs($admin)->get("/bao-cao/xuat-du-lieu?nam_hoc={$lop->nam_hoc}")->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'xd') . '.xlsx';
        file_put_contents($path, $res->streamedContent());
        $wb = IOFactory::load($path);

        $this->assertSame(['Hồ sơ học sinh', 'Kết quả cuối năm', 'Điểm các môn (CN)', 'Biến động', 'Thống kê'], $wb->getSheetNames());
        $hoSo = $wb->getSheetByName('Hồ sơ học sinh');
        $this->assertSame('001234567890', $hoSo->getCell('B4')->getValue());     // giữ số 0 đầu
        $this->assertSame('Học Sinh Xuất', $hoSo->getCell('C4')->getValue());
        $this->assertSame(1, $wb->getSheetByName('Thống kê')->getCell('B4')->getValue());
    }
}
