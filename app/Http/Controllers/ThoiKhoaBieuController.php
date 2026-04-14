<?php
namespace App\Http\Controllers;

use App\Models\LopHoc;
use App\Models\MonHoc;
use App\Models\ThoiKhoaBieu;
use App\Models\User;
use Illuminate\Http\Request;

class ThoiKhoaBieuController extends Controller
{
    private function getLopHoc()
    {
        $user = auth()->user();
        if ($user->hasRole('giao-vien-chu-nhiem')) {
            return LopHoc::where('giao_vien_chu_nhiem_id', $user->id)
                ->where('is_active', true)->orderBy('ten_lop')->get();
        }
        return LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
    }

    public function index(Request $request)
{
    $user          = auth()->user();
    $lopHoc        = $this->getLopHoc();
    $namHocHienTai = date('Y') . '-' . (date('Y') + 1);

    $lopId  = $request->lop_hoc_id ?? $lopHoc->first()?->id;
    $hocKy  = $request->hoc_ky ?? '1';
    $namHoc = $request->nam_hoc ?? $namHocHienTai;

    $selectedLop = $lopId ? LopHoc::find($lopId) : null;

    $thuLabels  = [
        2 => 'Thứ 2', 3 => 'Thứ 3', 4 => 'Thứ 4',
        5 => 'Thứ 5', 6 => 'Thứ 6', 7 => 'Thứ 7', 
    ];

    $tietLabels = [
        1  => 'Tiết 1 (7:00)',  2  => 'Tiết 2 (7:45)',
        3  => 'Tiết 3 (8:30)',  4  => 'Tiết 4 (9:15)',
        5  => 'Tiết 5 (10:15)',  
    ];

    $tkbData = collect();
    if ($lopId) {
        $tkbData = ThoiKhoaBieu::where([
            'lop_hoc_id' => $lopId,
            'hoc_ky'     => $hocKy,
            'nam_hoc'    => $namHoc,
        ])->with(['monHoc', 'giaoVien'])
          ->orderBy('thu')
          ->orderBy('tiet_bat_dau')
          ->get()
          ->groupBy('thu');
    }

    $tkbGiaoVien = collect();
    if ($user->hasRole('giao-vien') || $user->hasRole('giao-vien-chu-nhiem')) {
        $tkbGiaoVien = ThoiKhoaBieu::where([
            'giao_vien_id' => $user->id,
            'hoc_ky'       => $hocKy,
            'nam_hoc'      => $namHoc,
        ])->with(['monHoc', 'lopHoc'])
          ->orderBy('thu')
          ->orderBy('tiet_bat_dau')
          ->get()
          ->groupBy('thu');
    }

    return view('thoi-khoa-bieu.index', compact(
        'lopHoc', 'selectedLop', 'tkbData', 'tkbGiaoVien',
        'lopId', 'hocKy', 'namHoc', 'namHocHienTai',
        'thuLabels', 'tietLabels'
    ));
}
    public function create(Request $request)
{
    $lopHoc   = $this->getLopHoc();
    $monHoc   = MonHoc::where('is_active', true)->orderBy('ten_mon')->get();
    $giaoVien = User::role(['giao-vien-chu-nhiem'])
                    ->where('is_active', true)->orderBy('name')->get();

    $namHocHienTai = date('Y') . '-' . (date('Y') + 1);
    $selectedLopId = $request->lop_hoc_id;

    $thuLabels  = [
        2 => 'Thứ 2', 3 => 'Thứ 3', 4 => 'Thứ 4',
        5 => 'Thứ 5', 6 => 'Thứ 6', 7 => 'Thứ 7',
    ];

    $tietLabels = [
        1  => 'Tiết 1 (7:00)',  2  => 'Tiết 2 (7:45)',
        3  => 'Tiết 3 (8:30)',  4  => 'Tiết 4 (9:15)',
        5  => 'Tiết 5 (10:15)', 
    ];

    return view('thoi-khoa-bieu.create', compact(
        'lopHoc', 'monHoc', 'giaoVien',
        'namHocHienTai', 'selectedLopId',
        'thuLabels', 'tietLabels'
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lop_hoc_id'   => 'required|exists:lop_hoc,id',
            'mon_hoc_id'   => 'required|exists:mon_hoc,id',
            'giao_vien_id' => 'required|exists:users,id',
            'thu'          => 'required|integer|between:2,8',
            'tiet_bat_dau' => 'required|integer|between:1,10',
            'so_tiet'      => 'required|integer|between:1,5',
            'phong_hoc'    => 'nullable|string|max:20',
            'hoc_ky'       => 'required|in:1,2',
            'nam_hoc'      => 'required|string',
        ]);

        // Kiểm tra trùng lịch lớp
        $trungLop = ThoiKhoaBieu::where([
            'lop_hoc_id'   => $validated['lop_hoc_id'],
            'thu'          => $validated['thu'],
            'hoc_ky'       => $validated['hoc_ky'],
            'nam_hoc'      => $validated['nam_hoc'],
        ])->where(function($q) use ($validated) {
            $tietEnd = $validated['tiet_bat_dau'] + $validated['so_tiet'] - 1;
            $q->whereBetween('tiet_bat_dau', [$validated['tiet_bat_dau'], $tietEnd])
              ->orWhereRaw('? BETWEEN tiet_bat_dau AND (tiet_bat_dau + so_tiet - 1)',
                           [$validated['tiet_bat_dau']]);
        })->first();

        if ($trungLop) {
            return back()->withInput()
                ->with('error', "Lớp đã có lịch học vào {$this->getThuLabel($validated['thu'])} tiết {$validated['tiet_bat_dau']}!");
        }

        // Kiểm tra trùng lịch giáo viên
        $trungGV = ThoiKhoaBieu::where([
            'giao_vien_id' => $validated['giao_vien_id'],
            'thu'          => $validated['thu'],
            'hoc_ky'       => $validated['hoc_ky'],
            'nam_hoc'      => $validated['nam_hoc'],
        ])->where(function($q) use ($validated) {
            $tietEnd = $validated['tiet_bat_dau'] + $validated['so_tiet'] - 1;
            $q->whereBetween('tiet_bat_dau', [$validated['tiet_bat_dau'], $tietEnd])
              ->orWhereRaw('? BETWEEN tiet_bat_dau AND (tiet_bat_dau + so_tiet - 1)',
                           [$validated['tiet_bat_dau']]);
        })->first();

        if ($trungGV) {
            $gv = User::find($validated['giao_vien_id']);
            return back()->withInput()
                ->with('error', "Giáo viên {$gv->name} đã có lịch dạy vào {$this->getThuLabel($validated['thu'])} tiết {$validated['tiet_bat_dau']}!");
        }

        ThoiKhoaBieu::create($validated);

        return redirect()->route('thoi-khoa-bieu.index', [
            'lop_hoc_id' => $validated['lop_hoc_id'],
            'hoc_ky'     => $validated['hoc_ky'],
            'nam_hoc'    => $validated['nam_hoc'],
        ])->with('success', 'Đã thêm lịch học thành công!');
    }

    public function destroy(ThoiKhoaBieu $thoiKhoaBieu)
    {
        $thoiKhoaBieu->delete();
        return back()->with('success', 'Đã xóa lịch học!');
    }

    private function getThuLabel(int $thu): string
    {
        return ThoiKhoaBieu::$thuLabels[$thu] ?? "Thứ {$thu}";
    }
}