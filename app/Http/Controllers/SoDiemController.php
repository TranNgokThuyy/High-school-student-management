<?php
namespace App\Http\Controllers;

use App\Models\LopHoc;
use App\Models\PhanCongGiangDay;
use App\Services\SoDiemService;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SoDiemController extends Controller
{
    // Quản trị viên, GVCN của lớp hoặc giáo viên được phân công dạy lớp
    public function xuat(LopHoc $lopHoc, SoDiemService $soDiem)
    {
        $user = auth()->user();
        $duocXem = $user->hasRole('quan-tri-vien')
            || $lopHoc->giao_vien_chu_nhiem_id == $user->id
            || PhanCongGiangDay::where('giao_vien_id', $user->id)->where('lop_hoc_id', $lopHoc->id)->exists();
        abort_unless($duocXem, 403, 'Bạn không chủ nhiệm hoặc không dạy lớp này!');

        $spreadsheet = $soDiem->taoSpreadsheet($lopHoc);
        $tenFile = 'so-diem-' . Str::slug($lopHoc->ten_lop) . '-' . $lopHoc->nam_hoc . '.xlsx';

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $tenFile,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
