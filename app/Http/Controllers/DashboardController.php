<?php
namespace App\Http\Controllers;

use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $namHocHienTai = date('Y') . '-' . (date('Y') + 1);
        
        $stats = [
            'tong_hoc_sinh' => HocSinh::where('trang_thai', 'Đang học')->count(),
            'tong_lop_hoc' => LopHoc::where('is_active', true)->count(),
            'tong_giao_vien' => User::role('giao-vien-chu-nhiem')->where('is_active', true)->count(),
            'tong_tai_khoan' => User::count(),
        ];

        // Phân bố học sinh theo lớp
        $phanBoTheoLop = LopHoc::withCount('hocSinh')
            ->where('is_active', true)
            ->orderBy('khoi')
            ->take(10)
            ->get();

        // Thống kê hạnh kiểm theo loại
        $thongKeHanhKiem = HanhKiem::where('nam_hoc', $namHocHienTai)
            ->where('hoc_ky', '1')
            ->selectRaw('xep_loai_hanh_kiem, count(*) as so_luong')
            ->groupBy('xep_loai_hanh_kiem')
            ->get();

        return view('dashboard', compact('stats', 'phanBoTheoLop', 'thongKeHanhKiem', 'namHocHienTai'));
    }
}