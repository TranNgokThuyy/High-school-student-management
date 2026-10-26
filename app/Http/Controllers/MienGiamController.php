<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MienGiam;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class MienGiamController extends Controller
{
    public function index(Request $request)
    {
        $namHoc = $request->nam_hoc ?? NamHoc::hienTai();

        $mienGiam = MienGiam::with('hocSinh.lopHoc')
            ->where('nam_hoc', $namHoc)
            ->get()
            ->sortBy(fn ($mg) => [$mg->hocSinh?->lopHoc?->ten_lop, $mg->hocSinh?->ho_ten]);

        $lopHoc = LopHoc::where('nam_hoc', $namHoc)
            ->with(['hocSinh' => fn ($q) => $q->where('trang_thai', 'Đang học')->orderBy('ho_ten')])
            ->orderBy('ten_lop')->get();

        $dsNamHoc = LopHoc::distinct()->pluck('nam_hoc')->push(NamHoc::hienTai())->unique()->sortDesc()->values();
        $doiTuong = MienGiam::DOI_TUONG;

        return view('hoc-phi.mien-giam', compact('mienGiam', 'lopHoc', 'namHoc', 'dsNamHoc', 'doiTuong'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hoc_sinh_id' => 'required|exists:hoc_sinh,id',
            'nam_hoc'     => 'required|regex:/^\d{4}-\d{4}$/',
            'doi_tuong'   => 'required|string|max:150',
            'phan_tram'   => 'required|integer|min:1|max:100',
            'ghi_chu'     => 'nullable|string|max:255',
        ]);

        MienGiam::updateOrCreate(
            ['hoc_sinh_id' => $validated['hoc_sinh_id'], 'nam_hoc' => $validated['nam_hoc']],
            $validated
        );

        $ten = HocSinh::find($validated['hoc_sinh_id'])->ho_ten;

        return back()->with('success', "Đã lưu miễn giảm {$validated['phan_tram']}% cho {$ten}.");
    }

    public function destroy(MienGiam $mienGiam)
    {
        $mienGiam->delete();

        return back()->with('success', 'Đã xóa miễn giảm!');
    }
}
