<?php
namespace App\Http\Controllers;

use App\Models\DiemSo;
use App\Models\HocSinh;
use App\Models\KhenThuongKyLuat;
use App\Services\TongKetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhieuDiemController extends Controller
{
    public function __construct(private TongKetService $tongKet) {}

    // GV chủ nhiệm chỉ in học sinh lớp mình; GV bộ môn và quản trị viên in được mọi học sinh
    private function kiemTraQuyen(HocSinh $hocSinh): void
    {
        $user = auth()->user();
        if ($user->hasRole('giao-vien-chu-nhiem') && !$user->hasAnyRole(['quan-tri-vien', 'giao-vien-bo-mon'])
            && $hocSinh->lopHoc?->giao_vien_chu_nhiem_id != $user->id) {
            abort(403, 'Bạn chỉ được in phiếu điểm / học bạ học sinh lớp mình chủ nhiệm!');
        }
    }

    public function show(Request $request, HocSinh $hocSinh)
    {
        $this->kiemTraQuyen($hocSinh);

        $dsNamHoc = DiemSo::where('hoc_sinh_id', $hocSinh->id)->distinct()->orderByDesc('nam_hoc')->pluck('nam_hoc');
        $ketQua = $this->tongKet->ketQuaHocSinh($hocSinh, $request->nam_hoc ?? $dsNamHoc->first());

        if (!$ketQua) {
            return back()->with('error', 'Học sinh chưa có dữ liệu điểm để in phiếu.');
        }

        $pdf = Pdf::loadView('phieu-diem.pdf', compact('hocSinh', 'ketQua'))->setPaper('a4');
        $tenFile = 'phieu-diem-' . Str::slug($hocSinh->ho_ten) . '-' . $ketQua['nam_hoc'] . '.pdf';

        return $request->boolean('tai') ? $pdf->download($tenFile) : $pdf->stream($tenFile);
    }

    // Học bạ: kết quả tất cả các năm học đã có điểm, mỗi năm một trang
    public function hocBa(Request $request, HocSinh $hocSinh)
    {
        $this->kiemTraQuyen($hocSinh);

        $cacNam = DiemSo::where('hoc_sinh_id', $hocSinh->id)->distinct()->orderBy('nam_hoc')->pluck('nam_hoc')
            ->map(fn ($namHoc) => $this->tongKet->ketQuaHocSinh($hocSinh, $namHoc))
            ->filter()
            ->values();

        if ($cacNam->isEmpty()) {
            return back()->with('error', 'Học sinh chưa có dữ liệu điểm để in học bạ.');
        }

        $khenThuong = KhenThuongKyLuat::where('hoc_sinh_id', $hocSinh->id)->orderBy('ngay_quyet_dinh')->get()->groupBy('nam_hoc');

        $pdf = Pdf::loadView('phieu-diem.hoc-ba', compact('hocSinh', 'cacNam', 'khenThuong'))->setPaper('a4');
        $tenFile = 'hoc-ba-' . Str::slug($hocSinh->ho_ten) . '-' . $hocSinh->ma_hoc_sinh . '.pdf';

        return $request->boolean('tai') ? $pdf->download($tenFile) : $pdf->stream($tenFile);
    }
}
