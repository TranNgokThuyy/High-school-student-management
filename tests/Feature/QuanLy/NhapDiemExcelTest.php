<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\PhanCongGiangDay;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NhapDiemExcelTest extends QuanLyTestCase
{
    public function test_tai_mau_dien_diem_va_tai_len(): void
    {
        $gv = $this->taoUser('giao-vien-bo-mon');
        $lop = $this->taoLop();
        $an = $this->taoHocSinh($lop, 'An');
        $binh = $this->taoHocSinh($lop, 'Bình');
        $mon = $this->taoMon();
        PhanCongGiangDay::create(['giao_vien_id' => $gv->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'nam_hoc' => $lop->nam_hoc, 'hoc_ky' => 'Ca năm']);
        // An đã có điểm thường xuyên từ trước
        DiemSo::create(['hoc_sinh_id' => $an->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_tx1' => 9, 'diem_tx2' => 7]);
        $thamSo = ['lop_hoc_id' => $lop->id, 'mon_hoc_id' => $mon->id, 'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc];

        $mau = $this->actingAs($gv)->get('/diem-so/mau-excel?' . http_build_query($thamSo))->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'mau') . '.xlsx';
        file_put_contents($path, $mau->streamedContent());

        $wb = IOFactory::load($path);
        $sheet = $wb->getSheet(0);
        $this->assertEquals(9, $sheet->getCell('C5')->getValue());   // điểm có sẵn được điền vào mẫu

        // Giáo viên chỉ nhập giữa kỳ, cuối kỳ cho An; Bình có một ô sai
        $sheet->setCellValue('G5', 8)->setCellValue('H5', '8,5');
        $sheet->setCellValue('C6', 7)->setCellValue('H6', 12);
        (new Xlsx($wb))->save($path);

        $this->actingAs($gv)->post('/diem-so/nhap-excel', $thamSo + ['file' => new UploadedFile($path, 'diem.xlsx', null, null, true)])
            ->assertRedirect()->assertSessionHas('loi_excel', fn ($loi) => count($loi) === 1 && str_contains($loi[0], 'Bình'));

        $diemAn = DiemSo::where('hoc_sinh_id', $an->id)->first();
        $this->assertEquals(9, $diemAn->diem_tx1);         // ô không đổi vẫn giữ
        $this->assertEquals(8.5, $diemAn->diem_ck);        // dấu phẩy thập phân được chấp nhận
        $this->assertNotNull($diemAn->diem_trung_binh);
        $this->assertNull(DiemSo::where('hoc_sinh_id', $binh->id)->first()); // dòng lỗi không lưu

        // Tệp của lớp/môn khác bị chặn
        $lopKhac = $this->taoLop(['ten_lop' => '10A2']);
        PhanCongGiangDay::create(['giao_vien_id' => $gv->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lopKhac->id,
            'nam_hoc' => $lop->nam_hoc, 'hoc_ky' => 'Ca năm']);
        $this->actingAs($gv)->post('/diem-so/nhap-excel', ['lop_hoc_id' => $lopKhac->id] + $thamSo
            + ['file' => new UploadedFile($path, 'diem.xlsx', null, null, true)])->assertSessionHas('error');
    }

    public function test_giao_vien_chua_phan_cong_khong_tai_duoc(): void
    {
        $gv = $this->taoUser('giao-vien-bo-mon');
        $lop = $this->taoLop();
        $mon = $this->taoMon();

        $this->actingAs($gv)->get("/diem-so/mau-excel?lop_hoc_id={$lop->id}&mon_hoc_id={$mon->id}&hoc_ky=1&nam_hoc={$lop->nam_hoc}")
            ->assertForbidden();
    }
}
