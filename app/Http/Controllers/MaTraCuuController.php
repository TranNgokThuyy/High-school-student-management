<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;

// Quản lý mã tra cứu kết quả của phụ huynh: in phiếu theo lớp, cấp lại mã
class MaTraCuuController extends Controller
{
    private function kiemTraQuyenLop(?LopHoc $lop): void
    {
        $user = auth()->user();
        if (!$user->hasRole('quan-tri-vien') && (!$lop || $lop->giao_vien_chu_nhiem_id != $user->id)) {
            abort(403, 'Bạn chỉ được quản lý mã tra cứu của học sinh lớp mình chủ nhiệm!');
        }
    }

    public function inPhieuLop(LopHoc $lopHoc)
    {
        $this->kiemTraQuyenLop($lopHoc);

        $hocSinh = $lopHoc->hocSinh()->where('trang_thai', 'Đang học')->orderBy('ho_ten')->get();

        return view('ma-tra-cuu.phieu-lop', compact('lopHoc', 'hocSinh'));
    }

    public function capLai(HocSinh $hocSinh)
    {
        $this->kiemTraQuyenLop($hocSinh->lopHoc);

        $hocSinh->update(['ma_tra_cuu' => HocSinh::taoMaTraCuu()]);

        return back()->with('success', "Đã cấp mã tra cứu mới cho {$hocSinh->ho_ten}: {$hocSinh->ma_tra_cuu}. Mã cũ không còn dùng được.");
    }
}
