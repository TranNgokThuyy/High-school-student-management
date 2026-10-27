<?php
namespace App\Services;

use App\Models\BienDongHocSinh;
use App\Models\LopHoc;
use App\Support\Diem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Xuất dữ liệu toàn trường của một năm học ra Excel để đối chiếu / nhập lên CSDL ngành.
 * Đây là tệp tổng hợp theo cấu trúc phổ biến, không phải mẫu chính thức của Sở / Bộ.
 */
class XuatDuLieuService
{
    public function __construct(private TongKetService $tongKet) {}

    public function taoSpreadsheet(string $namHoc): Spreadsheet
    {
        $lopHoc = LopHoc::where('nam_hoc', $namHoc)->orderBy('khoi')->orderBy('ten_lop')->get();

        $hoSo = [];
        $ketQua = [];
        $diemMon = [];
        $tenMon = [];
        $thongKe = [];

        foreach ($lopHoc as $lop) {
            ['mon_hoc' => $monHoc, 'ket_qua' => $kq] = $this->tongKet->tongKetLop($lop, 'Ca năm');
            foreach ($monHoc as $m) {
                $tenMon[$m->id] = $m->ten_mon;
            }

            foreach ($kq as $r) {
                $hs = $r['hoc_sinh'];
                $hoSo[] = [
                    $hs->ma_hoc_sinh, $hs->cccd, $hs->ho_ten, $hs->ngay_sinh?->format('d/m/Y'), $hs->gioi_tinh,
                    $hs->dan_toc, $hs->ton_giao, $lop->ten_lop, $lop->khoi, $hs->dia_chi_thuong_tru,
                    $hs->ho_ten_cha, $hs->so_dien_thoai_cha, $hs->ho_ten_me, $hs->so_dien_thoai_me, $hs->trang_thai,
                ];
                $hocTap = $r['hoc_tap_sau_ktl'] ?? $r['hoc_tap'];
                $renLuyen = $r['ren_luyen_sau_he'] ?? $r['ren_luyen'];
                $ketQua[] = [
                    $hs->ma_hoc_sinh, $hs->ho_ten, $lop->ten_lop, $r['hoc_tap'] ?? 'Chưa đủ điểm', $r['ren_luyen'] ?? '',
                    $r['hoc_tap_sau_ktl'] ?? '', $r['ren_luyen_sau_he'] ?? '', $r['so_buoi_nghi'],
                    $r['danh_hieu'] ?? '', $r['len_lop']['ket_qua'] ?? '',
                ];
                $diemMon[] = ['ma' => $hs->ma_hoc_sinh, 'ten' => $hs->ho_ten, 'lop' => $lop->ten_lop, 'diem' => $r['diem_mon']];

                $tk = &$thongKe[$lop->khoi];
                $tk['si_so'] = ($tk['si_so'] ?? 0) + 1;
                $tk['nu'] = ($tk['nu'] ?? 0) + ($hs->gioi_tinh === 'Nữ' ? 1 : 0);
                $tk['hoc_tap'][$hocTap ?? 'Chưa đủ điểm'] = ($tk['hoc_tap'][$hocTap ?? 'Chưa đủ điểm'] ?? 0) + 1;
                $tk['ren_luyen'][$renLuyen ?? 'Chưa xếp loại'] = ($tk['ren_luyen'][$renLuyen ?? 'Chưa xếp loại'] ?? 0) + 1;
                $tk['len_lop'] = ($tk['len_lop'] ?? 0) + (($r['len_lop']['dat'] ?? false) ? 1 : 0);
                unset($tk);
            }
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->trangTinh($spreadsheet, 'Hồ sơ học sinh', [
            'Mã HS', 'Mã định danh / CCCD', 'Họ và tên', 'Ngày sinh', 'Giới tính', 'Dân tộc', 'Tôn giáo', 'Lớp', 'Khối',
            'Địa chỉ thường trú', 'Họ tên cha', 'SĐT cha', 'Họ tên mẹ', 'SĐT mẹ', 'Trạng thái',
        ], $hoSo, $namHoc, textCols: ['A', 'B', 'L', 'N']);

        $this->trangTinh($spreadsheet, 'Kết quả cuối năm', [
            'Mã HS', 'Họ và tên', 'Lớp', 'Học tập CN', 'Rèn luyện CN', 'Học tập sau KTL', 'Rèn luyện sau hè',
            'Số buổi nghỉ', 'Danh hiệu', 'Kết quả xét cuối năm',
        ], $ketQua, $namHoc, textCols: ['A']);

        asort($tenMon);
        $this->trangTinh($spreadsheet, 'Điểm các môn (CN)', ['Mã HS', 'Họ và tên', 'Lớp', ...array_values($tenMon)],
            array_map(fn ($d) => [$d['ma'], $d['ten'], $d['lop'],
                ...array_map(fn ($id) => array_key_exists($id, $d['diem']) ? $this->giaTri($d['diem'][$id]) : '', array_keys($tenMon))],
                $diemMon),
            $namHoc, textCols: ['A']);

        $this->trangTinh($spreadsheet, 'Biến động', ['Ngày', 'Mã HS', 'Họ và tên', 'Lớp', 'Loại', 'Số QĐ', 'Trường đi / đến', 'Lý do'],
            BienDongHocSinh::with(['hocSinh', 'lopHoc'])->where('nam_hoc', $namHoc)->orderBy('ngay')->get()
                ->map(fn ($b) => [$b->ngay->format('d/m/Y'), $b->hocSinh?->ma_hoc_sinh, $b->hocSinh?->ho_ten,
                    $b->lopHoc?->ten_lop, $b->loai, $b->so_quyet_dinh, $b->truong, $b->ly_do])->all(),
            $namHoc, textCols: ['B']);

        ksort($thongKe);
        $mucHocTap = ['Tốt', 'Khá', 'Đạt', 'Chưa đạt', 'Chưa đủ điểm'];
        $mucRenLuyen = ['Tốt', 'Khá', 'Đạt', 'Chưa đạt', 'Chưa xếp loại'];
        $this->trangTinh($spreadsheet, 'Thống kê', [
            'Khối', 'Sĩ số', 'Nữ', ...array_map(fn ($m) => "HT: {$m}", $mucHocTap), ...array_map(fn ($m) => "RL: {$m}", $mucRenLuyen), 'Lên lớp',
        ], collect($thongKe)->map(fn ($tk, $khoi) => [
            $khoi, $tk['si_so'], $tk['nu'],
            ...array_map(fn ($m) => $tk['hoc_tap'][$m] ?? 0, $mucHocTap),
            ...array_map(fn ($m) => $tk['ren_luyen'][$m] ?? 0, $mucRenLuyen),
            $tk['len_lop'],
        ])->values()->all(), $namHoc);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function giaTri(float|string|null $ketQua): float|string
    {
        return is_float($ketQua) ? $ketQua : Diem::hienThi($ketQua);
    }

    private function trangTinh(Spreadsheet $spreadsheet, string $ten, array $tieuDe, array $dong, string $namHoc, array $textCols = []): Worksheet
    {
        $sheet = $spreadsheet->createSheet()->setTitle($ten);
        $sheet->setCellValue('A1', mb_strtoupper(config('app.ten_truong')) . " — {$ten} — Năm học {$namHoc}");
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->fromArray([$tieuDe], null, 'A3');

        // Cột mã / số điện thoại ghi dạng chữ để giữ số 0 đầu
        foreach ($dong as $i => $giaTri) {
            foreach ($giaTri as $c => $v) {
                $o = $sheet->getCell([$c + 1, 4 + $i]);
                in_array($o->getColumn(), $textCols, true) && $v !== null
                    ? $o->setValueExplicit((string) $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING)
                    : $o->setValue($v);
            }
        }

        $cotCuoi = $sheet->getHighestColumn();
        $sheet->getStyle("A3:{$cotCuoi}3")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$cotCuoi}3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F0FE');
        $sheet->setAutoFilter("A3:{$cotCuoi}" . max(3, 3 + count($dong)));
        $sheet->freezePane('A4');
        foreach (range(1, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cotCuoi)) as $c) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        return $sheet;
    }
}
