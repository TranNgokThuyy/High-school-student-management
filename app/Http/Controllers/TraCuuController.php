<?php
namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\HocSinh;
use App\Models\KhenThuongKyLuat;
use App\Models\TinNhanNguoiNhan;
use App\Services\HocPhiService;
use App\Services\TongKetService;
use App\Support\NamHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;

// Phụ huynh / học sinh tra cứu kết quả bằng mã học sinh + ngày sinh, không cần tài khoản
class TraCuuController extends Controller
{
    public function form()
    {
        return view('tra-cuu.index');
    }

    public function traCuu(Request $request, TongKetService $tongKet, HocPhiService $hocPhi)
    {
        $request->validate([
            'ma_hoc_sinh' => 'required|string|max:30',
            'ngay_sinh'   => 'required|date',
        ], [
            'ma_hoc_sinh.required' => 'Vui lòng nhập mã học sinh',
            'ngay_sinh.required'   => 'Vui lòng nhập ngày sinh',
        ]);

        $hocSinh = HocSinh::with('lopHoc')
            ->where('ma_hoc_sinh', strtoupper(trim($request->ma_hoc_sinh)))
            ->whereDate('ngay_sinh', Carbon::parse($request->ngay_sinh)->toDateString())
            ->first();

        if (!$hocSinh) {
            return back()->withInput()->withErrors(['ma_hoc_sinh' => 'Không tìm thấy học sinh với mã và ngày sinh đã nhập.']);
        }

        $lop = $hocSinh->lopHoc;
        $namHoc = $lop?->nam_hoc ?? NamHoc::hienTai();

        $ketQua = $tongKet->ketQuaHocSinh($hocSinh, $namHoc);
        $congNo = $lop ? $hocPhi->congNoHocSinh($hocSinh, $lop) : collect();

        $vangGanDay = DiemDanh::where('hoc_sinh_id', $hocSinh->id)
            ->where('trang_thai', '!=', 'Có mặt')
            ->where('ngay', '>=', today()->subDays(30)->toDateString())
            ->orderByDesc('ngay')
            ->get();

        $khenThuong = KhenThuongKyLuat::where('hoc_sinh_id', $hocSinh->id)
            ->where('nam_hoc', $namHoc)
            ->orderByDesc('ngay_quyet_dinh')
            ->get();

        $tinNhan = TinNhanNguoiNhan::with('tinNhan')
            ->where('hoc_sinh_id', $hocSinh->id)
            ->orderByDesc('id')
            ->take(20)
            ->get();
        // Đánh dấu đã xem để nhà trường biết phụ huynh đã đọc
        TinNhanNguoiNhan::whereIn('id', $tinNhan->whereNull('da_xem_luc')->pluck('id'))->update(['da_xem_luc' => now()]);

        return view('tra-cuu.ket-qua', compact('hocSinh', 'ketQua', 'congNo', 'vangGanDay', 'khenThuong', 'namHoc', 'tinNhan'));
    }
}
