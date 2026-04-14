<?php
namespace App\Http\Controllers;

use App\Models\DiemSo;
use App\Models\HanhKiem;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\MonHoc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DiemSoExport;

class BaoCaoController extends Controller
{
    

    public function index()
    {
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
        $monHoc = MonHoc::where('is_active', true)->orderBy('ten_mon')->get();
        return view('bao-cao.index', compact('lopHoc', 'monHoc'));
    }

    // Báo cáo điểm theo lớp
    public function baoCaoDiemLop(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'hoc_ky' => 'required|in:1,2',
            'nam_hoc' => 'required|string',
        ]);

        $lop = LopHoc::with(['hocSinh', 'giaoVienChuNhiem'])->findOrFail($request->lop_hoc_id);
        $monHoc = MonHoc::where('is_active', true)->get();
        
        $bangDiem = [];
        foreach ($lop->hocSinh as $hs) {
            $diemHocSinh = [];
            foreach ($monHoc as $mon) {
                $diem = DiemSo::where([
                    'hoc_sinh_id' => $hs->id,
                    'mon_hoc_id' => $mon->id,
                    'hoc_ky' => $request->hoc_ky,
                    'nam_hoc' => $request->nam_hoc,
                ])->first();
                $diemHocSinh[$mon->id] = $diem;
            }
            
            // Tính điểm TB tổng hợp
            $diemTBCac = collect($diemHocSinh)
                ->filter(fn($d) => $d && !is_null($d->diem_trung_binh))
                ->avg('diem_trung_binh');
            
            $bangDiem[] = [
                'hoc_sinh' => $hs,
                'diem' => $diemHocSinh,
                'diem_tb_tong_hop' => $diemTBCac ? round($diemTBCac, 2) : null,
            ];
        }

        return view('bao-cao.diem-lop', compact('lop', 'monHoc', 'bangDiem', 'request'));
    }

    // Xuất PDF
    public function xuatPDF(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'hoc_ky' => 'required|in:1,2',
            'nam_hoc' => 'required|string',
        ]);

        $lop = LopHoc::with(['hocSinh', 'giaoVienChuNhiem'])->findOrFail($request->lop_hoc_id);
        $monHoc = MonHoc::where('is_active', true)->get();
        
        $bangDiem = [];
        foreach ($lop->hocSinh as $hs) {
            $diemHocSinh = [];
            foreach ($monHoc as $mon) {
                $diem = DiemSo::where([
                    'hoc_sinh_id' => $hs->id,
                    'mon_hoc_id' => $mon->id,
                    'hoc_ky' => $request->hoc_ky,
                    'nam_hoc' => $request->nam_hoc,
                ])->first();
                $diemHocSinh[$mon->id] = $diem;
            }
            $bangDiem[] = ['hoc_sinh' => $hs, 'diem' => $diemHocSinh];
        }

        $pdf = Pdf::loadView('bao-cao.pdf.diem-lop', compact('lop', 'monHoc', 'bangDiem', 'request'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("bang-diem-{$lop->ten_lop}-hk{$request->hoc_ky}.pdf");
    }

    // Xuất Excel
    public function xuatExcel(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'hoc_ky' => 'required|in:1,2',
            'nam_hoc' => 'required|string',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $tenFile = "bang-diem-{$lop->ten_lop}-hk{$request->hoc_ky}-{$request->nam_hoc}.xlsx";

        return Excel::download(
            new DiemSoExport($request->lop_hoc_id, $request->hoc_ky, $request->nam_hoc),
            $tenFile
        );
    }
}