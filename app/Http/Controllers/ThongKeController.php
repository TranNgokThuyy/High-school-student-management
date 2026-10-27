<?php
namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Services\HocPhiService;
use App\Services\TongKetService;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class ThongKeController extends Controller
{
    public const MUC = ['Tốt', 'Khá', 'Đạt', 'Chưa đạt'];

    public function __construct(private TongKetService $tongKet, private HocPhiService $hocPhi) {}

    public function index(Request $request)
    {
        $dsNamHoc = LopHoc::distinct()->orderBy('nam_hoc')->pluck('nam_hoc');
        $namHoc = $request->nam_hoc ?? ($dsNamHoc->contains(NamHoc::hienTai()) ? NamHoc::hienTai() : $dsNamHoc->last());
        $lopHoc = LopHoc::where('nam_hoc', $namHoc)->orderBy('khoi')->orderBy('ten_lop')->get();

        // Sĩ số theo khối, giới tính (học sinh đang học)
        $siSo = HocSinh::where('trang_thai', 'Đang học')
            ->whereIn('lop_hoc_id', $lopHoc->pluck('id'))
            ->with('lopHoc:id,khoi')
            ->get(['id', 'gioi_tinh', 'lop_hoc_id'])
            ->groupBy(fn ($hs) => $hs->lopHoc->khoi)
            ->map(fn ($ds) => ['Nam' => $ds->where('gioi_tinh', 'Nam')->count(), 'Nữ' => $ds->where('gioi_tinh', 'Nữ')->count()])
            ->sortKeys();

        // Kết quả cả năm theo khối
        $theoKhoi = [];
        $lenLop = ['dat' => 0, 'tong' => 0];
        foreach ($lopHoc as $lop) {
            foreach ($this->tongKet->tongKetLop($lop, 'Ca năm')['ket_qua'] as $r) {
                $k = &$theoKhoi[$lop->khoi];
                $k['hoc_tap'][$r['hoc_tap'] ?? 'Chưa đủ điểm'] = ($k['hoc_tap'][$r['hoc_tap'] ?? 'Chưa đủ điểm'] ?? 0) + 1;
                $k['ren_luyen'][$r['ren_luyen'] ?? 'Chưa xếp loại'] = ($k['ren_luyen'][$r['ren_luyen'] ?? 'Chưa xếp loại'] ?? 0) + 1;
                unset($k);
                if ($r['hoc_tap'] && $r['ren_luyen']) {
                    $lenLop['tong']++;
                    $lenLop['dat'] += $r['len_lop']['dat'] ? 1 : 0;
                }
            }
        }
        ksort($theoKhoi);

        // Chuyên cần theo lớp (từ điểm danh)
        $chuyenCan = [];
        foreach ($lopHoc as $lop) {
            $th = DiemDanh::tongHop($lop->id, ...NamHoc::khoangThoiGian($namHoc, 'Ca năm'));
            $siSoLop = $lop->hocSinh()->where('trang_thai', 'Đang học')->count();
            if ($th['so_buoi'] > 0 && $siSoLop > 0) {
                $vang = collect($th['hoc_sinh'])->sum(fn ($h) => $h['vang_co_phep'] + $h['vang_khong_phep']);
                $chuyenCan[$lop->ten_lop] = round(100 - $vang / ($th['so_buoi'] * $siSoLop) * 100, 1);
            }
        }

        // Học phí theo lớp (chỉ quản trị viên / kế toán)
        $hocPhi = [];
        if ($request->user()->hasRole('quan-tri-vien') || $request->user()->can('thu-hoc-phi')) {
            foreach ($lopHoc as $lop) {
                $tong = $this->hocPhi->congNoLop($lop)['tong'];
                if ($tong['phai_nop'] > 0) {
                    $hocPhi[$lop->ten_lop] = ['Đã thu' => $tong['da_nop'], 'Còn nợ' => $tong['con_no']];
                }
            }
        }

        // So sánh kết quả học tập toàn trường qua các năm
        $quaCacNam = [];
        foreach ($dsNamHoc->take(-5) as $nh) {
            foreach (LopHoc::where('nam_hoc', $nh)->get() as $lop) {
                foreach ($this->tongKet->tongKetLop($lop, 'Ca năm')['ket_qua'] as $r) {
                    if ($r['hoc_tap']) {
                        $quaCacNam[$nh][$r['hoc_tap']] = ($quaCacNam[$nh][$r['hoc_tap']] ?? 0) + 1;
                    }
                }
            }
        }

        $tongThu = collect($hocPhi)->sum('Đã thu');
        $tongPhai = $tongThu + collect($hocPhi)->sum('Còn nợ');
        $chiSo = [
            'hoc_sinh'   => $siSo->sum(fn ($g) => $g['Nam'] + $g['Nữ']),
            'len_lop'    => $lenLop['tong'] ? round($lenLop['dat'] / $lenLop['tong'] * 100, 1) : null,
            'chuyen_can' => $chuyenCan ? round(collect($chuyenCan)->avg(), 1) : null,
            'thu_hoc_phi'=> $tongPhai ? round($tongThu / $tongPhai * 100, 1) : null,
        ];

        return view('thong-ke.index', compact(
            'dsNamHoc', 'namHoc', 'siSo', 'theoKhoi', 'chuyenCan', 'hocPhi', 'quaCacNam', 'chiSo'
        ) + ['muc' => self::MUC]);
    }
}
