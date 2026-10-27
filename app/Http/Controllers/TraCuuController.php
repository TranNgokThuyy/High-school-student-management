<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Services\KetQuaPhuHuynhService;
use Illuminate\Http\Request;

// Phụ huynh / học sinh tra cứu kết quả bằng mã học sinh + mã tra cứu, không cần tài khoản
class TraCuuController extends Controller
{
    public function form()
    {
        return view('tra-cuu.index');
    }

    public function traCuu(Request $request, KetQuaPhuHuynhService $ketQua)
    {
        $request->validate([
            'ma_hoc_sinh' => 'required|string|max:30',
            'ma_tra_cuu'  => 'required|string|max:20',
        ], [
            'ma_hoc_sinh.required' => 'Vui lòng nhập mã học sinh',
            'ma_tra_cuu.required'  => 'Vui lòng nhập mã tra cứu',
        ]);

        $hocSinh = HocSinh::timTheoMaTraCuu($request->ma_hoc_sinh, $request->ma_tra_cuu);
        if (!$hocSinh) {
            return back()->withInput($request->only('ma_hoc_sinh'))
                ->withErrors(['ma_hoc_sinh' => 'Mã học sinh hoặc mã tra cứu không đúng.']);
        }

        return view('tra-cuu.ket-qua', $ketQua->duLieu($hocSinh));
    }
}
