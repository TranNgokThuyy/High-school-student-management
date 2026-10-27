<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\KiemTraLai;
use App\Models\MonHoc;
use App\Models\RenLuyenHe;
use App\Models\LopHoc;
use App\Services\ThongBaoPhuHuynhService;
use App\Services\TongKetService;
use App\Support\NamHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TongKetController extends Controller
{
    public function __construct(private TongKetService $tongKet) {}

    // Quản trị viên xem mọi lớp; giáo viên chủ nhiệm xem lớp mình chủ nhiệm
    private function getLopHoc()
    {
        $user = auth()->user();

        return LopHoc::query()
            ->when(!$user->hasRole('quan-tri-vien'), fn($q) => $q->where('giao_vien_chu_nhiem_id', $user->id))
            ->orderByDesc('nam_hoc')
            ->orderBy('ten_lop')
            ->get();
    }

    private function kiemTraQuyenLop(LopHoc $lop): void
    {
        $user = auth()->user();
        if (!$user->hasRole('quan-tri-vien') && $lop->giao_vien_chu_nhiem_id != $user->id) {
            abort(403, 'Bạn chỉ được xem tổng kết lớp mình chủ nhiệm!');
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'nullable|exists:lop_hoc,id',
            'hoc_ky'     => 'nullable|in:1,2,Ca năm',
        ]);

        $lopHoc = $this->getLopHoc();
        $lopId  = $request->lop_hoc_id ?? $lopHoc->first()?->id;
        $hocKy  = $request->hoc_ky ?? 'Ca năm';

        $lop = null;
        $monHoc = collect();
        $ketQua = collect();
        $thongKe = [];

        if ($lopId) {
            $lop = LopHoc::with('giaoVienChuNhiem')->findOrFail($lopId);
            $this->kiemTraQuyenLop($lop);

            ['mon_hoc' => $monHoc, 'ket_qua' => $ketQua] = $this->tongKet->tongKetLop($lop, $hocKy);

            $thongKe = [
                'hoc_tap'   => $ketQua->countBy(fn($r) => $r['hoc_tap'] ?? 'Chưa đủ điểm')->all(),
                'ren_luyen' => $ketQua->countBy(fn($r) => $r['ren_luyen'] ?? 'Chưa xếp loại')->all(),
                'danh_hieu' => $ketQua->countBy(fn($r) => $r['danh_hieu'] ?? 'Không')->all(),
                'len_lop'   => $hocKy === 'Ca năm'
                    ? $ketQua->countBy(fn($r) => $r['len_lop']['ket_qua'])->all()
                    : [],
            ];
        }

        return view('tong-ket.index', compact('lopHoc', 'lop', 'hocKy', 'monHoc', 'ketQua', 'thongKe'));
    }

    // Gửi kết quả học tập, rèn luyện của học kỳ / cả năm cho phụ huynh từng học sinh
    public function guiKetQua(Request $request, ThongBaoPhuHuynhService $thongBao)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'hoc_ky'     => 'required|in:1,2,Ca năm',
            'kenh'       => 'nullable|array',
            'kenh.*'     => 'in:email,sms',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $this->kiemTraQuyenLop($lop);

        ['mon_hoc' => $monHoc, 'ket_qua' => $ketQua] = $this->tongKet->tongKetLop($lop, $request->hoc_ky);
        $ketQua = $ketQua->filter(fn ($r) => $r['dang_o_lop'])->keyBy(fn ($r) => $r['hoc_sinh']->id);
        if ($ketQua->isEmpty()) {
            return back()->with('error', 'Lớp không có học sinh để gửi kết quả.');
        }

        $tenKy = $request->hoc_ky === 'Ca năm' ? 'cả năm' : 'học kỳ ' . $request->hoc_ky;

        $tinNhan = $thongBao->gui(
            "Kết quả học tập {$tenKy} năm học {$lop->nam_hoc}",
            "Kết quả học tập và rèn luyện {$tenKy} của từng học sinh.",
            'Kết quả học tập',
            "Lớp {$lop->ten_lop}",
            $ketQua->pluck('hoc_sinh'),
            $request->input('kenh', []),
            auth()->id(),
            function ($hs) use ($ketQua, $monHoc, $tenKy) {
                $r = $ketQua[$hs->id];
                $diem = $monHoc->filter(fn ($m) => array_key_exists($m->id, $r['diem_mon']))
                    ->map(fn ($m) => $m->ten_mon . ': ' . \App\Support\Diem::hienThi($r['diem_mon'][$m->id]))
                    ->implode('; ');

                return "Kết quả {$tenKy} của em {$hs->ho_ten}:\n"
                    . "- Điểm trung bình các môn: {$diem}\n"
                    . '- Kết quả học tập: ' . ($r['hoc_tap'] ?? 'Chưa đủ điểm') . "\n"
                    . '- Kết quả rèn luyện: ' . ($r['ren_luyen'] ?? 'Chưa xếp loại') . "\n"
                    . "- Số buổi nghỉ: {$r['so_buoi_nghi']}"
                    . ($r['danh_hieu'] ? "\n- Danh hiệu: {$r['danh_hieu']}" : '')
                    . ($r['len_lop'] ? "\n- Kết quả xét cuối năm: {$r['len_lop']['ket_qua']}" : '');
            },
        );

        return redirect()->route('tin-nhan.show', $tinNhan)
            ->with('success', "Đã gửi kết quả {$tenKy} tới phụ huynh của {$ketQua->count()} học sinh.");
    }

    // Kiểm tra lại & rèn luyện hè (TT22 Điều 12, 13): học sinh chưa được lên lớp lần đầu
    public function he(Request $request)
    {
        $request->validate(['lop_hoc_id' => 'nullable|exists:lop_hoc,id']);
        $lopHoc = $this->getLopHoc();
        $lop = $request->lop_hoc_id ? LopHoc::findOrFail($request->lop_hoc_id) : null;

        $danhSach = collect();
        $monHoc = collect();
        if ($lop) {
            ['mon_hoc' => $monHoc, 'ket_qua' => $ketQua] = $this->tongKet->tongKetLop($lop, 'Ca năm');
            $danhSach = $ketQua->filter(fn ($r) => $r['dang_o_lop']
                && $r['so_buoi_nghi'] <= TongKetService::SO_BUOI_NGHI_TOI_DA
                && ($r['hoc_tap'] === 'Chưa đạt' || $r['ren_luyen'] === 'Chưa đạt'))
                ->map(fn ($r) => $r + [
                    // Môn phải kiểm tra lại: ĐTB cả năm < 5.0 hoặc nhận xét Chưa đạt
                    'mon_kiem_tra_lai' => $r['hoc_tap'] === 'Chưa đạt'
                        ? $monHoc->filter(fn ($m) => array_key_exists($m->id, $r['diem_mon'])
                            && \App\Support\Diem::thap($r['diem_mon'][$m->id]))->values()
                        : collect(),
                ])
                ->values();
        }

        return view('tong-ket.he', compact('lopHoc', 'lop', 'danhSach'));
    }

    public function luuHe(Request $request)
    {
        $request->validate([
            'lop_hoc_id'       => 'required|exists:lop_hoc,id',
            'kiem_tra_lai'     => 'nullable|array',
            'kiem_tra_lai.*.*' => 'nullable|string|max:10',
            'ren_luyen_he'     => 'nullable|array',
            'ren_luyen_he.*'   => 'nullable|in:Đạt,Chưa đạt',
        ]);
        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $hocSinhIds = $lop->hocSinh()->pluck('id')->map(fn ($id) => (int) $id);

        $loi = [];
        DB::transaction(function () use ($request, $lop, $hocSinhIds, &$loi) {
            foreach ($request->input('kiem_tra_lai', []) as $hsId => $dsMon) {
                if (!$hocSinhIds->contains((int) $hsId)) {
                    continue;
                }
                foreach ($dsMon as $monId => $giaTri) {
                    $mon = MonHoc::find($monId);
                    if (!$mon || $giaTri === null || $giaTri === '') {
                        continue;
                    }
                    $duLieu = ['diem' => null, 'ket_qua_nhan_xet' => null, 'lop_hoc_id' => $lop->id, 'nguoi_nhap_id' => auth()->id()];
                    if ($mon->laMonNhanXet()) {
                        if (!in_array($giaTri, ['Đạt', 'Chưa đạt'], true)) {
                            continue;
                        }
                        $duLieu['ket_qua_nhan_xet'] = $giaTri;
                    } else {
                        $so = str_replace(',', '.', $giaTri);
                        if (!is_numeric($so) || $so < 0 || $so > 10) {
                            $loi[] = "Điểm \"{$giaTri}\" môn {$mon->ten_mon} không hợp lệ.";
                            continue;
                        }
                        $duLieu['diem'] = round((float) $so, 2);
                    }
                    KiemTraLai::updateOrCreate(
                        ['hoc_sinh_id' => (int) $hsId, 'mon_hoc_id' => $mon->id, 'nam_hoc' => $lop->nam_hoc], $duLieu
                    );
                }
            }

            foreach ($request->input('ren_luyen_he', []) as $hsId => $ketQua) {
                if (!$hocSinhIds->contains((int) $hsId) || !$ketQua) {
                    continue;
                }
                RenLuyenHe::updateOrCreate(
                    ['hoc_sinh_id' => (int) $hsId, 'nam_hoc' => $lop->nam_hoc],
                    ['lop_hoc_id' => $lop->id, 'ket_qua' => $ketQua, 'nguoi_nhap_id' => auth()->id()]
                );
            }
        });

        return redirect()->route('tong-ket.he', ['lop_hoc_id' => $lop->id])
            ->with($loi ? 'error' : 'success', $loi ? implode(' ', $loi) : 'Đã lưu kết quả kiểm tra lại / rèn luyện hè và xét lại lên lớp.');
    }

    // Màn hình xét lên lớp / chuyển học sinh sang năm học mới
    public function lenLop(Request $request)
    {
        $request->validate(['lop_hoc_id' => 'nullable|exists:lop_hoc,id']);

        $lopHoc = $this->getLopHoc();
        $lop = $request->lop_hoc_id ? LopHoc::findOrFail($request->lop_hoc_id) : null;

        $ketQua = collect();
        $lopLenLop = collect();
        $lopOLai = collect();
        $namHocMoi = null;

        if ($lop) {
            $ketQua = $this->tongKet->tongKetLop($lop, 'Ca năm')['ket_qua']
                ->filter(fn($r) => $r['dang_o_lop'] && $r['hoc_sinh']->trang_thai === 'Đang học')
                ->values();

            $namHocMoi = NamHoc::keTiep($lop->nam_hoc);

            if ($lop->khoi !== '12') {
                $lopLenLop = LopHoc::where('nam_hoc', $namHocMoi)
                    ->where('khoi', (string) ((int) $lop->khoi + 1))
                    ->orderBy('ten_lop')->get();
            }
            $lopOLai = LopHoc::where('nam_hoc', $namHocMoi)
                ->where('khoi', $lop->khoi)
                ->orderBy('ten_lop')->get();
        }

        return view('tong-ket.len-lop', compact('lopHoc', 'lop', 'ketQua', 'lopLenLop', 'lopOLai', 'namHocMoi'));
    }

    public function luuLenLop(Request $request)
    {
        $request->validate([
            'lop_hoc_id'     => 'required|exists:lop_hoc,id',
            'lop_dich_id'    => 'nullable|exists:lop_hoc,id',
            'lop_o_lai_id'   => 'nullable|exists:lop_hoc,id',
            'hoc_sinh_ids'   => 'nullable|array',
            'hoc_sinh_ids.*' => 'integer',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $namHocMoi = NamHoc::keTiep($lop->nam_hoc);
        $laKhoi12 = $lop->khoi === '12';

        $lopDich = $request->lop_dich_id ? LopHoc::find($request->lop_dich_id) : null;
        $lopOLai = $request->lop_o_lai_id ? LopHoc::find($request->lop_o_lai_id) : null;

        if (!$laKhoi12 && (!$lopDich || $lopDich->nam_hoc !== $namHocMoi || (int) $lopDich->khoi !== (int) $lop->khoi + 1)) {
            return back()->with('error', "Vui lòng chọn lớp khối " . ((int) $lop->khoi + 1) . " của năm học {$namHocMoi}!");
        }
        if ($lopOLai && ($lopOLai->nam_hoc !== $namHocMoi || $lopOLai->khoi !== $lop->khoi)) {
            return back()->with('error', "Lớp cho học sinh ở lại phải là lớp khối {$lop->khoi} năm học {$namHocMoi}!");
        }

        $hocSinhLop = HocSinh::where('lop_hoc_id', $lop->id)->where('trang_thai', 'Đang học')->get();
        $duocLen = collect($request->hoc_sinh_ids ?? [])->map(fn($id) => (int) $id);

        $soLen = 0;
        $soOLai = 0;

        DB::transaction(function () use ($hocSinhLop, $duocLen, $laKhoi12, $lopDich, $lopOLai, &$soLen, &$soOLai) {
            foreach ($hocSinhLop as $hs) {
                if ($duocLen->contains($hs->id)) {
                    $laKhoi12
                        ? $hs->update(['trang_thai' => 'Đã tốt nghiệp'])
                        : $hs->update(['lop_hoc_id' => $lopDich->id]);
                    $soLen++;
                } elseif ($lopOLai) {
                    $hs->update(['lop_hoc_id' => $lopOLai->id]);
                    $soOLai++;
                }
            }
        });

        $lop->capNhatSiSo();
        $lopDich?->capNhatSiSo();
        $lopOLai?->capNhatSiSo();

        $thongBao = $laKhoi12
            ? "Đã công nhận tốt nghiệp cho {$soLen} học sinh lớp {$lop->ten_lop}."
            : "Đã chuyển {$soLen} học sinh lên lớp {$lopDich->ten_lop}.";
        if ($soOLai) {
            $thongBao .= " {$soOLai} học sinh ở lại chuyển vào lớp {$lopOLai->ten_lop}.";
        }

        return redirect()->route('tong-ket.len-lop', ['lop_hoc_id' => $lop->id])->with('success', $thongBao);
    }
}
