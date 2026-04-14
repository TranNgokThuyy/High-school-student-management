<?php
namespace App\Http\Controllers;

use App\Models\LopHoc;
use App\Models\User;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'ten_lop' => 'required|string|max:20',
            'khoi' => 'required|in:10,11,12',
            'nam_hoc' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'giao_vien_chu_nhiem_id' => 'nullable|exists:users,id',
            'ghi_chu' => 'nullable|string|max:500',
        ], [
            'nam_hoc.regex' => 'Năm học phải theo định dạng YYYY-YYYY (VD: 2024-2025)',
            'ten_lop.required' => 'Vui lòng nhập tên lớp',
        ]);

        // Kiểm tra trùng tên lớp trong cùng năm học
        $exists = LopHoc::where('ten_lop', $validated['ten_lop'])
            ->where('nam_hoc', $validated['nam_hoc'])
            ->exists();
        
        if ($exists) {
            return back()->withErrors(['ten_lop' => 'Lớp học này đã tồn tại trong năm học đã chọn!'])
                ->withInput();
        }

        LopHoc::create($validated);

        return redirect()->route('lop-hoc.index')
            ->with('success', "Đã tạo lớp {$validated['ten_lop']} thành công!");
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
        $validated = $request->validate([
            'ten_lop' => 'required|string|max:20',
            'khoi' => 'required|in:10,11,12',
            'nam_hoc' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'giao_vien_chu_nhiem_id' => 'nullable|exists:users,id',
            'ghi_chu' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

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