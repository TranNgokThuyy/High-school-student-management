<?php
namespace App\Http\Controllers;

use App\Models\DangKyMon;
use App\Models\LopHoc;
use App\Models\MonHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Đăng ký môn lựa chọn / chuyên đề học tập (chương trình GDPT 2018) cho học sinh của lớp
class DangKyMonController extends Controller
{
    // Chương trình GDPT 2018: mỗi học sinh THPT chọn 4 môn lựa chọn và 3 cụm chuyên đề học tập
    public const SO_MON_LUA_CHON = 4;
    public const SO_CHUYEN_DE = 3;

    private function kiemTraQuyenLop(LopHoc $lop): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('quan-tri-vien') || $lop->giao_vien_chu_nhiem_id == $user->id, 403,
            'Bạn chỉ được đăng ký môn cho học sinh lớp mình chủ nhiệm!');
    }

    private function monTuChon(LopHoc $lop)
    {
        return MonHoc::whereIn('loai_mon', ['Lựa chọn', 'Chuyên đề'])
            ->where('is_active', true)
            ->whereIn('khoi', [$lop->khoi, 'Tất cả'])
            ->orderBy('loai_mon')->orderBy('ten_mon')
            ->get();
    }

    public function edit(LopHoc $lopHoc)
    {
        $this->kiemTraQuyenLop($lopHoc);

        $hocSinh = $lopHoc->hocSinh()->where('trang_thai', 'Đang học')->orderBy('ho_ten')->get();
        $monHoc = $this->monTuChon($lopHoc);
        $daDangKy = DangKyMon::where('nam_hoc', $lopHoc->nam_hoc)
            ->whereIn('hoc_sinh_id', $hocSinh->pluck('id'))
            ->get()
            ->map(fn ($dk) => "{$dk->hoc_sinh_id}-{$dk->mon_hoc_id}")
            ->flip();

        return view('dang-ky-mon.edit', [
            'lop' => $lopHoc, 'hocSinh' => $hocSinh, 'monHoc' => $monHoc, 'daDangKy' => $daDangKy,
            'soLuaChon' => self::SO_MON_LUA_CHON, 'soChuyenDe' => self::SO_CHUYEN_DE,
        ]);
    }

    public function update(Request $request, LopHoc $lopHoc)
    {
        $this->kiemTraQuyenLop($lopHoc);
        $request->validate(['dang_ky' => 'nullable|array', 'dang_ky.*' => 'array', 'dang_ky.*.*' => 'integer']);

        $hocSinhIds = $lopHoc->hocSinh()->where('trang_thai', 'Đang học')->pluck('id')->map(fn ($id) => (int) $id);
        $monIds = $this->monTuChon($lopHoc)->pluck('id')->map(fn ($id) => (int) $id);

        DB::transaction(function () use ($request, $lopHoc, $hocSinhIds, $monIds) {
            DangKyMon::where('nam_hoc', $lopHoc->nam_hoc)
                ->whereIn('hoc_sinh_id', $hocSinhIds)
                ->whereIn('mon_hoc_id', $monIds)
                ->delete();

            foreach ($request->input('dang_ky', []) as $hocSinhId => $dsMon) {
                if (!$hocSinhIds->contains((int) $hocSinhId)) {
                    continue;
                }
                foreach ($monIds->intersect(array_map('intval', $dsMon)) as $monId) {
                    DangKyMon::create(['hoc_sinh_id' => (int) $hocSinhId, 'mon_hoc_id' => $monId, 'nam_hoc' => $lopHoc->nam_hoc]);
                }
            }
        });

        return redirect()->route('dang-ky-mon.edit', $lopHoc)
            ->with('success', "Đã lưu đăng ký môn lựa chọn lớp {$lopHoc->ten_lop}.");
    }
}
