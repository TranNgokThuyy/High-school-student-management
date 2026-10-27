<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\NhatKy;
use App\Models\User;
use Illuminate\Http\Request;

class NhatKyController extends Controller
{
    public function index(Request $request)
    {
        $nhatKy = NhatKy::with('nguoiThaoTac')
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->doi_tuong, fn ($q) => $q->where('doi_tuong_loai', $request->doi_tuong))
            ->when($request->hanh_dong, fn ($q) => $q->where('hanh_dong', $request->hanh_dong))
            ->when($request->hoc_sinh_id, fn ($q) => $q->where('hoc_sinh_id', $request->hoc_sinh_id))
            ->when($request->filled('tu_ngay'), fn ($q) => $q->whereDate('created_at', '>=', $request->tu_ngay))
            ->when($request->filled('den_ngay'), fn ($q) => $q->whereDate('created_at', '<=', $request->den_ngay))
            ->when($request->filled('tim'), fn ($q) => $q->where('mo_ta', 'like', "%{$request->tim}%"))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $nguoiDung = User::orderBy('name')->get(['id', 'name']);
        $hocSinh = $request->hoc_sinh_id ? HocSinh::withTrashed()->find($request->hoc_sinh_id) : null;

        return view('nhat-ky.index', compact('nhatKy', 'nguoiDung', 'hocSinh'));
    }
}
