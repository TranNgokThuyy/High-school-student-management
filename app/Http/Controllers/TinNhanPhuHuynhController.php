<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Models\TinNhan;
use App\Models\TinNhanNguoiNhan;
use App\Services\ThongBaoPhuHuynhService;
use App\Support\NamHoc;
use Illuminate\Http\Request;

class TinNhanPhuHuynhController extends Controller
{
    public function __construct(private ThongBaoPhuHuynhService $thongBao) {}

    // Quản trị viên và kế toán gửi được toàn trường; giáo viên chủ nhiệm chỉ gửi lớp mình
    public static function guiToanTruong(): bool
    {
        $user = auth()->user();

        return $user->hasRole('quan-tri-vien') || $user->can('thu-hoc-phi');
    }

    public static function lopDuocGui()
    {
        return LopHoc::where('is_active', true)
            ->where('nam_hoc', NamHoc::hienTai())
            ->when(!self::guiToanTruong(), fn ($q) => $q->where('giao_vien_chu_nhiem_id', auth()->id()))
            ->orderBy('khoi')->orderBy('ten_lop')
            ->get();
    }

    public function index(Request $request)
    {
        $tinNhan = TinNhan::with('nguoiGui')
            ->withCount([
                'nguoiNhan',
                'nguoiNhan as da_xem_count' => fn ($q) => $q->whereNotNull('da_xem_luc'),
                'nguoiNhan as email_da_gui_count' => fn ($q) => $q->where('trang_thai_email', TinNhanNguoiNhan::DA_GUI),
                'nguoiNhan as sms_da_gui_count' => fn ($q) => $q->where('trang_thai_sms', TinNhanNguoiNhan::DA_GUI),
                'nguoiNhan as loi_count' => fn ($q) => $q->where(fn ($q) => $q
                    ->where('trang_thai_email', TinNhanNguoiNhan::LOI)
                    ->orWhere('trang_thai_sms', TinNhanNguoiNhan::LOI)
                    ->orWhere('trang_thai_zalo', TinNhanNguoiNhan::LOI)),
            ])
            ->when(!auth()->user()->hasRole('quan-tri-vien'), fn ($q) => $q->where('nguoi_gui_id', auth()->id()))
            ->when($request->loai, fn ($q) => $q->where('loai', $request->loai))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tin-nhan.index', compact('tinNhan'));
    }

    public function create(Request $request)
    {
        $lopHoc = self::lopDuocGui()->load(['hocSinh' => fn ($q) => $q->where('trang_thai', 'Đang học')->orderBy('ho_ten')]);
        $hocSinhChon = $request->filled('hoc_sinh_id')
            ? HocSinh::with('lopHoc')->find($request->hoc_sinh_id)
            : null;
        if ($hocSinhChon && !$lopHoc->contains('id', $hocSinhChon->lop_hoc_id)) {
            abort(403, 'Bạn chỉ được nhắn tin cho phụ huynh lớp mình chủ nhiệm!');
        }

        $bienThayThe = ThongBaoPhuHuynhService::BIEN_THAY_THE;
        $guiToanTruong = self::guiToanTruong();

        return view('tin-nhan.create', compact('lopHoc', 'hocSinhChon', 'bienThayThe', 'guiToanTruong'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pham_vi'        => 'required|in:toan_truong,khoi,lop,hoc_sinh',
            'khoi'           => 'required_if:pham_vi,khoi|nullable|in:10,11,12',
            'lop_hoc_ids'    => 'required_if:pham_vi,lop|array',
            'lop_hoc_ids.*'  => 'integer',
            'hoc_sinh_ids'   => 'required_if:pham_vi,hoc_sinh|array',
            'hoc_sinh_ids.*' => 'integer',
            'loai'           => 'required|in:' . implode(',', TinNhan::LOAI),
            'tieu_de'        => 'required|string|max:200',
            'noi_dung'       => 'required|string|max:2000',
            'kenh'           => 'nullable|array',
            'kenh.*'         => 'in:web,email,sms,zalo',
        ], [
            'lop_hoc_ids.required_if'  => 'Vui lòng chọn ít nhất một lớp',
            'hoc_sinh_ids.required_if' => 'Vui lòng chọn ít nhất một học sinh',
        ]);

        if (in_array($validated['pham_vi'], ['toan_truong', 'khoi']) && !self::guiToanTruong()) {
            abort(403, 'Bạn chỉ được gửi tin cho phụ huynh lớp mình chủ nhiệm!');
        }

        $lopDuocGui = self::lopDuocGui();
        $lopIds = match ($validated['pham_vi']) {
            'toan_truong' => $lopDuocGui->pluck('id'),
            'khoi'        => $lopDuocGui->where('khoi', $validated['khoi'])->pluck('id'),
            'lop'         => $lopDuocGui->pluck('id')->intersect($validated['lop_hoc_ids']),
            'hoc_sinh'    => $lopDuocGui->pluck('id'),
        };

        $hocSinh = HocSinh::whereIn('lop_hoc_id', $lopIds)
            ->where('trang_thai', 'Đang học')
            ->when($validated['pham_vi'] === 'hoc_sinh', fn ($q) => $q->whereIn('id', $validated['hoc_sinh_ids']))
            ->orderBy('ho_ten')
            ->get();

        if ($hocSinh->isEmpty()) {
            return back()->withInput()->with('error', 'Không có học sinh nào thuộc phạm vi đã chọn!');
        }

        $moTaPhamVi = match ($validated['pham_vi']) {
            'toan_truong' => 'Toàn trường',
            'khoi'        => 'Khối ' . $validated['khoi'],
            'lop'         => 'Lớp ' . $lopDuocGui->whereIn('id', $lopIds)->pluck('ten_lop')->implode(', '),
            'hoc_sinh'    => $hocSinh->count() === 1 ? 'PH em ' . $hocSinh->first()->ho_ten : $hocSinh->count() . ' học sinh',
        };

        $tinNhan = $this->thongBao->gui(
            $validated['tieu_de'], $validated['noi_dung'], $validated['loai'], $moTaPhamVi,
            $hocSinh, $validated['kenh'] ?? [], auth()->id(),
        );

        return redirect()->route('tin-nhan.show', $tinNhan)
            ->with('success', "Đã gửi tin nhắn tới phụ huynh của {$tinNhan->nguoiNhan()->count()} học sinh.");
    }

    public function show(TinNhan $tinNhan)
    {
        $this->kiemTraQuyenXem($tinNhan);

        $tinNhan->load(['nguoiGui', 'nguoiNhan' => fn ($q) => $q->with('hocSinh.lopHoc')]);

        return view('tin-nhan.show', compact('tinNhan'));
    }

    public function guiLai(TinNhan $tinNhan)
    {
        $this->kiemTraQuyenXem($tinNhan);

        $soLuong = $this->thongBao->guiLai($tinNhan);

        return back()->with('success', $soLuong ? "Đang gửi lại cho {$soLuong} phụ huynh." : 'Không có tin nào bị lỗi.');
    }

    private function kiemTraQuyenXem(TinNhan $tinNhan): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('quan-tri-vien') || $tinNhan->nguoi_gui_id == $user->id, 403);
    }
}
