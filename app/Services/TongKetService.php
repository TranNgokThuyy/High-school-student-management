<?php
namespace App\Services;

use App\Models\DiemDanh;
use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;
use App\Support\NamHoc;
use Illuminate\Support\Collection;

/**
 * Tổng kết kết quả học tập, rèn luyện theo Thông tư 22/2021/TT-BGDĐT
 * (áp dụng cho môn đánh giá bằng điểm số).
 */
class TongKetService
{
    public const SO_BUOI_NGHI_TOI_DA = 45;

    /**
     * Xếp loại kết quả học tập từ danh sách ĐTB các môn (Điều 9 TT22).
     */
    public static function xepLoaiHocTap(array $diemCacMon): ?string
    {
        $diem = collect($diemCacMon)->filter(fn($d) => !is_null($d))->map(fn($d) => (float) $d);
        if ($diem->isEmpty()) {
            return null;
        }

        $min = $diem->min();

        return match (true) {
            $min >= 6.5 && $diem->filter(fn($d) => $d >= 8.0)->count() >= 6 => 'Tốt',
            $min >= 5.0 && $diem->filter(fn($d) => $d >= 6.5)->count() >= 6 => 'Khá',
            $min >= 3.5 && $diem->filter(fn($d) => $d < 5.0)->count() <= 1  => 'Đạt',
            default => 'Chưa đạt',
        };
    }

    /**
     * Quy đổi xếp loại hạnh kiểm cũ sang mức rèn luyện TT22.
     */
    public static function mucRenLuyen(?string $hanhKiem): ?string
    {
        return match ($hanhKiem) {
            'Tốt'        => 'Tốt',
            'Khá'        => 'Khá',
            'Trung bình' => 'Đạt',
            'Yếu'        => 'Chưa đạt',
            default      => null,
        };
    }

    /**
     * Danh hiệu: Học sinh Xuất sắc / Học sinh Giỏi (Điều 15 TT22).
     */
    public static function danhHieu(?string $hocTap, ?string $renLuyen, array $diemCacMon): ?string
    {
        if ($hocTap !== 'Tốt' || $renLuyen !== 'Tốt') {
            return null;
        }

        $soMonTren9 = collect($diemCacMon)->filter(fn($d) => !is_null($d) && $d >= 9.0)->count();

        return $soMonTren9 >= 6 ? 'Học sinh Xuất sắc' : 'Học sinh Giỏi';
    }

    /**
     * Xét lên lớp cuối năm (Điều 12, 13 TT22).
     */
    public static function xetLenLop(?string $hocTap, ?string $renLuyen, int $soBuoiNghi, string $khoi): array
    {
        if (is_null($hocTap) || is_null($renLuyen)) {
            return ['dat' => false, 'ket_qua' => 'Chưa đủ dữ liệu', 'ly_do' => []];
        }

        $lyDo = [];
        if ($soBuoiNghi > self::SO_BUOI_NGHI_TOI_DA) {
            $lyDo[] = "Nghỉ {$soBuoiNghi} buổi (quá " . self::SO_BUOI_NGHI_TOI_DA . ' buổi)';
        }
        if ($hocTap === 'Chưa đạt') {
            $lyDo[] = 'Học tập Chưa đạt → kiểm tra lại';
        }
        if ($renLuyen === 'Chưa đạt') {
            $lyDo[] = 'Rèn luyện Chưa đạt → rèn luyện trong hè';
        }

        if (empty($lyDo)) {
            return [
                'dat'     => true,
                'ket_qua' => $khoi === '12' ? 'Hoàn thành chương trình' : 'Lên lớp',
                'ly_do'   => [],
            ];
        }

        return [
            'dat'     => false,
            'ket_qua' => $soBuoiNghi > self::SO_BUOI_NGHI_TOI_DA ? 'Ở lại lớp' : 'Chưa được lên lớp',
            'ly_do'   => $lyDo,
        ];
    }

