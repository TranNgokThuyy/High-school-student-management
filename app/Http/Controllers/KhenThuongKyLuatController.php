<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\KhenThuongKyLuat;
use App\Models\LopHoc;
use App\Support\NamHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KhenThuongKyLuatController extends Controller
{
    // Giáo viên chủ nhiệm chỉ quản lý học sinh lớp mình; quản trị viên quản lý tất cả
    private function lopDuocQuanLy()
    {
        $user = auth()->user();

        return LopHoc::query()
            ->when(!$user->hasRole('quan-tri-vien'), fn($q) => $q->where('giao_vien_chu_nhiem_id', $user->id))
            ->where('is_active', true)
            ->orderBy('ten_lop')
            ->get();
    }

    public function index(Request $request)
    {
        $user   = auth()->user();
        $lopHoc = $this->lopDuocQuanLy();
        $namHoc = $request->nam_hoc ?? NamHoc::hienTai();

        $query = KhenThuongKyLuat::with(['hocSinh.lopHoc', 'nguoiTao'])
            ->where('nam_hoc', $namHoc)
            ->when(!$user->hasRole('quan-tri-vien'), fn($q) =>
                $q->whereHas('hocSinh', fn($h) => $h->whereIn('lop_hoc_id', $lopHoc->pluck('id'))))
            ->when($request->loai, fn($q) => $q->where('loai', $request->loai))
            ->when($request->lop_hoc_id, fn($q) =>
                $q->whereHas('hocSinh', fn($h) => $h->where('lop_hoc_id', $request->lop_hoc_id)))
            ->when($request->search, fn($q) =>
                $q->whereHas('hocSinh', fn($h) => $h->where('ho_ten', 'like', "%{$request->search}%")))
            ->orderByDesc('ngay_quyet_dinh');

        $danhSach = $query->paginate(20)->withQueryString();

        $dsNamHoc = KhenThuongKyLuat::distinct()->pluck('nam_hoc')
            ->push(NamHoc::hienTai())->unique()->sortDesc()->values();

        return view('khen-thuong.index', compact('danhSach', 'lopHoc', 'namHoc', 'dsNamHoc'));
    }

    public function create(Request $request)
    {
        $lopHoc = $this->lopDuocQuanLy()->load(['hocSinh' => fn($q) => $q->where('trang_thai', 'Đang học')->orderBy('ho_ten')]);
        $hinhThuc = KhenThuongKyLuat::HINH_THUC;
        $hocSinhChon = $request->hoc_sinh_id;

        return view('khen-thuong.create', compact('lopHoc', 'hinhThuc', 'hocSinhChon'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hoc_sinh_id'     => 'required|exists:hoc_sinh,id',
            'loai'            => 'required|in:Khen thưởng,Kỷ luật',
            'hinh_thuc'       => 'required|string|max:100',
            'noi_dung'        => 'required|string|max:1000',
            'ngay_quyet_dinh' => 'required|date|before_or_equal:today',
            'so_quyet_dinh'   => 'nullable|string|max:50',
        ], [
            'noi_dung.required' => 'Vui lòng nhập nội dung khen thưởng / kỷ luật',
        ]);

        $hocSinh = HocSinh::findOrFail($validated['hoc_sinh_id']);
        abort_unless(
            $this->lopDuocQuanLy()->pluck('id')->contains($hocSinh->lop_hoc_id),
            403,
            'Bạn chỉ được ghi nhận khen thưởng / kỷ luật cho học sinh lớp mình chủ nhiệm!'
        );

        KhenThuongKyLuat::create($validated + [
            'nam_hoc'      => NamHoc::hienTai(Carbon::parse($validated['ngay_quyet_dinh'])),
            'nguoi_tao_id' => auth()->id(),
        ]);

        return redirect()->route('khen-thuong.index')
            ->with('success', "Đã ghi nhận {$validated['loai']} cho học sinh {$hocSinh->ho_ten}!");
    }

    public function destroy(KhenThuongKyLuat $khenThuong)
    {
        $khenThuong->delete();

        return back()->with('success', 'Đã xóa bản ghi!');
    }
}
