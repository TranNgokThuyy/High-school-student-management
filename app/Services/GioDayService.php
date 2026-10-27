<?php
namespace App\Services;

use App\Models\HoSoGiaoVien;
use App\Models\LopHoc;
use App\Models\PhanCongGiangDay;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Tính giờ dạy của giáo viên theo phân công: số tiết/tuần từng học kỳ so với định mức.
 */
class GioDayService
{
    // Số tiết/tuần giảm cho giáo viên chủ nhiệm lớp — chỉnh theo quy định áp dụng tại trường
    public const GIAM_CHU_NHIEM = 4;
    public const SO_TUAN = ['1' => 18, '2' => 17];

    public function bangGioDay(string $namHoc): Collection
    {
        $giaoVien = User::role(['giao-vien-chu-nhiem', 'giao-vien-bo-mon'])->with('hoSoGiaoVien')->orderBy('name')->get();
        $phanCong = PhanCongGiangDay::with(['monHoc', 'lopHoc'])->where('nam_hoc', $namHoc)->get()->groupBy('giao_vien_id');
        $chuNhiem = LopHoc::where('nam_hoc', $namHoc)->whereNotNull('giao_vien_chu_nhiem_id')
            ->get()->groupBy('giao_vien_chu_nhiem_id');

        return $giaoVien->map(function (User $gv) use ($phanCong, $chuNhiem) {
            $hoSo = $gv->hoSoGiaoVien ?? new HoSoGiaoVien();
            $pc = $phanCong->get($gv->id, collect());
            $lopCn = $chuNhiem->get($gv->id, collect());

            $dinhMuc = max(0, ($hoSo->dinh_muc_tiet ?? 17) - ($hoSo->giam_tru_tiet ?? 0)
                - ($lopCn->isNotEmpty() ? self::GIAM_CHU_NHIEM : 0));

            $theoKy = [];
            foreach (self::SO_TUAN as $hk => $soTuan) {
                $tiet = $pc->filter(fn ($p) => in_array($p->hoc_ky, [$hk, 'Ca năm'], true))
                    ->sum(fn ($p) => $p->monHoc?->so_tiet_tuan ?? 0);
                $theoKy[$hk] = [
                    'tiet_tuan' => $tiet,
                    'chenh_tuan' => $tiet - $dinhMuc,
                    'chenh_ky' => ($tiet - $dinhMuc) * $soTuan,
                ];
            }

            return [
                'giao_vien' => $gv,
                'ho_so' => $hoSo,
                'lop_chu_nhiem' => $lopCn->pluck('ten_lop')->implode(', '),
                'phan_cong' => $pc,
                'dinh_muc' => $dinhMuc,
                'theo_ky' => $theoKy,
            ];
        });
    }
}