    /**
     * Tổng kết toàn bộ học sinh của một lớp theo học kỳ ('1', '2') hoặc cả năm ('Ca năm').
     *
     * @return array{mon_hoc: Collection, ket_qua: Collection}
     */
    public function tongKetLop(LopHoc $lop, string $hocKy): array
    {
        $hocKyDiem = $hocKy === 'Ca năm' ? ['1', '2'] : [$hocKy];

        $diemSo = DiemSo::where('lop_hoc_id', $lop->id)
            ->where('nam_hoc', $lop->nam_hoc)
            ->whereIn('hoc_ky', $hocKyDiem)
            ->get();

        // Học sinh hiện tại của lớp + học sinh từng có điểm ở lớp (đã chuyển lớp/lên lớp)
        $hocSinhIds = $lop->hocSinh()->pluck('id')
            ->merge($diemSo->pluck('hoc_sinh_id'))
            ->unique();
        $hocSinh = HocSinh::withTrashed()->whereIn('id', $hocSinhIds)->orderBy('ho_ten')->get();

        $monHoc = MonHoc::whereIn('id', $diemSo->pluck('mon_hoc_id')->unique())->orderBy('ten_mon')->get();

        $hanhKiem = HanhKiem::where('lop_hoc_id', $lop->id)
            ->where('nam_hoc', $lop->nam_hoc)
            ->where('hoc_ky', $hocKy)
            ->get()
            ->keyBy('hoc_sinh_id');

        $diemDanh = DiemDanh::tongHop($lop->id, ...NamHoc::khoangThoiGian($lop->nam_hoc, $hocKy));

        $diemTheoHs = $diemSo->groupBy('hoc_sinh_id');

        $ketQua = $hocSinh->map(function (HocSinh $hs) use ($monHoc, $diemTheoHs, $hocKy, $hanhKiem, $diemDanh, $lop) {
            $diemCuaHs = $diemTheoHs->get($hs->id, collect());

            $diemMon = [];
            foreach ($monHoc as $mon) {
                $diemMon[$mon->id] = $this->diemTrungBinhMon($diemCuaHs->where('mon_hoc_id', $mon->id), $hocKy);
            }

            $duDiem   = !in_array(null, $diemMon, true) && count($diemMon) > 0;
            $hocTap   = $duDiem ? self::xepLoaiHocTap($diemMon) : null;
            $hk       = $hanhKiem->get($hs->id);
            $renLuyen = self::mucRenLuyen($hk?->xep_loai_hanh_kiem);

            // Ưu tiên số buổi nghỉ đã chốt trong hạnh kiểm, nếu chưa có thì lấy từ điểm danh
            $vangDiemDanh = $diemDanh['hoc_sinh'][$hs->id] ?? ['vang_co_phep' => 0, 'vang_khong_phep' => 0];
            $soBuoiNghi = $hk
                ? $hk->so_buoi_vang_co_phep + $hk->so_buoi_vang_khong_phep
                : $vangDiemDanh['vang_co_phep'] + $vangDiemDanh['vang_khong_phep'];

            $diemCoGiaTri = array_filter($diemMon, fn($d) => !is_null($d));

            return [
                'hoc_sinh'   => $hs,
                'diem_mon'   => $diemMon,
                'dtb_chung'  => $diemCoGiaTri ? round(array_sum($diemCoGiaTri) / count($diemCoGiaTri), 2) : null,
                'du_diem'    => $duDiem,
                'hoc_tap'    => $hocTap,
                'hanh_kiem'  => $hk?->xep_loai_hanh_kiem,
                'ren_luyen'  => $renLuyen,
                'so_buoi_nghi' => $soBuoiNghi,
                'danh_hieu'  => self::danhHieu($hocTap, $renLuyen, $diemMon),
                'len_lop'    => $hocKy === 'Ca năm'
                    ? self::xetLenLop($hocTap, $renLuyen, $soBuoiNghi, $lop->khoi)
                    : null,
                'dang_o_lop' => $hs->lop_hoc_id == $lop->id && !$hs->trashed(),
            ];
        });

        return ['mon_hoc' => $monHoc, 'ket_qua' => $ketQua];
    }

    // ĐTB môn học kỳ lấy từ bảng điểm; ĐTB môn cả năm = (ĐTB HK1 + 2 × ĐTB HK2) / 3
    private function diemTrungBinhMon(Collection $diem, string $hocKy): ?float
    {
        if ($hocKy !== 'Ca năm') {
            $dtb = $diem->first()?->diem_trung_binh;
            return is_null($dtb) ? null : (float) $dtb;
        }

        $hk1 = $diem->firstWhere('hoc_ky', '1')?->diem_trung_binh;
        $hk2 = $diem->firstWhere('hoc_ky', '2')?->diem_trung_binh;

        if (is_null($hk1) || is_null($hk2)) {
            return null;
        }

        return round(((float) $hk1 + 2 * (float) $hk2) / 3, 1);
    }
}
