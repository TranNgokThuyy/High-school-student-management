<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HocSinhController extends Controller
    {

    

    public function index(Request $request)
    {
        $query = HocSinh::with('lopHoc')
            ->when($request->search, fn($q) => 
                $q->where('ho_ten', 'like', "%{$request->search}%")
                  ->orWhere('ma_hoc_sinh', 'like', "%{$request->search}%")
                  ->orWhere('cccd', 'like', "%{$request->search}%")
            )
            ->when($request->lop_hoc_id, fn($q) => 
                $q->where('lop_hoc_id', $request->lop_hoc_id)
            )
            ->when($request->trang_thai, fn($q) => 
                $q->where('trang_thai', $request->trang_thai)
            )
            ->when($request->gioi_tinh, fn($q) => 
                $q->where('gioi_tinh', $request->gioi_tinh)
            )
            ->orderBy('ho_ten');

        $hocSinh = $query->paginate(20)->withQueryString();
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();

        return view('hoc-sinh.index', compact('hocSinh', 'lopHoc'));
    }

    public function create()
    {
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
        return view('hoc-sinh.create', compact('lopHoc'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'ho_ten'             => 'required|string|max:100',
        'ngay_sinh'          => 'required|date|before:today',
        'gioi_tinh'          => 'required|in:Nam,Nữ,Khác',
        'dan_toc'            => 'nullable|string|max:50',
        'ton_giao'           => 'nullable|string|max:50',
        'cccd'               => 'nullable|digits_between:9,12|unique:hoc_sinh,cccd',
        'dia_chi_thuong_tru' => 'nullable|string|max:255',
        'dia_chi_tam_tru'    => 'nullable|string|max:255',
        'so_dien_thoai'      => 'nullable|digits_between:9,12',
        'email'              => 'nullable|email|max:100',
        'ho_ten_cha'         => 'nullable|string|max:100',
        'nghe_nghiep_cha'    => 'nullable|string|max:100',
        'so_dien_thoai_cha'  => 'nullable|digits_between:9,12',
        'ho_ten_me'          => 'nullable|string|max:100',
        'nghe_nghiep_me'     => 'nullable|string|max:100',
        'so_dien_thoai_me'   => 'nullable|digits_between:9,12',
        'lop_hoc_id'         => 'nullable|exists:lop_hoc,id',
        'trang_thai'         => 'required|in:Đang học,Đã tốt nghiệp,Thôi học,Chuyển trường',
        'anh_the'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'ho_ten.required'   => 'Vui lòng nhập họ tên',
        'ngay_sinh.required'=> 'Vui lòng nhập ngày sinh',
        'ngay_sinh.before'  => 'Ngày sinh phải trước ngày hôm nay',
        'cccd.unique'       => 'Số CCCD này đã tồn tại trong hệ thống',
        'gioi_tinh.required'=> 'Vui lòng chọn giới tính',
        'cccd.digits_between' => 'CCCD phải từ 9 đến 12 chữ số',
        'so_dien_thoai.digits_between' => 'Số điện thoại phải từ 9 đến 12 chữ số',
        'so_dien_thoai_cha.digits_between' => 'Số điện thoại của cha phải từ 9 đến 12 chữ số',
        'so_dien_thoai_me.digits_between' => 'Số điện thoại của mẹ phải từ 9 đến 12 chữ số',
        
        
    ]);

    // Upload ảnh thẻ
    if ($request->hasFile('anh_the')) {
        $validated['anh_the'] = $request->file('anh_the')
            ->store('hoc-sinh/anh-the', 'public');
    }

    // KHÔNG cần set ma_hoc_sinh — Model tự tạo trong booted()
    $hocSinh = HocSinh::create($validated);

    // Cập nhật sĩ số lớp
    if ($hocSinh->lop_hoc_id) {
        $hocSinh->lopHoc->increment('si_so');
    }

    return redirect()->route('hoc-sinh.show', $hocSinh)
        ->with('success', "Đã thêm học sinh {$hocSinh->ho_ten} (Mã: {$hocSinh->ma_hoc_sinh}) thành công!");
}

    public function show(HocSinh $hocSinh)
    {
        $hocSinh->load(['lopHoc', 'diemSo.monHoc', 'hanhKiem']);
        return view('hoc-sinh.show', compact('hocSinh'));
    }

    public function edit(HocSinh $hocSinh)
    {
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
        return view('hoc-sinh.edit', compact('hocSinh', 'lopHoc'));
    }

    public function update(Request $request, HocSinh $hocSinh)
    {
        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'ngay_sinh' => 'required|date|before:today',
            'gioi_tinh' => 'required|in:Nam,Nữ,Khác',
            'dan_toc' => 'nullable|string|max:50',
            'ton_giao' => 'nullable|string|max:50',
            'dia_chi_thuong_tru' => 'nullable|string|max:255',
            'dia_chi_tam_tru' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|digits_between:9,12|max:12',
            'email' => 'nullable|email|max:100',
            'cccd' => "nullable|digits_between:9,12|unique:hoc_sinh,cccd,{$hocSinh->id}",
            'ho_ten_cha' => 'nullable|string|max:100',
            'nghe_nghiep_cha' => 'nullable|string|max:100',
            'so_dien_thoai_cha' => 'nullable|digits_between:9,12|max:12',
            'ho_ten_me' => 'nullable|string|max:100',
            'nghe_nghiep_me' => 'nullable|string|max:100',
            'so_dien_thoai_me' => 'nullable|digits_between:9,12|max:12',
            'trang_thai' => 'required|in:Đang học,Đã tốt nghiệp,Thôi học,Chuyển trường',
            'lop_hoc_id' => 'nullable|exists:lop_hoc,id',
            'anh_the' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $lopCu = $hocSinh->lop_hoc_id;

        if ($request->hasFile('anh_the')) {
            // Xóa ảnh cũ
            if ($hocSinh->anh_the) {
                Storage::disk('public')->delete($hocSinh->anh_the);
            }
            $validated['anh_the'] = $request->file('anh_the')
                ->store('hoc-sinh/anh-the', 'public');
        }

        $hocSinh->update($validated);

        // Cập nhật sĩ số
        if ($lopCu != $validated['lop_hoc_id']) {
            if ($lopCu) $this->capNhatSiSo($lopCu);
            if ($validated['lop_hoc_id']) $this->capNhatSiSo($validated['lop_hoc_id']);
        }

        return redirect()->route('hoc-sinh.show', $hocSinh)
            ->with('success', 'Cập nhật thông tin học sinh thành công!');
    }

    public function destroy(HocSinh $hocSinh)
    {
        $lopId = $hocSinh->lop_hoc_id;
        $tenHocSinh = $hocSinh->ho_ten;
        
        $hocSinh->delete(); // Soft delete
        
        if ($lopId) $this->capNhatSiSo($lopId);

        return redirect()->route('hoc-sinh.index')
            ->with('success', "Đã xóa học sinh {$tenHocSinh} khỏi hệ thống!");
    }

    private function capNhatSiSo(int $lopId): void
    {
        $lop = LopHoc::find($lopId);
        if ($lop) {
            $lop->update(['si_so' => $lop->hocSinh()->count()]);
        }
    }
}