<?php

namespace Tests\Feature\QuanLy;

use App\Models\HocSinh;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NhapExcelTest extends QuanLyTestCase
{
    private function taoFileXlsx(array $rows, callable $tuyChinh = null): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);
        if ($tuyChinh) {
            $tuyChinh($spreadsheet->getActiveSheet());
        }
        $path = tempnam(sys_get_temp_dir(), 'hs') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'danh-sach.xlsx', null, null, true);
    }

    public function test_nhap_xlsx_tieu_de_tieng_viet_va_o_ngay_excel(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();

        $file = $this->taoFileXlsx([
            ['STT', 'Họ và tên', 'Ngày sinh', 'Giới tính', 'Lớp', 'Năm học', 'SĐT', 'Số CCCD'],
            [1, 'Trần Thị Mai', 40405, 'nữ', '10A1', '2025-2026', 912345678, 12345678901],
            [2, 'Lê Văn Nam', '01/02/2010', 'Nam', '10A1', '2025-2026', null, null],
            [3, 'Sai Lớp', '01/02/2010', 'Nam', '99Z9', '2025-2026', null, null],
        ], fn ($sheet) => $sheet->getStyle('C2')->getNumberFormat()->setFormatCode('dd/mm/yyyy'));

        $this->actingAs($admin)->post('/hoc-sinh-nhap-excel', ['file' => $file])
            ->assertRedirect('/hoc-sinh-nhap-excel')
            ->assertSessionHas('import_result', fn ($r) => $r['imported'] === 2 && count($r['errors']) === 1 && $r['errors'][0]['line'] === 4);

        $mai = HocSinh::where('ho_ten', 'Trần Thị Mai')->first();
        $this->assertSame('2010-08-15', $mai->ngay_sinh->toDateString());
        $this->assertSame('Nữ', $mai->gioi_tinh);
        $this->assertSame('0912345678', $mai->so_dien_thoai);
        $this->assertSame('012345678901', $mai->cccd);
        $this->assertSame(2, $lop->fresh()->si_so);
    }

    public function test_nhap_lai_cung_tep_khong_tao_trung(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $this->taoLop();
        $rows = [['ho_ten', 'ngay_sinh', 'gioi_tinh', 'ten_lop', 'nam_hoc'], ['Phạm An', '05/05/2010', 'Nam', '10A1', '2025-2026']];

        $this->actingAs($admin)->post('/hoc-sinh-nhap-excel', ['file' => $this->taoFileXlsx($rows)]);
        $this->actingAs($admin)->post('/hoc-sinh-nhap-excel', ['file' => $this->taoFileXlsx($rows)])
            ->assertSessionHas('import_result', fn ($r) => $r['imported'] === 0 && $r['skipped'] === 1);

        $this->assertSame(1, HocSinh::count());
    }

    public function test_nhap_csv_dau_cham_phay(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $this->taoLop();
        $path = tempnam(sys_get_temp_dir(), 'hs') . '.csv';
        file_put_contents($path, "\xEF\xBB\xBFHọ và tên;Ngày sinh;Giới tính;Lớp;Năm học\nĐỗ Hà;03/03/2010;Nữ;10A1;2025-2026\n");

        $this->actingAs($admin)->post('/hoc-sinh-nhap-excel', ['file' => new UploadedFile($path, 'ds.csv', null, null, true)])
            ->assertSessionHas('import_result', fn ($r) => $r['imported'] === 1);
    }

    public function test_file_mau_xlsx_nhap_lai_duoc(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $this->taoLop();

        $response = $this->actingAs($admin)->get('/hoc-sinh-mau-excel')->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'mau') . '.xlsx';
        file_put_contents($path, $response->streamedContent());

        $this->actingAs($admin)->post('/hoc-sinh-nhap-excel', ['file' => new UploadedFile($path, 'mau.xlsx', null, null, true)])
            ->assertSessionHas('import_result', fn ($r) => $r['imported'] === 1);

        $this->assertSame('012345678901', HocSinh::first()->cccd);
    }

    public function test_giao_vien_khong_nhap_duoc(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');

        $this->actingAs($gv)->get('/hoc-sinh-nhap-excel')->assertForbidden();
    }
}
