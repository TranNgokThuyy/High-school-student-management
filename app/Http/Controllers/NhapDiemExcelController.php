<?php
namespace App\Http\Controllers;

use App\Models\DangKyMon;
use App\Models\DiemSo;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;
use App\Models\PhanCongGiangDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

// Giáo viên tải mẫu điểm của lớp / môn / học kỳ, nhập trên Excel rồi tải lên
class NhapDiemExcelController extends Controller
{
    private const COT = ['TX1' => 'tx1', 'TX2' => 'tx2', 'TX3' => 'tx3', 'TX4' => 'tx4', 'GK' => 'gk', 'CK' => 'ck'];

    private function thongTin(Request $request): array
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'mon_hoc_id' => 'required|exists:mon_hoc,id',
            'hoc_ky'     => 'required|in:1,2',
            'nam_hoc'    => 'required|string',
        ]);

        abort_unless(PhanCongGiangDay::duocNhapDiem(
            auth()->user(), (int) $request->lop_hoc_id, (int) $request->mon_hoc_id, $request->hoc_ky, $request->nam_hoc
        ), 403, 'Bạn chưa được phân công giảng dạy môn này ở lớp này!');

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $mon = MonHoc::findOrFail($request->mon_hoc_id);
        $hocSinh = HocSinh::whereIn('id', DangKyMon::hocSinhHocMon(
            $mon, $lop->hocSinh()->pluck('id')->map(fn ($id) => (int) $id)->all(), $request->nam_hoc
        ))->orderBy('ho_ten')->get();

        return [$lop, $mon, $hocSinh];
    }

    public function taiMau(Request $request)
    {
        [$lop, $mon, $hocSinh] = $this->thongTin($request);

        $diem = DiemSo::where(['lop_hoc_id' => $lop->id, 'mon_hoc_id' => $mon->id,
            'hoc_ky' => $request->hoc_ky, 'nam_hoc' => $request->nam_hoc])->get()->keyBy('hoc_sinh_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Nhập điểm');
        $sheet->setCellValue('A1', "Lớp {$lop->ten_lop} — {$mon->ten_mon} — Học kỳ {$request->hoc_ky} — Năm học {$request->nam_hoc}");
        $sheet->setCellValue('A2', $mon->laMonNhanXet()
            ? 'Môn đánh giá bằng nhận xét: nhập Đ hoặc CĐ. Ô để trống sẽ giữ nguyên kết quả cũ.'
            : 'Nhập điểm thang 10 (VD: 8.5). Ô để trống sẽ giữ nguyên điểm cũ. Không sửa cột Mã HS.');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        $sheet->fromArray([['Mã HS', 'Họ và tên', ...array_keys(self::COT)]], null, 'A4');
        $sheet->getStyle('A4:H4')->getFont()->setBold(true);
        $sheet->getStyle('A4:H4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F0FE');

        foreach ($hocSinh->values() as $i => $hs) {
            $dong = 5 + $i;
            $d = $diem[$hs->id] ?? null;
            $sheet->getCell("A{$dong}")->setValueExplicit($hs->ma_hoc_sinh, DataType::TYPE_STRING);
            $sheet->setCellValue("B{$dong}", $hs->ho_ten);
            foreach (array_values(self::COT) as $j => $cot) {
                $giaTri = $mon->laMonNhanXet() ? ($d?->nhan_xet[$cot] ?? null) : $d?->{"diem_{$cot}"};
                if ($giaTri !== null) {
                    $sheet->setCellValue([3 + $j, $dong], is_numeric($giaTri) ? (float) $giaTri : $giaTri);
                }
            }
        }
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(28);

        // Thông tin ẩn để kiểm tra tệp tải lên đúng lớp / môn / học kỳ
        $an = $spreadsheet->createSheet()->setTitle('_thong_tin');
        $an->fromArray([['lop_hoc_id', 'mon_hoc_id', 'hoc_ky', 'nam_hoc'], [$lop->id, $mon->id, $request->hoc_ky, $request->nam_hoc]]);
        $an->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        $spreadsheet->setActiveSheetIndex(0);

        $tenFile = 'diem-' . Str::slug("{$lop->ten_lop}-{$mon->ten_mon}") . "-hk{$request->hoc_ky}-{$request->nam_hoc}.xlsx";

        return response()->streamDownload(fn () => (new Xlsx($spreadsheet))->save('php://output'), $tenFile,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function taiLen(Request $request)
    {
        [$lop, $mon, $hocSinh] = $this->thongTin($request);
        $request->validate(['file' => 'required|file|extensions:xlsx,xls|max:5120'], [
            'file.required' => 'Vui lòng chọn tệp Excel.',
            'file.extensions' => 'Chỉ chấp nhận tệp .xlsx hoặc .xls.',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        } catch (Throwable) {
            return back()->with('error', 'Không đọc được tệp Excel.');
        }

        // Tệp tải từ mẫu có trang ẩn ghi lớp / môn / học kỳ: chặn nhập nhầm tệp của lớp khác
        $an = $spreadsheet->getSheetByName('_thong_tin');
        if ($an && [(string) $an->getCell('A2')->getValue(), (string) $an->getCell('B2')->getValue(),
                (string) $an->getCell('C2')->getValue(), (string) $an->getCell('D2')->getValue()]
                !== [(string) $lop->id, (string) $mon->id, (string) $request->hoc_ky, (string) $request->nam_hoc]) {
            return back()->with('error', 'Tệp này là mẫu của lớp / môn / học kỳ khác, vui lòng kiểm tra lại.');
        }

        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, false, false);

        // Tìm dòng tiêu đề có cột "Mã HS"
        $dongTieuDe = null;
        $viTri = [];
        foreach ($rows as $i => $row) {
            $tieuDe = array_map(fn ($v) => Str::upper(Str::ascii(trim((string) $v))), $row);
            if (in_array('MA HS', $tieuDe, true)) {
                $dongTieuDe = $i;
                foreach ($tieuDe as $c => $ten) {
                    $viTri[$ten] = $c;
                }
                break;
            }
        }
        if ($dongTieuDe === null) {
            return back()->with('error', 'Không tìm thấy dòng tiêu đề có cột "Mã HS". Hãy dùng tệp mẫu tải từ hệ thống.');
        }

        $theoMa = $hocSinh->keyBy('ma_hoc_sinh');
        $loi = [];
        $capNhat = [];

        foreach (array_slice($rows, $dongTieuDe + 1, null, true) as $i => $row) {
            $ma = strtoupper(trim((string) ($row[$viTri['MA HS']] ?? '')));
            if ($ma === '') {
                continue;
            }
            $soDong = $i + 1;
            $hs = $theoMa[$ma] ?? null;
            if (!$hs) {
                $loi[] = "Dòng {$soDong}: mã {$ma} không thuộc danh sách học môn này của lớp.";
                continue;
            }

            $duLieu = [];
            foreach (self::COT as $nhan => $cot) {
                $giaTri = isset($viTri[$nhan]) ? trim((string) ($row[$viTri[$nhan]] ?? '')) : '';
                if ($giaTri === '') {
                    continue;
                }
                if ($mon->laMonNhanXet()) {
                    $giaTri = Str::upper($giaTri);
                    $giaTri = match ($giaTri) { 'Đ', 'D', 'ĐẠT', 'DAT' => 'Đ', 'CĐ', 'CD', 'CHƯA ĐẠT', 'CHUA DAT' => 'CĐ', default => null };
                    if (!$giaTri) {
                        $loi[] = "Dòng {$soDong} ({$hs->ho_ten}), cột {$nhan}: chỉ nhập Đ hoặc CĐ.";
                        continue 2;
                    }
                    $duLieu['nhan_xet'][$cot] = $giaTri;
                } else {
                    $so = str_replace(',', '.', $giaTri);
                    if (!is_numeric($so) || $so < 0 || $so > 10) {
                        $loi[] = "Dòng {$soDong} ({$hs->ho_ten}), cột {$nhan}: \"{$giaTri}\" không phải điểm từ 0 đến 10.";
                        continue 2;
                    }
                    $duLieu["diem_{$cot}"] = round((float) $so, 2);
                }
            }
            if ($duLieu) {
                $capNhat[$hs->id] = $duLieu;
            }
        }

        DB::transaction(function () use ($capNhat, $lop, $mon, $request) {
            foreach ($capNhat as $hocSinhId => $duLieu) {
                $record = DiemSo::firstOrNew([
                    'hoc_sinh_id' => $hocSinhId, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
                    'hoc_ky' => $request->hoc_ky, 'nam_hoc' => $request->nam_hoc,
                ]);
                $record->giao_vien_id = auth()->id();
                $record->ganDiem($duLieu, $mon, giuGiaTriCu: true);
                $record->save();
            }
        });

        $thongBao = 'Đã nhập điểm từ Excel cho ' . count($capNhat) . ' học sinh.';

        return redirect()->route('diem-so.nhap-diem', $request->only('lop_hoc_id', 'mon_hoc_id', 'hoc_ky', 'nam_hoc'))
            ->with($loi ? 'error' : 'success', $loi ? $thongBao . ' Có ' . count($loi) . ' dòng lỗi bị bỏ qua.' : $thongBao)
            ->with('loi_excel', $loi);
    }
}
