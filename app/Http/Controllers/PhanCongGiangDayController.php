<?php
namespace App\Http\Controllers;

use App\Models\LopHoc;
use App\Models\MonHoc;
use App\Models\PhanCongGiangDay;
use App\Models\User;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class PhanCongGiangDayController extends Controller
{
    public function index(Request $request)
    {
        $namHoc = $request->nam_hoc ?? NamHoc::hienTai();

        $phanCong = PhanCongGiangDay::with(['giaoVien', 'monHoc', 'lopHoc'])
            ->where('nam_hoc', $namHoc)
            ->when($request->lop_hoc_id,   fn($q) => $q->where('lop_hoc_id', $request->lop_hoc_id))
            ->when($request->giao_vien_id, fn($q) => $q->where('giao_vien_id', $request->giao_vien_id))
            ->when($request->mon_hoc_id,   fn($q) => $q->where('mon_hoc_id', $request->mon_hoc_id))
            ->get()
            ->sortBy(fn($pc) => [$pc->lopHoc?->ten_lop, $pc->monHoc?->ten_mon]);

        $lopHoc   = LopHoc::where('nam_hoc', $namHoc)->orderBy('ten_lop')->get();
        $monHoc   = MonHoc::where('is_active', true)->orderBy('ten_mon')->get();
        $giaoVien = $this->danhSachGiaoVien();
        $dsNamHoc = LopHoc::distinct()->pluck('nam_hoc')->push(NamHoc::hienTai())->unique()->sort()->values();

        // Tổng số tiết/tuần của từng giáo viên (theo số tiết chuẩn của môn)
        $taiGiangDay = $phanCong->groupBy('giao_vien_id')
            ->map(fn($ds) => $ds->sum(fn($pc) => $pc->monHoc?->so_tiet_tuan ?? 0));

        return view('phan-cong.index', compact(
            'phanCong', 'lopHoc', 'monHoc', 'giaoVien', 'dsNamHoc', 'namHoc', 'taiGiangDay'
        ));
    }

    public function create(Request $request)
    {
        $namHoc   = $request->nam_hoc ?? NamHoc::hienTai();
        $lopHoc   = LopHoc::where('is_active', true)->orderBy('nam_hoc', 'desc')->orderBy('ten_lop')->get();
        $monHoc   = MonHoc::where('is_active', true)->orderBy('ten_mon')->get();
        $giaoVien = $this->danhSachGiaoVien();

        return view('phan-cong.create', compact('lopHoc', 'monHoc', 'giaoVien', 'namHoc'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'giao_vien_id' => 'required|exists:users,id',
            'mon_hoc_id'   => 'required|exists:mon_hoc,id',
            'lop_hoc_ids'  => 'required|array|min:1',
            'lop_hoc_ids.*'=> 'exists:lop_hoc,id',
            'hoc_ky'       => 'required|in:1,2,Ca năm',
        ], [
            'lop_hoc_ids.required' => 'Vui lòng chọn ít nhất một lớp',
        ]);

        $daThem = 0;
        $boQua  = [];

        foreach (LopHoc::whereIn('id', $validated['lop_hoc_ids'])->get() as $lop) {
            // Mỗi môn ở mỗi lớp chỉ có 1 giáo viên phụ trách trong cùng học kỳ
            $trung = PhanCongGiangDay::with('giaoVien')->where([
                'mon_hoc_id' => $validated['mon_hoc_id'],
                'lop_hoc_id' => $lop->id,
                'nam_hoc'    => $lop->nam_hoc,
            ])->where(function ($q) use ($validated) {
                $validated['hoc_ky'] === 'Ca năm'
                    ? $q->whereIn('hoc_ky', ['1', '2', 'Ca năm'])
                    : $q->whereIn('hoc_ky', [$validated['hoc_ky'], 'Ca năm']);
            })->first();

            if ($trung) {
                $boQua[] = "{$lop->ten_lop} (đã phân công cho {$trung->giaoVien?->name})";
                continue;
            }

            PhanCongGiangDay::create([
                'giao_vien_id' => $validated['giao_vien_id'],
                'mon_hoc_id'   => $validated['mon_hoc_id'],
                'lop_hoc_id'   => $lop->id,
                'nam_hoc'      => $lop->nam_hoc,
                'hoc_ky'       => $validated['hoc_ky'],
            ]);
            $daThem++;
        }

        $redirect = redirect()->route('phan-cong.index')
            ->with('success', "Đã thêm {$daThem} phân công giảng dạy.");

        return $boQua
            ? $redirect->with('error', 'Bỏ qua: ' . implode(', ', $boQua))
            : $redirect;
    }

    public function destroy(PhanCongGiangDay $phanCong)
    {
        $phanCong->delete();

        return back()->with('success', 'Đã xóa phân công giảng dạy!');
    }

    private function danhSachGiaoVien()
    {
        return User::role(['giao-vien-chu-nhiem', 'giao-vien-bo-mon'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
