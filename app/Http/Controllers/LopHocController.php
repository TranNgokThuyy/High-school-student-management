<?php
namespace App\Http\Controllers;

use App\Models\LopHoc;
use App\Models\User;
use App\Rules\GiaoVien;
use App\Rules\NamHoc;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LopHocController extends Controller
{


    public function index(Request $request)
    {
        $query = LopHoc::with('giaoVienChuNhiem')
            ->withCount('hocSinh')
            ->when($request->khoi, fn($q) => $q->where('khoi', $request->khoi))
            ->when($request->nam_hoc, fn($q) => $q->where('nam_hoc', $request->nam_hoc))
            ->when($request->search, fn($q) => $q->where('ten_lop', 'like', "%{$request->search}%"))
            ->orderBy('khoi')->orderBy('ten_lop');

        $lopHoc = $query->paginate(15)->withQueryString();
        $namHoc = LopHoc::distinct()->pluck('nam_hoc')->sort()->values();

        return view('lop-hoc.index', compact('lopHoc', 'namHoc'));
    }

    public function create()
    {
        $giaoVien = User::role(['giao-vien-chu-nhiem', 'quan-tri-vien'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('lop-hoc.create', compact('giaoVien'));
    }

    public function store(Request $request)
    {
        $validated = $this->kiemTra($request);

        LopHoc::create($validated);

        return redirect()->route('lop-hoc.index')
            ->with('success', "Đã tạo lớp {$validated['ten_lop']} thành công!");
    }

    // Tên lớp bắt đầu bằng khối (10A1, 11 Toán...), không trùng trong năm học; mỗi GVCN chỉ chủ nhiệm một lớp mỗi năm
    private function kiemTra(Request $request, ?LopHoc $lopHoc = null): array
    {
        $request->merge(['ten_lop' => strtoupper(preg_replace('/\s+/', '', (string) $request->ten_lop))]);

        return $request->validate([
            'khoi'    => 'required|in:10,11,12',
            'nam_hoc' => ['required', new NamHoc()],
            'ten_lop' => [
                'required', 'string', 'max:20', 'regex:/^(10|11|12)[A-Z0-9]{1,6}$/u',
                function ($attr, $value, $fail) use ($request) {
                    if (in_array($request->khoi, ['10', '11', '12'], true) && !str_starts_with($value, $request->khoi)) {
                        $fail("Tên lớp phải bắt đầu bằng khối {$request->khoi} (VD: {$request->khoi}A1).");
                    }
                },
                Rule::unique('lop_hoc', 'ten_lop')->where('nam_hoc', $request->nam_hoc)->ignore($lopHoc?->id),
            ],
            'giao_vien_chu_nhiem_id' => [
                'nullable', new GiaoVien(['giao-vien-chu-nhiem', 'quan-tri-vien']),
                Rule::unique('lop_hoc', 'giao_vien_chu_nhiem_id')->where('nam_hoc', $request->nam_hoc)->ignore($lopHoc?->id),
            ],
            'ghi_chu' => 'nullable|string|max:500',
        ], [
            'ten_lop.regex' => 'Tên lớp gồm khối và ký hiệu lớp, không dấu (VD: 10A1, 11B2).',
            'ten_lop.unique' => 'Lớp học này đã tồn tại trong năm học đã chọn!',
            'giao_vien_chu_nhiem_id.unique' => 'Giáo viên này đã chủ nhiệm một lớp khác trong năm học này.',
        ]);
    }

    public function show(LopHoc $lopHoc)
    {
        $lopHoc->load(['giaoVienChuNhiem', 'hocSinh']);
        return view('lop-hoc.show', compact('lopHoc'));
    }

    public function edit(LopHoc $lopHoc)
    {
        $giaoVien = User::role(['giao-vien-chu-nhiem', 'quan-tri-vien'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('lop-hoc.edit', compact('lopHoc', 'giaoVien'));
    }

    public function update(Request $request, LopHoc $lopHoc)
    {
        $validated = $this->kiemTra($request, $lopHoc) + ['is_active' => $request->boolean('is_active')];

        $lopHoc->update($validated);

        return redirect()->route('lop-hoc.show', $lopHoc)
            ->with('success', 'Cập nhật lớp học thành công!');
    }

    public function destroy(LopHoc $lopHoc)
    {
        if ($lopHoc->hocSinh()->count() > 0) {
            return back()->with('error', 'Không thể xóa lớp học đang có học sinh! Hãy chuyển học sinh sang lớp khác trước.');
        }

        $tenLop = $lopHoc->ten_lop;
        $lopHoc->delete();

        return redirect()->route('lop-hoc.index')
            ->with('success', "Đã xóa lớp {$tenLop}!");
    }
}