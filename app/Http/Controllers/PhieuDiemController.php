<?php
namespace App\Http\Controllers;

use App\Models\DiemSo;
use App\Models\HocSinh;
use App\Services\TongKetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhieuDiemController extends Controller
{
    public function __construct(private TongKetService $tongKet) {}

    public function show(Request $request, HocSinh $hocSinh)
    {
        $user = auth()->user();
        // GV chủ nhiệm chỉ in phiếu điểm học sinh lớp mình; GV bộ môn và quản trị viên in được mọi học sinh
        if ($user->hasRole('giao-vien-chu-nhiem') && !$user->hasAnyRole(['quan-tri-vien', 'giao-vien-bo-mon'])
            && $hocSinh->lopHoc?->giao_vien_chu_nhiem_id != $user->id) {
            abort(403, 'Bạn chỉ được in phiếu điểm học sinh lớp mình chủ nhiệm!');
        }

        $dsNamHoc = DiemSo::where('hoc_sinh_id', $hocSinh->id)->distinct()->orderByDesc('nam_hoc')->pluck('nam_hoc');
        $ketQua = $this->tongKet->ketQuaHocSinh($hocSinh, $request->nam_hoc ?? $dsNamHoc->first());

        if (!$ketQua) {
            return back()->with('error', 'Học sinh chưa có dữ liệu điểm để in phiếu.');
        }

        $pdf = Pdf::loadView('phieu-diem.pdf', compact('hocSinh', 'ketQua'))->setPaper('a4');
        $tenFile = 'phieu-diem-' . Str::slug($hocSinh->ho_ten) . '-' . $ketQua['nam_hoc'] . '.pdf';

        return $request->boolean('tai') ? $pdf->download($tenFile) : $pdf->stream($tenFile);
    }
}
