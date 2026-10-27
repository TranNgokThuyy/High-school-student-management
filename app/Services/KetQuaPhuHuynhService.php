<?php
namespace App\Services;

use App\Models\DiemDanh;
use App\Models\HocSinh;
use App\Models\KhenThuongKyLuat;
use App\Models\TinNhanNguoiNhan;
use App\Support\NamHoc;

// Dữ liệu một học sinh hiển thị cho phụ huynh (trang tra cứu và cổng phụ huynh dùng chung)
class KetQuaPhuHuynhService
{
    public function __construct(private TongKetService $tongKet, private HocPhiService $hocPhi) {}

    public function duLieu(HocSinh $hocSinh): array
    {
        $hocSinh->loadMissing('lopHoc');
        $lop = $hocSinh->lopHoc;
        $namHoc = $lop?->nam_hoc ?? NamHoc::hienTai();

        $tinNhan = TinNhanNguoiNhan::with('tinNhan')
            ->where('hoc_sinh_id', $hocSinh->id)
            ->orderByDesc('id')
            ->take(20)
            ->get();
        // Đánh dấu đã xem để nhà trường biết phụ huynh đã đọc (trang vẫn hiện nhãn "mới" ở lần xem này)
        TinNhanNguoiNhan::whereIn('id', $tinNhan->whereNull('da_xem_luc')->pluck('id'))->update(['da_xem_luc' => now()]);

        return [
            'hocSinh'    => $hocSinh,
            'namHoc'     => $namHoc,
            'ketQua'     => $this->tongKet->ketQuaHocSinh($hocSinh, $namHoc),
            'congNo'     => $lop ? $this->hocPhi->congNoHocSinh($hocSinh, $lop) : collect(),
            'vangGanDay' => DiemDanh::where('hoc_sinh_id', $hocSinh->id)
                ->where('trang_thai', '!=', 'Có mặt')
                ->where('ngay', '>=', today()->subDays(30)->toDateString())
                ->orderByDesc('ngay')
                ->get(),
            'khenThuong' => KhenThuongKyLuat::where('hoc_sinh_id', $hocSinh->id)
                ->where('nam_hoc', $namHoc)
                ->orderByDesc('ngay_quyet_dinh')
                ->get(),
            'tinNhan'    => $tinNhan,
        ];
    }

    // Tóm tắt cho thẻ học sinh ở trang chủ cổng phụ huynh
    public function tomTat(HocSinh $hocSinh): array
    {
        $hocSinh->loadMissing('lopHoc');

        return [
            'tin_moi' => TinNhanNguoiNhan::where('hoc_sinh_id', $hocSinh->id)->whereNull('da_xem_luc')->count(),
            'con_no'  => $hocSinh->lopHoc ? $this->hocPhi->congNoHocSinh($hocSinh, $hocSinh->lopHoc)->sum('con_no') : 0,
            'vang'    => DiemDanh::where('hoc_sinh_id', $hocSinh->id)
                ->whereIn('trang_thai', ['Vắng có phép', 'Vắng không phép'])
                ->where('ngay', '>=', today()->subDays(30)->toDateString())
                ->count(),
        ];
    }
}
