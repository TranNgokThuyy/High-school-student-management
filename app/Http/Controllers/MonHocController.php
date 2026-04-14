<?php
namespace App\Http\Controllers;

use App\Models\MonHoc;
use Illuminate\Http\Request;

class MonHocController extends Controller
{
    public function index(Request $request)
    {
        $query = MonHoc::withCount('diemSo')
            ->when($request->search, fn($q) =>
                $q->where('ten_mon', 'like', "%{$request->search}%")
                  ->orWhere('ma_mon', 'like', "%{$request->search}%")
            )
            ->when($request->khoi, fn($q) =>
                $q->where('khoi', $request->khoi)
            )
            ->orderBy('khoi')->orderBy('ten_mon');

        $monHoc = $query->paginate(15)->withQueryString();

        return view('mon-hoc.index', compact('monHoc'));
    }

    public function create()
    {
        return view('mon-hoc.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_mon'       => 'required|string|max:20|unique:mon_hoc',
            'ten_mon'      => 'required|string|max:100',
            'so_tiet_tuan' => 'required|integer|min:1|max:10',
            'khoi'         => 'required|in:10,11,12,Tất cả',
            'is_active'    => 'boolean',
        ], [
            'ma_mon.required'  => 'Vui lòng nhập mã môn học',
            'ma_mon.unique'    => 'Mã môn học này đã tồn tại',
            'ten_mon.required' => 'Vui lòng nhập tên môn học',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        MonHoc::create($validated);

        return redirect()->route('mon-hoc.index')
            ->with('success', "Đã thêm môn học {$validated['ten_mon']} thành công!");
    }

    public function edit(MonHoc $monHoc)
    {
        return view('mon-hoc.edit', compact('monHoc'));
    }

    public function update(Request $request, MonHoc $monHoc)
    {
        $validated = $request->validate([
            'ma_mon'       => "required|string|max:20|unique:mon_hoc,ma_mon,{$monHoc->id}",
            'ten_mon'      => 'required|string|max:100',
            'so_tiet_tuan' => 'required|integer|min:1|max:10',
            'khoi'         => 'required|in:10,11,12,Tất cả',
            'is_active'    => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $monHoc->update($validated);

        return redirect()->route('mon-hoc.index')
            ->with('success', "Đã cập nhật môn {$monHoc->ten_mon} thành công!");
    }

    public function destroy(MonHoc $monHoc)
    {
        if ($monHoc->diemSo()->count() > 0) {
            return back()->with('error',
                "Không thể xóa môn \"{$monHoc->ten_mon}\" vì đã có dữ liệu điểm liên quan!");
        }

        $ten = $monHoc->ten_mon;
        $monHoc->delete();

        return redirect()->route('mon-hoc.index')
            ->with('success', "Đã xóa môn học {$ten}!");
    }

    public function toggleActive(MonHoc $monHoc)
    {
        $monHoc->update(['is_active' => !$monHoc->is_active]);
        $trangThai = $monHoc->is_active ? 'kích hoạt' : 'ẩn';
        return back()->with('success', "Đã {$trangThai} môn {$monHoc->ten_mon}!");
    }
}