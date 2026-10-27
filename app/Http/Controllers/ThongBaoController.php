<?php
namespace App\Http\Controllers;

use App\Models\ThongBao;
use App\Support\PhanQuyen;
use Illuminate\Http\Request;

class ThongBaoController extends Controller
{
    public function index()
    {
        $thongBao = ThongBao::choNguoiDung(auth()->user(), caHetHan: true);
        $vaiTro = PhanQuyen::TEN_VAI_TRO;

        return view('thong-bao.index', compact('thongBao', 'vaiTro'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de'     => 'required|string|max:200',
            'noi_dung'    => 'required|string|max:5000',
            'muc_do'      => 'required|in:' . implode(',', array_keys(ThongBao::MUC_DO)),
            'doi_tuong'   => 'nullable|array',
            'doi_tuong.*' => 'in:' . implode(',', array_keys(PhanQuyen::TEN_VAI_TRO)),
            'het_han'     => 'nullable|date|after_or_equal:today',
        ]);

        ThongBao::create($validated + [
            'doi_tuong'     => $validated['doi_tuong'] ?? null,
            'ghim'          => $request->boolean('ghim'),
            'nguoi_dang_id' => auth()->id(),
        ]);

        return redirect()->route('thong-bao.index')->with('success', 'Đã đăng thông báo!');
    }

    public function destroy(ThongBao $thongBao)
    {
        $thongBao->delete();

        return back()->with('success', 'Đã xóa thông báo!');
    }
}
