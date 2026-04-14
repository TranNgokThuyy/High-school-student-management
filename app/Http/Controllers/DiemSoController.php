<?php
namespace App\Http\Controllers;

use App\Models\DiemSo;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiemSoController extends Controller
{
  

    // Bảng điểm theo lớp, môn, học kỳ
    public function bangDiem(Request $request)
    {
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
        $monHoc = MonHoc::where('is_active', true)->orderBy('ten_mon')->get();
        $namHoc = LopHoc::distinct()->pluck('nam_hoc')->sort()->values();

        $diem = collect();
        $selectedLop = null;
        $selectedMon = null;

        if ($request->filled(['lop_hoc_id', 'mon_hoc_id', 'hoc_ky', 'nam_hoc'])) {
            $selectedLop = LopHoc::with('hocSinh')->find($request->lop_hoc_id);
            $selectedMon = MonHoc::find($request->mon_hoc_id);

            if ($selectedLop && $selectedMon) {
                $diem = DiemSo::where([
                    'lop_hoc_id' => $request->lop_hoc_id,
                    'mon_hoc_id' => $request->mon_hoc_id,
                    'hoc_ky' => $request->hoc_ky,
                    'nam_hoc' => $request->nam_hoc,
                ])->with('hocSinh')->get()->keyBy('hoc_sinh_id');
            }
        }

        return view('diem-so.bang-diem', compact(
            'lopHoc', 'monHoc', 'namHoc', 'diem', 'selectedLop', 'selectedMon'
        ));
    }

    // Form nhập điểm hàng loạt
    public function nhapDiem(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'mon_hoc_id' => 'required|exists:mon_hoc,id',
            'hoc_ky' => 'required|in:1,2',
            'nam_hoc' => 'required|string',
        ]);

        $lop = LopHoc::with('hocSinh')->findOrFail($request->lop_hoc_id);
        $mon = MonHoc::findOrFail($request->mon_hoc_id);

        $diemHienCo = DiemSo::where([
            'lop_hoc_id' => $request->lop_hoc_id,
            'mon_hoc_id' => $request->mon_hoc_id,
            'hoc_ky' => $request->hoc_ky,
            'nam_hoc' => $request->nam_hoc,
        ])->get()->keyBy('hoc_sinh_id');

        return view('diem-so.nhap-diem', compact(
            'lop', 'mon', 'diemHienCo',
            'request'
        ));
    }

    // Lưu điểm hàng loạt
    public function luuDiem(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'mon_hoc_id' => 'required|exists:mon_hoc,id',
            'hoc_ky' => 'required|in:1,2',
            'nam_hoc' => 'required|string',
            'diem' => 'required|array',
            'diem.*.diem_tx1' => 'nullable|numeric|min:0|max:10',
            'diem.*.diem_tx2' => 'nullable|numeric|min:0|max:10',
            'diem.*.diem_tx3' => 'nullable|numeric|min:0|max:10',
            'diem.*.diem_tx4' => 'nullable|numeric|min:0|max:10',
            'diem.*.diem_gk' => 'nullable|numeric|min:0|max:10',
            'diem.*.diem_ck' => 'nullable|numeric|min:0|max:10',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->diem as $hocSinhId => $diemData) {
                $record = DiemSo::firstOrNew([
                    'hoc_sinh_id' => $hocSinhId,
                    'mon_hoc_id' => $request->mon_hoc_id,
                    'lop_hoc_id' => $request->lop_hoc_id,
                    'hoc_ky' => $request->hoc_ky,
                    'nam_hoc' => $request->nam_hoc,
                ]);

                $record->fill([
                    'diem_tx1' => $diemData['diem_tx1'] ?? null,
                    'diem_tx2' => $diemData['diem_tx2'] ?? null,
                    'diem_tx3' => $diemData['diem_tx3'] ?? null,
                    'diem_tx4' => $diemData['diem_tx4'] ?? null,
                    'diem_gk' => $diemData['diem_gk'] ?? null,
                    'diem_ck' => $diemData['diem_ck'] ?? null,
                    'giao_vien_id' => auth()->id(),
                ]);

                $record->save();
                
                // Tính điểm trung bình
                $record->diem_trung_binh = $record->tinhDiemTrungBinh();
                $record->save();
            }
        });

        return redirect()->route('diem-so.bang-diem', [
            'lop_hoc_id' => $request->lop_hoc_id,
            'mon_hoc_id' => $request->mon_hoc_id,
            'hoc_ky' => $request->hoc_ky,
            'nam_hoc' => $request->nam_hoc,
        ])->with('success', 'Đã lưu điểm thành công!');
    }
}