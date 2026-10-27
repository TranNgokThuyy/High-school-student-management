<?php
namespace App\Http\Controllers;

use App\Services\SaoLuuService;
use RuntimeException;

class SaoLuuController extends Controller
{
    public function __construct(private SaoLuuService $saoLuu) {}

    public function index()
    {
        $danhSach = $this->saoLuu->danhSach();

        return view('sao-luu.index', compact('danhSach'));
    }

    public function store()
    {
        $tenTep = $this->saoLuu->tao();

        return back()->with('success', "Đã tạo bản sao lưu {$tenTep}.");
    }

    public function download(string $tenTep)
    {
        try {
            return response()->download($this->saoLuu->duongDan($tenTep));
        } catch (RuntimeException) {
            abort(404);
        }
    }

    public function destroy(string $tenTep)
    {
        try {
            $this->saoLuu->xoa($tenTep);
        } catch (RuntimeException) {
            abort(404);
        }

        return back()->with('success', "Đã xóa bản sao lưu {$tenTep}.");
    }
}
