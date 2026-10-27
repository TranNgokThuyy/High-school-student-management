<?php
namespace App\Http\Controllers;

use App\Models\KyThi;
use App\Models\LichThi;
use App\Models\MonHoc;
use App\Models\PhongThi;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class KyThiController extends Controller
{
    public function index()
    {
        $kyThi = KyThi::withCount(['thiSinh', 'phongThi', 'lichThi'])->orderByDesc('id')->get();

        return view('ky-thi.index', ['kyThi' => $kyThi, 'namHoc' => NamHoc::hienTai(), 'hocKy' => NamHoc::hocKyHienTai()]);
    }

    public function store(Request $request)
    {
        $kyThi = KyThi::create($request->validate([
            'ten'            => 'required|string|max:150',
            'nam_hoc'        => 'required|regex:/^\d{4}-\d{4}$/',
            'hoc_ky'         => 'required|in:1,2',
            'khoi'           => 'required|in:10,11,12,Tất cả',
            'suc_chua_phong' => 'required|integer|min:5|max:60',
            'tien_to_sbd'    => 'required|alpha_num|max:10',
            'ghi_chu'        => 'nullable|string|max:500',
        ]));

        return redirect()->route('ky-thi.show', $kyThi)->with('success', "Đã tạo kỳ thi {$kyThi->ten}.");
    }

    public function show(KyThi $kyThi)
    {
        $kyThi->load(['lichThi.monHoc', 'phongThi' => fn ($q) => $q->withCount('thiSinh')]);
        $monHoc = MonHoc::where('is_active', true)
            ->when($kyThi->khoi !== 'Tất cả', fn ($q) => $q->whereIn('khoi', [$kyThi->khoi, 'Tất cả']))
            ->orderBy('ten_mon')->get();
        $soDuThi = $kyThi->hocSinhDuThi()->count();

        return view('ky-thi.show', compact('kyThi', 'monHoc', 'soDuThi'));
    }

    public function themLich(Request $request, KyThi $kyThi)
    {
        $validated = $request->validate([
            'mon_hoc_id'  => 'required|exists:mon_hoc,id',
            'ngay'        => 'required|date',
            'gio_bat_dau' => 'required|date_format:H:i',
            'thoi_gian'   => 'required|integer|min:15|max:240',
        ]);

        if ($kyThi->lichThi()->where('mon_hoc_id', $validated['mon_hoc_id'])->exists()) {
            return back()->withInput()->with('error', 'Môn này đã có trong lịch thi.');
        }

        // Không cho hai môn thi trùng / chồng giờ trong cùng ngày
        [$h, $m] = array_map('intval', explode(':', $validated['gio_bat_dau']));
        $batDau = $h * 60 + $m;
        $ketThuc = $batDau + $validated['thoi_gian'];
        $trung = $kyThi->lichThi()->whereDate('ngay', $validated['ngay'])->with('monHoc')->get()
            ->first(function (LichThi $l) use ($batDau, $ketThuc) {
                [$h2, $m2] = array_map('intval', explode(':', $l->gio_bat_dau));
                $bd = $h2 * 60 + $m2;

                return $batDau < $bd + $l->thoi_gian && $bd < $ketThuc;
            });
        if ($trung) {
            return back()->withInput()->with('error', "Trùng giờ với môn {$trung->monHoc->ten_mon} ({$trung->gio_bat_dau}).");
        }

        $kyThi->lichThi()->create($validated);

        return back()->with('success', 'Đã thêm môn vào lịch thi.');
    }

    public function xoaLich(KyThi $kyThi, LichThi $lichThi)
    {
        abort_unless($lichThi->ky_thi_id === $kyThi->id, 404);
        $lichThi->delete();

        return back()->with('success', 'Đã xóa môn khỏi lịch thi.');
    }

    public function xepPhong(KyThi $kyThi)
    {
        $soThiSinh = $kyThi->xepPhong();

        return back()->with('success', "Đã xếp {$soThiSinh} thí sinh vào " . $kyThi->phongThi()->count() . ' phòng thi.');
    }

    // Danh sách thí sinh theo phòng (niêm yết / phát cho giám thị); không chọn phòng thì in tất cả
    public function inDanhSach(Request $request, KyThi $kyThi)
    {
        $phongThi = $kyThi->phongThi()
            ->when($request->phong, fn ($q) => $q->where('id', $request->phong))
            ->with('thiSinh.hocSinh.lopHoc')->get();
        $kyThi->load('lichThi.monHoc');

        return view('ky-thi.in-danh-sach', compact('kyThi', 'phongThi'));
    }

    public function destroy(KyThi $kyThi)
    {
        $kyThi->delete();

        return redirect()->route('ky-thi.index')->with('success', 'Đã xóa kỳ thi.');
    }
}
