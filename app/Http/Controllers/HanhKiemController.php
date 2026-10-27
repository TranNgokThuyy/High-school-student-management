<?php
namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\HanhKiem;
use App\Models\LopHoc;
use App\Support\NamHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HanhKiemController extends Controller
{
    private function getLopHoc()
    {
        $user = auth()->user();
        if ($user->hasRole('giao-vien-chu-nhiem')) {
            return LopHoc::where('giao_vien_chu_nhiem_id', $user->id)
                ->where('is_active', true)
                ->withCount('hocSinh')
                ->orderBy('ten_lop')
                ->get();
        }
        return LopHoc::where('is_active', true)
            ->withCount('hocSinh')
            ->orderBy('ten_lop')
            ->get();
    }

    private function kiemTraQuyenLop(LopHoc $lop): void
    {
        $user = auth()->user();
        if ($user->hasRole('giao-vien-chu-nhiem') && !$user->hasRole('quan-tri-vien')) {
            if ($lop->giao_vien_chu_nhiem_id !== $user->id) {
                abort(403, 'Bạn không có quyền nhập hạnh kiểm cho lớp này!');
            }
        }
    }

    private function tinhHanhKiemCaNam(int $hocSinhId, int $lopHocId, string $namHoc): void
    {
        $hk1 = HanhKiem::where([
            'hoc_sinh_id' => $hocSinhId,
            'lop_hoc_id'  => $lopHocId,
            'hoc_ky'      => '1',
            'nam_hoc'     => $namHoc,
        ])->first();

        $hk2 = HanhKiem::where([
            'hoc_sinh_id' => $hocSinhId,
            'lop_hoc_id'  => $lopHocId,
            'hoc_ky'      => '2',
            'nam_hoc'     => $namHoc,
        ])->first();

        if (!$hk1 || !$hk2) return;
        if (!$hk1->xep_loai_hanh_kiem || !$hk2->xep_loai_hanh_kiem) return;

        $xepLoaiCaNam = HanhKiem::xepLoaiCaNam(
            $hk1->xep_loai_hanh_kiem,
            $hk2->xep_loai_hanh_kiem
        );

        $tongBuoi   = ($hk1->so_buoi_hoc ?? 0) + ($hk2->so_buoi_hoc ?? 0);
        $tongVangCP = ($hk1->so_buoi_vang_co_phep ?? 0) + ($hk2->so_buoi_vang_co_phep ?? 0);
        $tongVangKP = ($hk1->so_buoi_vang_khong_phep ?? 0) + ($hk2->so_buoi_vang_khong_phep ?? 0);
        $tongDiTre  = ($hk1->so_buoi_di_tre ?? 0) + ($hk2->so_buoi_di_tre ?? 0);

        // Dùng firstOrNew + save thay vì updateOrCreate để tránh race condition
        $caNam = HanhKiem::firstOrNew([
            'hoc_sinh_id' => $hocSinhId,
            'lop_hoc_id'  => $lopHocId,
            'hoc_ky'      => 'Ca năm',
            'nam_hoc'     => $namHoc,
        ]);

        $caNam->xep_loai_hanh_kiem      = $xepLoaiCaNam;
        $caNam->so_buoi_hoc             = $tongBuoi;
        $caNam->so_buoi_vang_co_phep    = $tongVangCP;
        $caNam->so_buoi_vang_khong_phep = $tongVangKP;
        $caNam->so_buoi_di_tre          = $tongDiTre;
        $caNam->ghi_chu                 = 'Tự động tính từ HK1 + HK2';
        $caNam->giao_vien_chu_nhiem_id  = auth()->id();
        $caNam->save();
    }

    public function index(Request $request)
    {
        $lopHoc = $this->getLopHoc();
        $user   = auth()->user();

        $query = HanhKiem::with(['hocSinh', 'lopHoc'])
->whereHas('hocSinh'); // ← Chỉ lấy bản ghi có học sinh tồn tại
        // Giáo viên chỉ thấy lớp mình chủ nhiệm
        if ($user->hasRole('giao-vien-chu-nhiem') && !$user->hasRole('quan-tri-vien')) {
            $lopIds = $lopHoc->pluck('id');
            if ($lopIds->isNotEmpty()) {
                $query->whereIn('lop_hoc_id', $lopIds);
            }
        }

        $query->when($request->lop_hoc_id, fn($q) => $q->where('lop_hoc_id', $request->lop_hoc_id))
              ->when($request->hoc_ky,     fn($q) => $q->where('hoc_ky', $request->hoc_ky))
              ->when($request->nam_hoc,    fn($q) => $q->where('nam_hoc', $request->nam_hoc))
              ->when($request->xep_loai,   fn($q) => $q->where('xep_loai_hanh_kiem', $request->xep_loai))
              ->orderByDesc('updated_at');

        $hanhKiem = $query->paginate(20)->withQueryString();

        return view('hanh-kiem.index', compact('hanhKiem', 'lopHoc'));
    }

    public function chonLop()
    {
        $user   = auth()->user();
        $lopHoc = $this->getLopHoc();

        // Giáo viên không có lớp chủ nhiệm
        if ($user->hasRole('giao-vien-chu-nhiem') && !$user->hasRole('quan-tri-vien') && $lopHoc->isEmpty()) {
            return redirect()->route('hanh-kiem.index')
                ->with('error', 'Bạn chưa được phân công chủ nhiệm lớp nào!');
        }

        // Giáo viên có đúng 1 lớp → tự động redirect
        if ($user->hasRole('giao-vien-chu-nhiem') && !$user->hasRole('quan-tri-vien') && $lopHoc->count() === 1) {
            $lop           = $lopHoc->first();
            $namHocHienTai = \App\Support\NamHoc::hienTai();
            return redirect()->route('hanh-kiem.form', [
                'lop_hoc_id' => $lop->id,
                'hoc_ky'     => '1',
                'nam_hoc'    => $namHocHienTai,
            ]);
        }

        $namHocHienTai = \App\Support\NamHoc::hienTai();
        return view('hanh-kiem.chon-lop', compact('lopHoc', 'namHocHienTai'));
    }

    public function nhapHanhKiem(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'hoc_ky'     => 'required|in:1,2',
            'nam_hoc'    => 'required|string',
        ]);

        $lop = LopHoc::with([
            'hocSinh' => fn($q) => $q->orderBy('ho_ten'),
            'giaoVienChuNhiem',
        ])->findOrFail($request->lop_hoc_id);

        $this->kiemTraQuyenLop($lop);

        if ($lop->hocSinh->count() === 0) {
            return redirect()->route('hanh-kiem.nhap')
                ->with('error', "Lớp {$lop->ten_lop} chưa có học sinh nào!");
        }

        $hanhKiemHienCo = HanhKiem::where([
            'lop_hoc_id' => $request->lop_hoc_id,
            'hoc_ky'     => $request->hoc_ky,
            'nam_hoc'    => $request->nam_hoc,
        ])->get()->keyBy('hoc_sinh_id');

        $hocKyKia = $request->hoc_ky == '1' ? '2' : '1';

        $hanhKiemKyKia = HanhKiem::where([
            'lop_hoc_id' => $request->lop_hoc_id,
            'hoc_ky'     => $hocKyKia,
            'nam_hoc'    => $request->nam_hoc,
        ])->get()->keyBy('hoc_sinh_id');

        $hanhKiemCaNam = HanhKiem::where([
            'lop_hoc_id' => $request->lop_hoc_id,
            'hoc_ky'     => 'Ca năm',
            'nam_hoc'    => $request->nam_hoc,
        ])->get()->keyBy('hoc_sinh_id');

        // Số liệu chuyên cần lấy từ chức năng điểm danh hằng ngày
        $tongHopDiemDanh = DiemDanh::tongHop(
            $lop->id,
            ...NamHoc::khoangThoiGian($request->nam_hoc, $request->hoc_ky)
        );

        return view('hanh-kiem.nhap', compact(
            'lop', 'hanhKiemHienCo', 'hanhKiemKyKia',
            'hanhKiemCaNam', 'request', 'hocKyKia', 'tongHopDiemDanh'
        ));
    }

    public function luuHanhKiem(Request $request)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'hoc_ky'     => 'required|in:1,2',
            'nam_hoc'    => 'required|string',
            'hanh_kiem'  => 'required|array',
            'hanh_kiem.*.xep_loai'        => 'nullable|in:' . implode(',', HanhKiem::MUC),
            'hanh_kiem.*.so_buoi_hoc'     => 'nullable|integer|min:0|max:300',
            'hanh_kiem.*.vang_co_phep'    => 'nullable|integer|min:0|max:300',
            'hanh_kiem.*.vang_khong_phep' => 'nullable|integer|min:0|max:300',
            'hanh_kiem.*.di_tre'          => 'nullable|integer|min:0|max:300',
            'hanh_kiem.*.ghi_chu'         => 'nullable|string|max:255',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $this->kiemTraQuyenLop($lop);

        // Chỉ nhận học sinh của lớp, đúng năm học của lớp; số buổi vắng không vượt số buổi học
        $tenHocSinh = $lop->hocSinh()->pluck('ho_ten', 'id');
        $loi = [];
        if ($request->nam_hoc !== $lop->nam_hoc) {
            $loi['nam_hoc'] = "Lớp này thuộc năm học {$lop->nam_hoc}.";
        }
        foreach ($request->hanh_kiem as $hocSinhId => $data) {
            if (!$tenHocSinh->has($hocSinhId)) {
                $loi["hanh_kiem.{$hocSinhId}"] = 'Có học sinh không thuộc lớp này.';
                continue;
            }
            $vang = (int) ($data['vang_co_phep'] ?? 0) + (int) ($data['vang_khong_phep'] ?? 0);
            if (filled($data['so_buoi_hoc'] ?? null) && $vang > (int) $data['so_buoi_hoc']) {
                $loi["hanh_kiem.{$hocSinhId}.vang_khong_phep"] = "{$tenHocSinh[$hocSinhId]}: tổng số buổi vắng ({$vang}) lớn hơn số buổi học ({$data['so_buoi_hoc']}).";
            }
        }
        if ($loi) {
            throw \Illuminate\Validation\ValidationException::withMessages($loi);
        }

        $soLuong   = 0;
        $tinhCaNam = 0;

        DB::transaction(function () use ($request, &$soLuong, &$tinhCaNam) {
            foreach ($request->hanh_kiem as $hocSinhId => $data) {

                // Dùng firstOrNew + save thay vì updateOrCreate để tránh duplicate
                $hk = HanhKiem::firstOrNew([
                    'hoc_sinh_id' => (int) $hocSinhId,
                    'lop_hoc_id'  => (int) $request->lop_hoc_id,
                    'hoc_ky'      => $request->hoc_ky,
                    'nam_hoc'     => $request->nam_hoc,
                ]);

                $hk->xep_loai_hanh_kiem      = $data['xep_loai'] ?: null;
                $hk->so_buoi_hoc             = $data['so_buoi_hoc'] ?? 0;
                $hk->so_buoi_vang_co_phep    = $data['vang_co_phep'] ?? 0;
                $hk->so_buoi_vang_khong_phep = $data['vang_khong_phep'] ?? 0;
                $hk->so_buoi_di_tre          = $data['di_tre'] ?? 0;
                $hk->ghi_chu                 = $data['ghi_chu'] ?? null;
                $hk->giao_vien_chu_nhiem_id  = auth()->id();
                $hk->save();

                $soLuong++;

                // Tự động tính hạnh kiểm cả năm
                $this->tinhHanhKiemCaNam(
                    (int) $hocSinhId,
                    (int) $request->lop_hoc_id,
                    $request->nam_hoc
                );
                $tinhCaNam++;
            }
        });

        $thongBao = "Đã lưu hạnh kiểm HK{$request->hoc_ky} cho {$soLuong} học sinh!";

        $daTinhCaNam = HanhKiem::where([
            'lop_hoc_id' => $request->lop_hoc_id,
            'hoc_ky'     => 'Ca năm',
            'nam_hoc'    => $request->nam_hoc,
        ])->count();

        if ($daTinhCaNam > 0) {
            $thongBao .= " Đã tự động tính hạnh kiểm cả năm cho {$daTinhCaNam} học sinh.";
        }

        return redirect()->route('hanh-kiem.index', [
            'lop_hoc_id' => $request->lop_hoc_id,
            'hoc_ky'     => $request->hoc_ky,
            'nam_hoc'    => $request->nam_hoc,
        ])->with('success', $thongBao);
    }
}