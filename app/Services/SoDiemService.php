<?php
namespace App\Services;

use App\Models\DiemSo;
use App\Models\LopHoc;
use App\Models\MonHoc;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sổ theo dõi và đánh giá học sinh theo lớp (sổ điểm điện tử):
 * mỗi môn một trang tính (điểm từng lần đánh giá HK1, HK2, cả năm) và trang "Tổng hợp".
 */
class SoDiemService
{
    public function __construct(private TongKetService $tongKet) {}

    public function taoSpreadsheet(LopHoc $lop): Spreadsheet
    {
        $lop->loadMissing('giaoVienChuNhiem');
        $caNam = $this->tongKet->tongKetLop($lop, 'Ca năm');
        $hk1 = $this->tongKet->tongKetLop($lop, '1')['ket_qua']->keyBy(fn ($r) => $r['hoc_sinh']->id);
        $hk2 = $this->tongKet->tongKetLop($lop, '2')['ket_qua']->keyBy(fn ($r) => $r['hoc_sinh']->id);
        $hocSinh = $caNam['ket_qua']->pluck('hoc_sinh')->values();

        $monHoc = MonHoc::whereIn('id', DiemSo::where('lop_hoc_id', $lop->id)->distinct()->pluck('mon_hoc_id'))
            ->orderBy('ten_mon')->get();

        $diem = DiemSo::where('lop_hoc_id', $lop->id)->where('nam_hoc', $lop->nam_hoc)->get()
            ->keyBy(fn ($d) => "{$d->hoc_sinh_id}-{$d->mon_hoc_id}-{$d->hoc_ky}");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // Trang tổng hợp
        $tongHop = $spreadsheet->createSheet()->setTitle('Tổng hợp');
        $this->tieuDe($tongHop, $lop, 'TỔNG HỢP KẾT QUẢ ĐÁNH GIÁ', 12);
        $tongHop->fromArray([
            ['STT', 'Mã HS', 'Họ và tên', 'Học tập HK1', 'Rèn luyện HK1', 'Học tập HK2', 'Rèn luyện HK2',
             'Học tập CN', 'Rèn luyện CN', 'Số buổi nghỉ', 'Danh hiệu', 'Kết quả xét cuối năm'],
        ], null, 'A5');
        $dong = 6;
        foreach ($caNam['ket_qua']->values() as $i => $r) {
            $id = $r['hoc_sinh']->id;
            $tongHop->fromArray([[
                $i + 1, $r['hoc_sinh']->ma_hoc_sinh, $r['hoc_sinh']->ho_ten,
                $hk1[$id]['hoc_tap'] ?? '', $hk1[$id]['ren_luyen'] ?? '',
                $hk2[$id]['hoc_tap'] ?? '', $hk2[$id]['ren_luyen'] ?? '',
                $r['hoc_tap'] ?? 'Chưa đủ điểm', $r['ren_luyen'] ?? '', $r['so_buoi_nghi'],
                $r['danh_hieu'] ?? '', $r['len_lop']['ket_qua'] ?? '',
            ]], null, "A{$dong}");
            $dong++;
        }
        $this->dinhDangBang($tongHop, 'A5:L' . max(5, $dong - 1));

        // Mỗi môn một trang
        foreach ($monHoc as $mon) {
            $sheet = $spreadsheet->createSheet()->setTitle(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $mon->ten_mon), 0, 31));
            $nhanXet = $mon->laMonNhanXet();
            $this->tieuDe($sheet, $lop, 'MÔN ' . mb_strtoupper($mon->ten_mon) . ($nhanXet ? ' (đánh giá bằng nhận xét)' : ''), 18);

