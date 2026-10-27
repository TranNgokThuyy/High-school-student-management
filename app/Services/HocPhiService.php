<?php
namespace App\Services;

use App\Models\HocSinh;
use App\Models\KhoanThu;
use App\Models\LopHoc;
use App\Models\MienGiam;
use App\Models\PhieuThu;
use Illuminate\Support\Collection;

class HocPhiService
{
    /**
     * Công nợ từng khoản thu của một học sinh trong lớp hiện tại.
     *
     * @return Collection<int, array{khoan_thu: KhoanThu, phai_nop: int, da_nop: int, con_no: int}>
     */
    public function congNoHocSinh(HocSinh $hocSinh, LopHoc $lop): Collection
    {
        $khoanThu = KhoanThu::apDungChoLop($lop)->orderBy('han_nop')->orderBy('id')->get();
        $mienGiam = MienGiam::where('hoc_sinh_id', $hocSinh->id)->where('nam_hoc', $lop->nam_hoc)->first();
        $daNop = PhieuThu::hopLe()
            ->where('hoc_sinh_id', $hocSinh->id)
            ->whereIn('khoan_thu_id', $khoanThu->pluck('id'))
            ->selectRaw('khoan_thu_id, SUM(so_tien) as tong')
            ->groupBy('khoan_thu_id')
            ->pluck('tong', 'khoan_thu_id');

        return $khoanThu->map(fn (KhoanThu $kt) => $this->dong($kt, $mienGiam, (int) ($daNop[$kt->id] ?? 0)));
    }

    /**
     * Công nợ cả lớp (có thể lọc một khoản thu).
     *
     * @return array{khoan_thu: Collection, hoc_sinh: Collection, tong: array}
     */
    public function congNoLop(LopHoc $lop, ?int $khoanThuId = null): array
    {
        $khoanThu = KhoanThu::apDungChoLop($lop)
            ->when($khoanThuId, fn ($q) => $q->where('id', $khoanThuId))
            ->orderBy('han_nop')->orderBy('id')->get();

        $hocSinh = $lop->hocSinh()->where('trang_thai', 'Đang học')->orderBy('ho_ten')->get();

        $mienGiam = MienGiam::whereIn('hoc_sinh_id', $hocSinh->pluck('id'))
            ->where('nam_hoc', $lop->nam_hoc)->get()->keyBy('hoc_sinh_id');

        $daNop = PhieuThu::hopLe()
            ->whereIn('hoc_sinh_id', $hocSinh->pluck('id'))
            ->whereIn('khoan_thu_id', $khoanThu->pluck('id'))
            ->selectRaw('hoc_sinh_id, khoan_thu_id, SUM(so_tien) as tong')
            ->groupBy('hoc_sinh_id', 'khoan_thu_id')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->hoc_sinh_id}-{$r->khoan_thu_id}" => (int) $r->tong]);

        $rows = $hocSinh->map(function (HocSinh $hs) use ($khoanThu, $mienGiam, $daNop) {
            $chiTiet = $khoanThu->mapWithKeys(fn (KhoanThu $kt) => [
                $kt->id => $this->dong($kt, $mienGiam->get($hs->id), $daNop["{$hs->id}-{$kt->id}"] ?? 0),
            ]);

            return [
                'hoc_sinh'  => $hs,
                'mien_giam' => $mienGiam->get($hs->id),
                'chi_tiet'  => $chiTiet,
                'phai_nop'  => $chiTiet->sum('phai_nop'),
                'da_nop'    => $chiTiet->sum('da_nop'),
                'con_no'    => $chiTiet->sum('con_no'),
            ];
        });

        return [
            'khoan_thu' => $khoanThu,
            'hoc_sinh'  => $rows,
            'tong'      => [
                'phai_nop'   => $rows->sum('phai_nop'),
                'da_nop'     => $rows->sum('da_nop'),
                'con_no'     => $rows->sum('con_no'),
                'so_hs_no'   => $rows->where('con_no', '>', 0)->count(),
            ],
        ];
    }

    private function dong(KhoanThu $kt, ?MienGiam $mienGiam, int $daNop): array
    {
        $phaiNop = $kt->soTienPhaiNop($mienGiam);

        return [
            'khoan_thu' => $kt,
            'phai_nop'  => $phaiNop,
            'da_nop'    => $daNop,
            'con_no'    => max(0, $phaiNop - $daNop),
        ];
    }
}
