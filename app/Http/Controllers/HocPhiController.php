<?php
namespace App\Http\Controllers;

use App\Exports\CongNoExport;
use App\Models\HocSinh;
use App\Models\KhoanThu;
use App\Models\LopHoc;
use App\Models\MienGiam;
use App\Models\PhieuThu;
use App\Services\HocPhiService;
use App\Services\ThongBaoPhuHuynhService;
use App\Support\NamHoc;
use App\Support\Tien;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HocPhiController extends Controller
{
    public function __construct(private HocPhiService $hocPhi) {}

    // Quản trị viên / kế toán xem toàn trường; GVCN chỉ xem lớp mình chủ nhiệm
    private function xemToanTruong(): bool
    {
        $user = auth()->user();

        return $user->hasRole('quan-tri-vien') || $user->can('thu-hoc-phi');
    }

    private function getLopHoc()
    {
        return LopHoc::where('is_active', true)
            ->when(!$this->xemToanTruong(), fn ($q) => $q->where('giao_vien_chu_nhiem_id', auth()->id()))
            ->orderByDesc('nam_hoc')
            ->orderBy('ten_lop')
            ->get();
    }

    private function kiemTraQuyenLop(?LopHoc $lop): void
    {
        if (!$this->xemToanTruong() && (!$lop || $lop->giao_vien_chu_nhiem_id != auth()->id())) {
            abort(403, 'Bạn chỉ được xem học phí của lớp mình chủ nhiệm!');
        }
    }

    public function index(Request $request)
    {
        $lopHoc = $this->getLopHoc();
        $lopId  = $request->lop_hoc_id
            ?? $lopHoc->firstWhere('nam_hoc', NamHoc::hienTai())?->id
            ?? $lopHoc->first()?->id;

        $lop = null;
        $congNo = null;
        if ($lopId) {
            $lop = LopHoc::findOrFail($lopId);
            $this->kiemTraQuyenLop($lop);
            $congNo = $this->hocPhi->congNoLop($lop, $request->integer('khoan_thu_id') ?: null);
        }

        $dsKhoanThu = $lop ? KhoanThu::apDungChoLop($lop)->orderBy('id')->get() : collect();

        return view('hoc-phi.index', compact('lopHoc', 'lop', 'congNo', 'dsKhoanThu'));
    }

    public function xuatExcel(Request $request)
    {
        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $this->kiemTraQuyenLop($lop);

        $congNo = $this->hocPhi->congNoLop($lop, $request->integer('khoan_thu_id') ?: null);

        return Excel::download(new CongNoExport($lop, $congNo), "cong-no-{$lop->ten_lop}-{$lop->nam_hoc}.xlsx");
    }

    // Gửi tin nhắc nộp tiền cho phụ huynh các học sinh còn nợ trong lớp
    public function nhacNo(Request $request, ThongBaoPhuHuynhService $thongBao)
    {
        $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
            'kenh'       => 'nullable|array',
            'kenh.*'     => 'in:email,sms',
        ]);

        $lop = LopHoc::findOrFail($request->lop_hoc_id);
        $this->kiemTraQuyenLop($lop);

        $conNo = $this->hocPhi->congNoLop($lop)['hoc_sinh']->where('con_no', '>', 0)->keyBy(fn ($r) => $r['hoc_sinh']->id);
        if ($conNo->isEmpty()) {
            return back()->with('error', "Lớp {$lop->ten_lop} không có học sinh còn nợ.");
        }

        $tinNhan = $thongBao->gui(
            'Nhắc nộp học phí và các khoản thu',
            'Danh sách khoản còn phải nộp của từng học sinh.',
            'Học phí',
            "Lớp {$lop->ten_lop} - HS còn nợ",
            $conNo->pluck('hoc_sinh'),
            $request->input('kenh', []),
            auth()->id(),
            function (HocSinh $hs) use ($conNo) {
                $r = $conNo[$hs->id];
                $dong = $r['chi_tiet']->where('con_no', '>', 0)->map(fn ($c) => '- ' . $c['khoan_thu']->ten . ': '
                    . Tien::dinhDang($c['con_no'])
                    . ($c['khoan_thu']->han_nop ? ' (hạn ' . $c['khoan_thu']->han_nop->format('d/m/Y') . ')' : ''));

                return "Em {$hs->ho_ten} còn các khoản chưa nộp:\n" . $dong->implode("\n")
                    . "\nTổng cộng: " . Tien::dinhDang($r['con_no'])
                    . ".\nKính mong quý phụ huynh hoàn thành đúng hạn. Xin cảm ơn!";
            },
        );

        return redirect()->route('tin-nhan.show', $tinNhan)
            ->with('success', "Đã gửi nhắc nộp tiền tới phụ huynh của {$conNo->count()} học sinh.");
    }

    public function hocSinh(HocSinh $hocSinh)
    {
        $lop = $hocSinh->lopHoc;
        $this->kiemTraQuyenLop($lop);

        $congNo   = $lop ? $this->hocPhi->congNoHocSinh($hocSinh, $lop) : collect();
        $mienGiam = $lop ? MienGiam::where('hoc_sinh_id', $hocSinh->id)->where('nam_hoc', $lop->nam_hoc)->first() : null;
        $phieuThu = PhieuThu::with(['khoanThu', 'nguoiThu'])
            ->where('hoc_sinh_id', $hocSinh->id)
            ->orderByDesc('ngay_thu')->orderByDesc('id')
            ->get();

        return view('hoc-phi.hoc-sinh', compact('hocSinh', 'lop', 'congNo', 'mienGiam', 'phieuThu'));
    }

    public function thu(Request $request, HocSinh $hocSinh)
    {
        $validated = $request->validate([
            'khoan_thu_id' => 'required|exists:khoan_thu,id',
            'so_tien'      => 'required|integer|min:1000',
            'ngay_thu'     => 'required|date|before_or_equal:today',
            'hinh_thuc'    => 'required|in:Tiền mặt,Chuyển khoản',
            'nguoi_nop'    => 'nullable|string|max:100',
            'ghi_chu'      => 'nullable|string|max:255',
        ], [
            'so_tien.min' => 'Số tiền thu tối thiểu 1.000 đ',
        ]);

        $lop = $hocSinh->lopHoc;
        abort_unless($lop, 422, 'Học sinh chưa được xếp lớp.');

        $phieu = DB::transaction(function () use ($validated, $hocSinh, $lop) {
            // Khóa các phiếu của học sinh để hai người thu cùng lúc không vượt số còn nợ
            PhieuThu::where('hoc_sinh_id', $hocSinh->id)->lockForUpdate()->get();

            $dong = $this->hocPhi->congNoHocSinh($hocSinh, $lop)
                ->first(fn ($d) => $d['khoan_thu']->id == $validated['khoan_thu_id']);

            if (!$dong) {
                return 'Khoản thu này không áp dụng cho lớp của học sinh!';
            }
            if ($validated['so_tien'] > $dong['con_no']) {
                return 'Số tiền vượt quá số còn phải nộp (' . Tien::dinhDang($dong['con_no']) . ')!';
            }

            return PhieuThu::create($validated + [
                'hoc_sinh_id'  => $hocSinh->id,
                'nguoi_thu_id' => auth()->id(),
            ]);
        });

        if (is_string($phieu)) {
            return back()->withInput()->with('error', $phieu);
        }

        return redirect()->route('hoc-phi.hoc-sinh', $hocSinh)
            ->with('success', "Đã lập phiếu thu {$phieu->so_phieu}: " . Tien::dinhDang($phieu->so_tien))
            ->with('phieu_moi', $phieu->id);
    }

    public function inPhieu(PhieuThu $phieuThu)
    {
        $phieuThu->load(['hocSinh.lopHoc', 'khoanThu', 'nguoiThu']);
        $this->kiemTraQuyenLop($phieuThu->hocSinh->lopHoc);

        return view('hoc-phi.phieu-thu', ['phieu' => $phieuThu]);
    }

    public function huyPhieu(Request $request, PhieuThu $phieuThu)
    {
        $request->validate(['ly_do_huy' => 'required|string|max:255'], [
            'ly_do_huy.required' => 'Vui lòng nhập lý do hủy phiếu',
        ]);

        if ($phieuThu->da_huy) {
            return back()->with('error', 'Phiếu này đã bị hủy trước đó!');
        }

        $phieuThu->update([
            'da_huy'       => true,
            'ly_do_huy'    => $request->ly_do_huy,
            'nguoi_huy_id' => auth()->id(),
            'huy_luc'      => now(),
        ]);

        return back()->with('success', "Đã hủy phiếu thu {$phieuThu->so_phieu}.");
    }

    // Sổ thu: danh sách phiếu theo khoảng ngày, tổng theo hình thức
    public function soThu(Request $request)
    {
        $tuNgay  = $request->filled('tu_ngay') ? Carbon::parse($request->tu_ngay) : today()->startOfMonth();
        $denNgay = $request->filled('den_ngay') ? Carbon::parse($request->den_ngay) : today();

        $query = PhieuThu::with(['hocSinh.lopHoc', 'khoanThu', 'nguoiThu'])
            ->whereDate('ngay_thu', '>=', $tuNgay->toDateString())
            ->whereDate('ngay_thu', '<=', $denNgay->toDateString())
            ->when($request->hinh_thuc, fn ($q) => $q->where('hinh_thuc', $request->hinh_thuc))
            ->when($request->filled('so_phieu'), fn ($q) => $q->where('so_phieu', 'like', "%{$request->so_phieu}%"));

        $tongHop = (clone $query)->hopLe()
            ->selectRaw('hinh_thuc, COUNT(*) as so_phieu, SUM(so_tien) as tong')
            ->groupBy('hinh_thuc')
            ->get()
            ->keyBy('hinh_thuc');

        $phieuThu = $query->orderByDesc('ngay_thu')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('hoc-phi.so-thu', compact('phieuThu', 'tongHop', 'tuNgay', 'denNgay'));
    }
}
