<?php
namespace App\Http\Controllers;

use App\Models\KhoanThu;
use App\Models\LopHoc;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class KhoanThuController extends Controller
{
    public function index(Request $request)
    {
        $namHoc = $request->nam_hoc ?? NamHoc::hienTai();

        $khoanThu = KhoanThu::where('nam_hoc', $namHoc)
            ->withSum(['phieuThu as da_thu' => fn ($q) => $q->where('da_huy', false)], 'so_tien')
            ->orderBy('loai')->orderBy('ten')
            ->get();

        $dsNamHoc = LopHoc::distinct()->pluck('nam_hoc')
            ->merge(KhoanThu::distinct()->pluck('nam_hoc'))
            ->push(NamHoc::hienTai())->unique()->sortDesc()->values();

        return view('hoc-phi.khoan-thu.index', compact('khoanThu', 'namHoc', 'dsNamHoc'));
    }

    public function create()
    {
        return view('hoc-phi.khoan-thu.form', ['khoanThu' => new KhoanThu([
            'nam_hoc' => NamHoc::hienTai(), 'hoc_ky' => 'Ca năm', 'khoi' => 'Tất cả',
            'loai' => 'Học phí', 'ap_dung_mien_giam' => true, 'is_active' => true,
        ])]);
    }

    public function store(Request $request)
    {
        KhoanThu::create($this->validated($request));

        return redirect()->route('khoan-thu.index', ['nam_hoc' => $request->nam_hoc])
            ->with('success', "Đã thêm khoản thu {$request->ten}!");
    }

    public function edit(KhoanThu $khoanThu)
    {
        return view('hoc-phi.khoan-thu.form', compact('khoanThu'));
    }

    public function update(Request $request, KhoanThu $khoanThu)
    {
        $khoanThu->update($this->validated($request));

        return redirect()->route('khoan-thu.index', ['nam_hoc' => $khoanThu->nam_hoc])
            ->with('success', "Đã cập nhật khoản thu {$khoanThu->ten}!");
    }

    public function destroy(KhoanThu $khoanThu)
    {
        if ($khoanThu->phieuThu()->exists()) {
            return back()->with('error', 'Khoản thu đã có phiếu thu, không thể xóa. Hãy chuyển sang "Ngừng áp dụng".');
        }

        $khoanThu->delete();

        return back()->with('success', 'Đã xóa khoản thu!');
    }

    private function validated(Request $request): array
    {
        $request->merge(['so_tien' => preg_replace('/\D/', '', (string) $request->so_tien)]);

        return $request->validate([
            'ten'      => 'required|string|max:150',
            'loai'     => 'required|in:' . implode(',', KhoanThu::LOAI),
            'nam_hoc'  => 'required|regex:/^\d{4}-\d{4}$/',
            'hoc_ky'   => 'required|in:1,2,Ca năm',
            'khoi'     => 'required|in:10,11,12,Tất cả',
            'so_tien'  => 'required|integer|min:0|max:1000000000',
            'han_nop'  => 'nullable|date',
            'mo_ta'    => 'nullable|string|max:500',
        ], [
            'nam_hoc.regex' => 'Năm học phải theo định dạng YYYY-YYYY',
        ]) + [
            'ap_dung_mien_giam' => $request->boolean('ap_dung_mien_giam'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
