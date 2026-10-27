<?php
namespace App\Http\Controllers;

use App\Models\HoSoGiaoVien;
use App\Models\LopHoc;
use App\Models\User;
use App\Services\GioDayService;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class GiaoVienController extends Controller
{
    public function index(Request $request, GioDayService $gioDay)
    {
        $namHoc = $request->nam_hoc ?? NamHoc::hienTai();
        $bang = $gioDay->bangGioDay($namHoc);
        $dsNamHoc = LopHoc::distinct()->pluck('nam_hoc')->push(NamHoc::hienTai())->unique()->sortDesc()->values();

        return view('giao-vien.index', [
            'bang' => $bang, 'namHoc' => $namHoc, 'dsNamHoc' => $dsNamHoc,
            'soTuan' => GioDayService::SO_TUAN, 'giamChuNhiem' => GioDayService::GIAM_CHU_NHIEM,
        ]);
    }

    public function edit(User $giaoVien)
    {
        abort_unless($giaoVien->hasAnyRole(['giao-vien-chu-nhiem', 'giao-vien-bo-mon']), 404);
        $hoSo = $giaoVien->hoSoGiaoVien ?? new HoSoGiaoVien(['dinh_muc_tiet' => 17, 'giam_tru_tiet' => 0, 'chuc_vu' => 'Giáo viên']);

        return view('giao-vien.edit', compact('giaoVien', 'hoSo'));
    }

    public function update(Request $request, User $giaoVien)
    {
        abort_unless($giaoVien->hasAnyRole(['giao-vien-chu-nhiem', 'giao-vien-bo-mon']), 404);

        $validated = $request->validate([
            'ngay_sinh'      => 'nullable|date|before:today',
            'gioi_tinh'      => 'nullable|in:Nam,Nữ',
            'trinh_do'       => 'nullable|in:' . implode(',', HoSoGiaoVien::TRINH_DO),
            'chuyen_mon'     => 'nullable|string|max:100',
            'to_chuyen_mon'  => 'nullable|string|max:100',
            'chuc_vu'        => 'required|in:' . implode(',', HoSoGiaoVien::CHUC_VU),
            'ngay_vao_nganh' => 'nullable|date|before_or_equal:today',
            'dinh_muc_tiet'  => 'required|integer|min:0|max:30',
            'giam_tru_tiet'  => 'required|integer|min:0|max:30',
            'ly_do_giam_tru' => 'nullable|string|max:255',
        ]);

        HoSoGiaoVien::updateOrCreate(['user_id' => $giaoVien->id], $validated);

        return redirect()->route('giao-vien.index')->with('success', "Đã cập nhật hồ sơ giáo viên {$giaoVien->name}.");
    }
}
