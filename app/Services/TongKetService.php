<?php
namespace App\Services;

use App\Models\DangKyMon;
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
     * Xếp loại kết quả học tập (Điều 9 TT22). Mỗi phần tử là ĐTB môn (số)
     * hoặc kết quả môn đánh giá bằng nhận xét ('Đạt' / 'Chưa đạt').
     */
    public static function xepLoaiHocTap(array $ketQuaCacMon): ?string
    {
        $ketQua = collect($ketQuaCacMon)->filter(fn ($d) => !is_null($d));
        $diem = $ketQua->reject(fn ($d) => is_string($d))->map(fn ($d) => (float) $d);
        $nhanXet = $ketQua->filter(fn ($d) => is_string($d));
        if ($ketQua->isEmpty()) {
            return null;
        }

        $min = $diem->min() ?? 10;
        $soNxChuaDat = $nhanXet->filter(fn ($d) => $d !== 'Đạt')->count();

        return match (true) {
            // Tốt: môn nhận xét đều Đạt; môn điểm số đều >= 6.5, ít nhất 6 môn >= 8.0
            $soNxChuaDat === 0 && $min >= 6.5 && $diem->filter(fn ($d) => $d >= 8.0)->count() >= 6 => 'Tốt',
            // Khá: môn nhận xét đều Đạt; môn điểm số đều >= 5.0, ít nhất 6 môn >= 6.5
            $soNxChuaDat === 0 && $min >= 5.0 && $diem->filter(fn ($d) => $d >= 6.5)->count() >= 6 => 'Khá',
            // Đạt: tối đa 1 môn nhận xét Chưa đạt; ít nhất 6 môn điểm số >= 5.0; không môn nào < 3.5
            $soNxChuaDat <= 1 && $min >= 3.5 && $diem->filter(fn ($d) => $d >= 5.0)->count() >= 6 => 'Đạt',
            default => 'Chưa đạt',
        };
    }

    /**
     * Mức rèn luyện TT22; vẫn nhận giá trị thang cũ (Trung bình / Yếu) nếu còn sót dữ liệu.
     */
    public static function mucRenLuyen(?string $hanhKiem): ?string
    {
        return match ($hanhKiem) {
            'Tốt'        => 'Tốt',
            'Khá'        => 'Khá',
            'Đạt'        => 'Đạt',
            'Chưa đạt'   => 'Chưa đạt',
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

        $soMonTren9 = collect($diemCacMon)->filter(fn ($d) => !is_null($d) && !is_string($d) && $d >= 9.0)->count();

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

        // Môn lựa chọn / chuyên đề (GDPT 2018): học sinh chỉ học môn đã đăng ký
        $dangKy = DangKyMon::where('nam_hoc', $lop->nam_hoc)
            ->whereIn('hoc_sinh_id', $hocSinh->pluck('id'))
            ->get(['hoc_sinh_id', 'mon_hoc_id'])
            ->groupBy('hoc_sinh_id')
            ->map(fn ($ds) => $ds->pluck('mon_hoc_id')->map(fn ($id) => (int) $id)->all());

        $ketQua = $hocSinh->map(function (HocSinh $hs) use ($monHoc, $diemTheoHs, $hocKy, $hanhKiem, $diemDanh, $lop, $dangKy) {
            $diemCuaHs = $diemTheoHs->get($hs->id, collect());
            $monDaDangKy = $dangKy->get($hs->id, []);

            // diem_mon chỉ có khóa cho các môn học sinh học; môn tự chọn không đăng ký (và không có điểm) bị bỏ qua
            $diemMon = [];
            foreach ($monHoc as $mon) {
                $diemCuaMon = $diemCuaHs->where('mon_hoc_id', $mon->id);
                if ($mon->laMonTuChon() && !in_array($mon->id, $monDaDangKy, true) && $diemCuaMon->isEmpty()) {
                    continue;
                }
                $diemMon[$mon->id] = $this->ketQuaMon($diemCuaMon, $hocKy, $mon);
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

            $diemCoGiaTri = array_filter($diemMon, fn ($d) => !is_null($d) && !is_string($d));

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

    /**
     * Kết quả cả năm của một học sinh (HK1, HK2, cả năm) — dùng cho phiếu điểm và tra cứu.
     * Lớp của năm học được xác định từ bảng điểm, nếu chưa có điểm thì lấy lớp hiện tại.
     */
    public function ketQuaHocSinh(HocSinh $hocSinh, ?string $namHoc = null): ?array
    {
        $namHoc ??= $hocSinh->lopHoc?->nam_hoc ?? NamHoc::hienTai();

        $lopId = DiemSo::where('hoc_sinh_id', $hocSinh->id)->where('nam_hoc', $namHoc)->value('lop_hoc_id')
            ?? ($hocSinh->lopHoc?->nam_hoc === $namHoc ? $hocSinh->lop_hoc_id : null);
        $lop = $lopId ? LopHoc::with('giaoVienChuNhiem')->find($lopId) : null;
        if (!$lop) {
            return null;
        }

        $theoKy = [];
        $monHoc = collect();
        foreach (['1', '2', 'Ca năm'] as $hocKy) {
            $tongKet = $this->tongKetLop($lop, $hocKy);
            $monHoc = $monHoc->merge($tongKet['mon_hoc'])->unique('id');
            $theoKy[$hocKy] = $tongKet['ket_qua']->first(fn ($r) => $r['hoc_sinh']->id === $hocSinh->id);
        }

        // Chỉ giữ các môn học sinh này học (bỏ môn lựa chọn không đăng ký)
        $monHoc = $monHoc->filter(fn ($mon) => collect($theoKy)->contains(
            fn ($r) => $r && array_key_exists($mon->id, $r['diem_mon'])
        ));

        return ['lop' => $lop, 'nam_hoc' => $namHoc, 'mon_hoc' => $monHoc->sortBy('ten_mon')->values(), 'theo_ky' => $theoKy];
    }

    // Kết quả môn: môn nhận xét lấy Đạt / Chưa đạt (cả năm = kết quả HK2), môn điểm số lấy ĐTB
    private function ketQuaMon(Collection $diem, string $hocKy, MonHoc $mon): float|string|null
    {
        if ($mon->laMonNhanXet()) {
            return $diem->firstWhere('hoc_ky', $hocKy === 'Ca năm' ? '2' : $hocKy)?->ket_qua_nhan_xet;
        }

        return $this->diemTrungBinhMon($diem, $hocKy);
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
