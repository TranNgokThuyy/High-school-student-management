<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Services\TongKetService;
use App\Support\NamHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TongKetController extends Controller
{
    public function __construct(private TongKetService $tongKet) {}

    // Quản trị viên xem mọi lớp; giáo viên chủ nhiệm xem lớp mình chủ nhiệm
    private function getLopHoc()
    {
        $user = auth()->user();

        return LopHoc::query()
            ->when(!$user->hasRole('quan-tri-vien'), fn($q) => $q->where('giao_vien_chu_nhiem_id', $user->id))
            ->orderByDesc('nam_hoc')
            ->orderBy('ten_lop')
            ->get();
    }

    private function kiemTraQuyenLop(LopHoc $lop): void
    {
        $user = auth()->user();
        if (!$user->hasRole('quan-tri-vien') && $lop->giao_vien_chu_nhiem_id != $user->id) {
            abort(403, 'Bạn chỉ được xem tổng kết lớp mình chủ nhiệm!');
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'nullable|exists:lop_hoc,id',
            'hoc_ky'     => 'nullable|in:1,2,Ca năm',
        ]);

        $lopHoc = $this->getLopHoc();
        $lopId  = $request->lop_hoc_id ?? $lopHoc->first()?->id;
        $hocKy  = $request->hoc_ky ?? 'Ca năm';

        $lop = null;
        $monHoc = collect();
        $ketQua = collect();
        $thongKe = [];

        if ($lopId) {
            $lop = LopHoc::with('giaoVienChuNhiem')->findOrFail($lopId);
            $this->kiemTraQuyenLop($lop);

            ['mon_hoc' => $monHoc, 'ket_qua' => $ketQua] = $this->tongKet->tongKetLop($lop, $hocKy);

            $thongKe = [
                'hoc_tap'   => $ketQua->countBy(fn($r) => $r['hoc_tap'] ?? 'Chưa đủ điểm')->all(),
                'ren_luyen' => $ketQua->countBy(fn($r) => $r['ren_luyen'] ?? 'Chưa xếp loại')->all(),
                'danh_hieu' => $ketQua->countBy(fn($r) => $r['danh_hieu'] ?? 'Không')->all(),
                'len_lop'   => $hocKy === 'Ca năm'
                    ? $ketQua->countBy(fn($r) => $r['len_lop']['ket_qua'])->all()
                    : [],
            ];
        }

        return view('tong-ket.index', compact('lopHoc', 'lop', 'hocKy', 'monHoc', 'ketQua', 'thongKe'));
    }

    // Màn hình xét lên lớp / chuyển học sinh sang năm học mới
    public function lenLop(Request $request)
    {
        $request->validate(['lop_hoc_id' => 'nullable|exists:lop_hoc,id']);

        $lopHoc = $this->getLopHoc();
        $lop = $request->lop_hoc_id ? LopHoc::findOrFail($request->lop_hoc_id) : null;

        $ketQua = collect();
        $lopLenLop = collect();
        $lopOLai = collect();
        $namHocMoi = null;

        if ($lop) {
            $ketQua = $this->tongKet->tongKetLop($lop, 'Ca năm')['ket_qua']
                ->filter(fn($r) => $r['dang_o_lop'] && $r['hoc_sinh']->trang_thai === 'Đang học')
                ->values();

            $namHocMoi = NamHoc::keTiep($lop->nam_hoc);

            if ($lop->khoi !== '12') {
                $lopLenLop = LopHoc::where('nam_hoc', $namHocMoi)
                    ->where('khoi', (string) ((int) $lop->khoi + 1))
                    ->orderBy('ten_lop')->get();
            }
            $lopOLai = LopHoc::where('nam_hoc', $namHocMoi)
                ->where('khoi', $lop->khoi)
                ->orderBy('ten_lop')->get();
        }

        return view('tong-ket.len-lop', compact('lopHoc', 'lop', 'ketQua', 'lopLenLop', 'lopOLai', 'namHocMoi'));
    }

    public function luuLenLop(Request $request)
    {
        $request->validate([
            'lop_hoc_id'     => 'required|exists:lop_hoc,id',
            'lop_dich_id'    => 'nullable|exists:lop_hoc,id',
            'lop_o_lai_id'   => 'nullable|exists:lop_hoc,id',
            'hoc_sinh_ids'   => 'nullable|array',
            'hoc_sinh_ids.*' => 'integer',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $namHocMoi = NamHoc::keTiep($lop->nam_hoc);
        $laKhoi12 = $lop->khoi === '12';

        $lopDich = $request->lop_dich_id ? LopHoc::find($request->lop_dich_id) : null;
        $lopOLai = $request->lop_o_lai_id ? LopHoc::find($request->lop_o_lai_id) : null;

        if (!$laKhoi12 && (!$lopDich || $lopDich->nam_hoc !== $namHocMoi || (int) $lopDich->khoi !== (int) $lop->khoi + 1)) {
            return back()->with('error', "Vui lòng chọn lớp khối " . ((int) $lop->khoi + 1) . " của năm học {$namHocMoi}!");
        }
        if ($lopOLai && ($lopOLai->nam_hoc !== $namHocMoi || $lopOLai->khoi !== $lop->khoi)) {
            return back()->with('error', "Lớp cho học sinh ở lại phải là lớp khối {$lop->khoi} năm học {$namHocMoi}!");
        }

        $hocSinhLop = HocSinh::where('lop_hoc_id', $lop->id)->where('trang_thai', 'Đang học')->get();
        $duocLen = collect($request->hoc_sinh_ids ?? [])->map(fn($id) => (int) $id);

        $soLen = 0;
        $soOLai = 0;

        DB::transaction(function () use ($hocSinhLop, $duocLen, $laKhoi12, $lopDich, $lopOLai, &$soLen, &$soOLai) {
            foreach ($hocSinhLop as $hs) {
                if ($duocLen->contains($hs->id)) {
                    $laKhoi12
                        ? $hs->update(['trang_thai' => 'Đã tốt nghiệp'])
                        : $hs->update(['lop_hoc_id' => $lopDich->id]);
                    $soLen++;
                } elseif ($lopOLai) {
                    $hs->update(['lop_hoc_id' => $lopOLai->id]);
                    $soOLai++;
                }
            }
        });

        $lop->capNhatSiSo();
        $lopDich?->capNhatSiSo();
        $lopOLai?->capNhatSiSo();

        $thongBao = $laKhoi12
            ? "Đã công nhận tốt nghiệp cho {$soLen} học sinh lớp {$lop->ten_lop}."
            : "Đã chuyển {$soLen} học sinh lên lớp {$lopDich->ten_lop}.";
        if ($soOLai) {
            $thongBao .= " {$soOLai} học sinh ở lại chuyển vào lớp {$lopOLai->ten_lop}.";
        }

        return redirect()->route('tong-ket.len-lop', ['lop_hoc_id' => $lop->id])->with('success', $thongBao);
    }
}