            $cot = ['TX1', 'TX2', 'TX3', 'TX4', 'GK', 'CK', $nhanXet ? 'KQ' : 'ĐTB'];
            $sheet->fromArray([['STT', 'Mã HS', 'Họ và tên', 'Học kỳ I', '', '', '', '', '', '', 'Học kỳ II', '', '', '', '', '', '', 'Cả năm']], null, 'A5');
            $sheet->fromArray([['', '', '', ...$cot, ...$cot, $nhanXet ? 'KQ' : 'ĐTB']], null, 'A6');
            foreach (['A5:A6', 'B5:B6', 'C5:C6', 'D5:J5', 'K5:Q5', 'R5:R6'] as $vung) {
                $sheet->mergeCells($vung);
            }

            // Môn lựa chọn: chỉ liệt kê học sinh học môn này
            $hocSinhMon = $hocSinh->filter(fn ($hs) => array_key_exists(
                $mon->id,
                $caNam['ket_qua']->first(fn ($r) => $r['hoc_sinh']->id === $hs->id)['diem_mon']
            ))->values();

            $dong = 7;
            foreach ($hocSinhMon as $i => $hs) {
                $giaTri = [$i + 1, $hs->ma_hoc_sinh, $hs->ho_ten];
                foreach (['1', '2'] as $hk) {
                    $d = $diem["{$hs->id}-{$mon->id}-{$hk}"] ?? null;
                    $giaTri = array_merge($giaTri, $nhanXet
                        ? [...array_map(fn ($c) => $d?->nhan_xet[$c] ?? '', DiemSo::COT_NHAN_XET), $d?->ket_qua_nhan_xet ?? '']
                        : [$d?->diem_tx1, $d?->diem_tx2, $d?->diem_tx3, $d?->diem_tx4, $d?->diem_gk, $d?->diem_ck, $d?->diem_trung_binh]);
                }
                $giaTri[] = $caNam['ket_qua']->first(fn ($r) => $r['hoc_sinh']->id === $hs->id)['diem_mon'][$mon->id] ?? '';
                $sheet->fromArray([array_map(fn ($v) => is_numeric($v) ? (float) $v : $v, $giaTri)], null, "A{$dong}");
                $dong++;
            }
            $this->dinhDangBang($sheet, 'A5:R' . max(6, $dong - 1));
            $sheet->getStyle('D7:R' . max(7, $dong - 1))->getNumberFormat()->setFormatCode('0.0#');
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function tieuDe(Worksheet $sheet, LopHoc $lop, string $tieuDe, int $soCot): void
    {
        $cotCuoi = chr(ord('A') + $soCot - 1);
        $sheet->setCellValue('A1', mb_strtoupper(config('app.ten_truong')));
        $sheet->setCellValue('A2', "SỔ THEO DÕI VÀ ĐÁNH GIÁ HỌC SINH — {$tieuDe}");
        $sheet->setCellValue('A3', "Lớp {$lop->ten_lop} — Năm học {$lop->nam_hoc}"
            . ($lop->giaoVienChuNhiem ? " — GVCN: {$lop->giaoVienChuNhiem->name}" : ''));
        foreach ([1, 2, 3] as $d) {
            $sheet->mergeCells("A{$d}:{$cotCuoi}{$d}");
        }
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getFont()->setSize(13);
        $sheet->getStyle("A2:{$cotCuoi}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function dinhDangBang(Worksheet $sheet, string $vung): void
    {
        $style = $sheet->getStyle($vung);
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        [$dau] = explode(':', $vung);
        $dongDau = (int) preg_replace('/\D/', '', $dau);
        $cotCuoi = preg_replace('/\d/', '', explode(':', $vung)[1]);
        $dongTieuDe = $sheet->getCell("A" . ($dongDau + 1))->getValue() === '' || $sheet->getCell("A" . ($dongDau + 1))->getValue() === null
            ? $dongDau + 1 : $dongDau;
        $sheet->getStyle("A{$dongDau}:{$cotCuoi}{$dongTieuDe}")->getFont()->setBold(true);
        $sheet->getStyle("A{$dongDau}:{$cotCuoi}{$dongTieuDe}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F0FE');
        $sheet->getStyle('C' . ($dongTieuDe + 1) . ':C' . $sheet->getHighestRow())
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(26);
    }
}
