<?php
namespace App\Http\Controllers;

use App\Models\BienDongHocSinh;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Support\NamHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Chuyển trường / thôi học / học lại / tiếp nhận học sinh chuyển đến
class BienDongController extends Controller
{
    public function index(Request $request)
    {
        $namHoc = $request->nam_hoc ?? NamHoc::hienTai();

        $bienDong = BienDongHocSinh::with(['hocSinh', 'lopHoc', 'nguoiTao'])
            ->where('nam_hoc', $namHoc)
            ->when($request->loai, fn ($q) => $q->where('loai', $request->loai))
            ->orderByDesc('ngay')->orderByDesc('id')
            ->paginate(30)->withQueryString();

        $thongKe = BienDongHocSinh::where('nam_hoc', $namHoc)
            ->selectRaw('loai, count(*) as so_luong')->groupBy('loai')->pluck('so_luong', 'loai');

        $dsNamHoc = LopHoc::distinct()->pluck('nam_hoc')->push(NamHoc::hienTai())->unique()->sortDesc()->values();

        return view('bien-dong.index', compact('bienDong', 'thongKe', 'namHoc', 'dsNamHoc'));
    }

    public function create(HocSinh $hocSinh)
    {
        $hocSinh->load(['lopHoc', 'bienDong']);
        $lopHoc = LopHoc::where('is_active', true)->where('nam_hoc', NamHoc::hienTai())->orderBy('ten_lop')->get();

        return view('bien-dong.create', compact('hocSinh', 'lopHoc'));
    }

    public function store(Request $request, HocSinh $hocSinh)
    {
        $validated = $request->validate([
            'loai'          => 'required|in:Chuyển đi,Thôi học,Học lại',
            'ngay'          => 'required|date|before_or_equal:today',
            'so_quyet_dinh' => 'nullable|string|max:50',
            'truong'        => 'required_if:loai,Chuyển đi|nullable|string|max:200',
            'ly_do'         => 'nullable|string|max:1000',
            'lop_hoc_id'    => 'required_if:loai,Học lại|nullable|exists:lop_hoc,id',
        ], [
            'truong.required_if' => 'Vui lòng nhập trường chuyển đến',
            'lop_hoc_id.required_if' => 'Vui lòng chọn lớp học sinh quay lại học',
        ]);

        $trangThaiHienTai = $hocSinh->trang_thai;
        if ($validated['loai'] === 'Học lại' && $trangThaiHienTai === 'Đang học') {
            return back()->withInput()->with('error', 'Học sinh đang đi học, không cần ghi nhận học lại.');
        }
        if ($validated['loai'] !== 'Học lại' && $trangThaiHienTai !== 'Đang học') {
            return back()->withInput()->with('error', "Học sinh đang ở trạng thái \"{$trangThaiHienTai}\".");
        }

        $lopCu = $hocSinh->lop_hoc_id;
        DB::transaction(function () use ($validated, $hocSinh) {
            BienDongHocSinh::create($validated + [
                'hoc_sinh_id'  => $hocSinh->id,
                'lop_hoc_id'   => $validated['lop_hoc_id'] ?? $hocSinh->lop_hoc_id,
                'nam_hoc'      => NamHoc::hienTai(Carbon::parse($validated['ngay'])),
                'nguoi_tao_id' => auth()->id(),
            ]);

            $hocSinh->update(array_filter([
                'trang_thai' => BienDongHocSinh::LOAI[$validated['loai']],
                'lop_hoc_id' => $validated['loai'] === 'Học lại' ? $validated['lop_hoc_id'] : null,
            ]));
        });

        foreach (array_unique(array_filter([$lopCu, $hocSinh->lop_hoc_id])) as $lopId) {
            LopHoc::find($lopId)?->capNhatSiSo();
        }

        return redirect()->route('hoc-sinh.show', $hocSinh)
            ->with('success', "Đã ghi nhận {$validated['loai']} cho học sinh {$hocSinh->ho_ten}.");
    }
}
