<?php
namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\HocSinh;
use App\Services\ThongBaoPhuHuynhService;
use App\Models\LopHoc;
use App\Support\NamHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiemDanhController extends Controller
{
    // Lớp của năm học hiện tại: quản trị viên thấy mọi lớp; giáo viên chỉ thấy lớp mình chủ nhiệm
    private function getLopHoc()
    {
        $user = auth()->user();

        return LopHoc::where('is_active', true)
            ->where('nam_hoc', NamHoc::hienTai())
            ->when(!$user->hasRole('quan-tri-vien'), fn($q) => $q->where('giao_vien_chu_nhiem_id', $user->id))
            ->orderBy('ten_lop')
            ->get();
    }

    private function kiemTraQuyenLop(LopHoc $lop): void
    {
        $user = auth()->user();
        if (!$user->hasRole('quan-tri-vien') && $lop->giao_vien_chu_nhiem_id != $user->id) {
            abort(403, 'Bạn chỉ được điểm danh lớp mình chủ nhiệm!');
        }
    }

    public function index(Request $request)
    {
        $lopHoc = $this->getLopHoc();
        $ngay   = $request->filled('ngay') ? Carbon::parse($request->ngay) : today();
        $lopId  = $request->lop_hoc_id ?? $lopHoc->first()?->id;

        $lop = null;
        $diemDanh = collect();
        $thongKe = [];

        if ($lopId) {
            $lop = LopHoc::with(['hocSinh' => fn($q) => $q->where('trang_thai', 'Đang học')->orderBy('ho_ten')])
                ->findOrFail($lopId);
            $this->kiemTraQuyenLop($lop);

            $diemDanh = DiemDanh::where('lop_hoc_id', $lop->id)
                ->whereDate('ngay', $ngay)
                ->get()
                ->keyBy('hoc_sinh_id');

            foreach (DiemDanh::TRANG_THAI as $tt) {
                $thongKe[$tt] = $diemDanh->where('trang_thai', $tt)->count();
            }
        }

        return view('diem-danh.index', compact('lopHoc', 'lop', 'ngay', 'diemDanh', 'thongKe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'ngay'       => 'required|date|before_or_equal:today',
            'diem_danh'  => 'required|array',
            'diem_danh.*.trang_thai' => 'required|in:' . implode(',', DiemDanh::TRANG_THAI),
            'diem_danh.*.ghi_chu'    => 'nullable|string|max:255',
        ], [
            'ngay.before_or_equal' => 'Không thể điểm danh cho ngày trong tương lai!',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $this->kiemTraQuyenLop($lop);

        [$batDauNamHoc, $ketThucNamHoc] = NamHoc::khoangThoiGian($lop->nam_hoc, 'Ca năm');
        if (!Carbon::parse($request->ngay)->between($batDauNamHoc, $ketThucNamHoc)) {
            return back()->with('error', "Ngày điểm danh không thuộc năm học {$lop->nam_hoc} của lớp {$lop->ten_lop}!");
        }

        $hocSinhCuaLop = $lop->hocSinh()->pluck('id')->map(fn($id) => (int) $id)->all();
        $ngay = Carbon::parse($request->ngay)->toDateString();

        // Học sinh đã bị đánh vắng không phép trước đó trong ngày (không báo lại khi lưu lần nữa)
        $daVangKhongPhep = DiemDanh::where('lop_hoc_id', $lop->id)->where('ngay', $ngay)
            ->where('trang_thai', 'Vắng không phép')->pluck('hoc_sinh_id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($request, $lop, $hocSinhCuaLop, $ngay) {
            foreach ($request->diem_danh as $hocSinhId => $data) {
                if (!in_array((int) $hocSinhId, $hocSinhCuaLop, true)) {
                    continue;
                }

                DiemDanh::updateOrCreate(
                    ['hoc_sinh_id' => (int) $hocSinhId, 'ngay' => $ngay],
                    [
                        'lop_hoc_id'         => $lop->id,
                        'trang_thai'         => $data['trang_thai'],
                        'ghi_chu'            => $data['ghi_chu'] ?? null,
                        'nguoi_diem_danh_id' => auth()->id(),
                    ]
                );
            }
        });

        $thongBao = "Đã lưu điểm danh lớp {$lop->ten_lop} ngày " . Carbon::parse($ngay)->format('d/m/Y') . '!';

        if ($request->boolean('bao_phu_huynh') && auth()->user()->can('gui-tin-phu-huynh')) {
            $vangMoi = collect($request->diem_danh)
                ->filter(fn ($d, $id) => $d['trang_thai'] === 'Vắng không phép'
                    && in_array((int) $id, $hocSinhCuaLop, true)
                    && !in_array((int) $id, $daVangKhongPhep, true))
                ->keys();

            if ($vangMoi->isNotEmpty()) {
                $ngayHienThi = Carbon::parse($ngay)->format('d/m/Y');
                app(ThongBaoPhuHuynhService::class)->gui(
                    "Học sinh vắng học không phép ngày {$ngayHienThi}",
                    "Nhà trường thông báo: em {ho_ten} (lớp {lop}) vắng học KHÔNG PHÉP ngày {$ngayHienThi}. "
                        . 'Kính đề nghị quý phụ huynh liên hệ giáo viên chủ nhiệm để phối hợp.',
                    'Chuyên cần',
                    "Lớp {$lop->ten_lop} - HS vắng không phép",
                    HocSinh::whereIn('id', $vangMoi)->get(),
                    ['email', 'sms', 'zalo'],
                    auth()->id(),
                );
                $thongBao .= " Đã báo phụ huynh của {$vangMoi->count()} học sinh vắng không phép.";
            }
        }

        return redirect()->route('diem-danh.index', ['lop_hoc_id' => $lop->id, 'ngay' => $ngay])
            ->with('success', $thongBao);
    }

    public function tongHop(Request $request)
    {
        $lopHoc = $this->getLopHoc();
        $lopId  = $request->lop_hoc_id ?? $lopHoc->first()?->id;
        $hocKy  = $request->hoc_ky ?? NamHoc::hocKyHienTai();

        $lop = null;
        $tongHop = ['so_buoi' => 0, 'hoc_sinh' => []];
        $khoang = null;

        if ($lopId) {
            $lop = LopHoc::with(['hocSinh' => fn($q) => $q->orderBy('ho_ten')])->findOrFail($lopId);
            $this->kiemTraQuyenLop($lop);

            $khoang  = NamHoc::khoangThoiGian($lop->nam_hoc, $hocKy);
            $tongHop = DiemDanh::tongHop($lop->id, ...$khoang);
        }

        return view('diem-danh.tong-hop', compact('lopHoc', 'lop', 'hocKy', 'tongHop', 'khoang'));
    }
}
