<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Services\StudentSpreadsheetReader;
use App\Support\NamHoc;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use RuntimeException;

class HocSinhController extends Controller implements HasMiddleware
    {
    public static function middleware(): array
    {
        return [
            new Middleware('permission:xem-hoc-sinh', only: ['index', 'show']),
            new Middleware('permission:them-hoc-sinh', only: ['create', 'store', 'showImport', 'import', 'downloadTemplate']),
            new Middleware('permission:sua-hoc-sinh', only: ['edit', 'update']),
            new Middleware('permission:xoa-hoc-sinh', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = HocSinh::with('lopHoc')
            ->when($request->search, fn($q) =>
                $q->where(fn ($searchQuery) => $searchQuery
                    ->where('ho_ten', 'like', "%{$request->search}%")
                    ->orWhere('ma_hoc_sinh', 'like', "%{$request->search}%")
                    ->orWhere('cccd', 'like', "%{$request->search}%"))
            )
            ->when($request->lop_hoc_id, fn($q) => 
                $q->where('lop_hoc_id', $request->lop_hoc_id)
            )
            ->when($request->trang_thai, fn($q) => 
                $q->where('trang_thai', $request->trang_thai)
            )
            ->when($request->gioi_tinh, fn($q) => 
                $q->where('gioi_tinh', $request->gioi_tinh)
            )
            ->orderBy('ho_ten');

        $hocSinh = $query->paginate(20)->withQueryString();
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();

        return view('hoc-sinh.index', compact('hocSinh', 'lopHoc'));
    }

    public function create()
    {
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
        return view('hoc-sinh.create', compact('lopHoc'));
    }

    public function showImport()
    {
        abort_unless(auth()->user()->can('them-hoc-sinh'), 403);

        return view('hoc-sinh.import');
    }

    public function downloadTemplate()
    {
        abort_unless(auth()->user()->can('them-hoc-sinh'), 403);

        $headers = ['ho_ten', 'ngay_sinh', 'gioi_tinh', 'ten_lop', 'nam_hoc', 'cccd', 'so_dien_thoai', 'email', 'dan_toc', 'dia_chi_thuong_tru', 'ho_ten_cha', 'so_dien_thoai_cha', 'ho_ten_me', 'so_dien_thoai_me', 'email_phu_huynh'];
        $lop = LopHoc::where('is_active', true)->orderByDesc('nam_hoc')->orderBy('ten_lop')->first();
        $example = ['Nguyễn Văn An', '15/08/2010', 'Nam', $lop->ten_lop ?? '10A1', $lop->nam_hoc ?? NamHoc::hienTai(), '012345678901', '0912345678', 'an@example.com', 'Kinh', 'Hà Nội', 'Nguyễn Văn Bình', '0987654321', 'Trần Thị Hoa', '0976543210', 'phuhuynh.an@example.com'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Danh sách học sinh');
        // Định dạng văn bản để Excel không xóa số 0 đầu của CCCD / số điện thoại / ngày sinh
        $sheet->getStyle('A:O')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->fromArray([$headers]);
        foreach ($example as $i => $value) {
            $sheet->getCell([$i + 1, 2])->setValueExplicit($value, DataType::TYPE_STRING);
        }
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
        foreach (range('A', 'O') as $cot) {
            $sheet->getColumnDimension($cot)->setAutoSize(true);
        }

        // Trang tính tra cứu tên lớp / năm học hợp lệ
        $dsLop = $spreadsheet->createSheet()->setTitle('Danh sách lớp');
        $dsLop->fromArray([['ten_lop', 'nam_hoc', 'khoi']]);
        $dsLop->fromArray(
            LopHoc::where('is_active', true)->orderByDesc('nam_hoc')->orderBy('ten_lop')->get(['ten_lop', 'nam_hoc', 'khoi'])->toArray(),
            null, 'A2'
        );
        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'mau-danh-sach-hoc-sinh.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function import(Request $request, StudentSpreadsheetReader $reader)
    {
        abort_unless(auth()->user()->can('them-hoc-sinh'), 403);
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'Vui lòng chọn tệp danh sách.',
            'file.extensions' => 'Chỉ chấp nhận tệp Excel (.xlsx, .xls) hoặc CSV.',
            'file.max' => 'Tệp không được lớn hơn 5 MB.',
        ]);

        try {
            [$headers, $rows] = $reader->read($request->file('file'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (count($rows) < 1) {
            return back()->with('error', 'Tệp không có dòng dữ liệu học sinh.');
        }

        $requiredHeaders = ['ho_ten', 'ngay_sinh', 'gioi_tinh', 'ten_lop', 'nam_hoc'];
        if (array_diff($requiredHeaders, $headers)) {
            return back()->with('error', 'Tệp thiếu cột bắt buộc: '.implode(', ', array_diff($requiredHeaders, $headers)).'. Hãy dùng file mẫu hoặc đặt tên cột như "Họ và tên", "Ngày sinh", "Giới tính", "Lớp", "Năm học".');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $affectedClassIds = [];
        foreach ($rows as $row) {
            $line = $row['_dong'];
            $row = array_map(fn ($value) => trim((string) $value) === '' ? null : trim((string) $value), $row);
            if (count(array_filter($row)) <= 1) {
                continue;
            }
            $row['ngay_sinh'] = $this->normalizeSpreadsheetDate($row['ngay_sinh'] ?? null);
            $row['gioi_tinh'] = $this->normalizeGioiTinh($row['gioi_tinh'] ?? null);

            $validator = Validator::make($row, [
                'ho_ten' => ['required', 'string', 'max:100'],
                'ngay_sinh' => ['required', 'date_format:Y-m-d', 'before:today'],
                'gioi_tinh' => ['required', 'in:Nam,Nữ,Khác'],
                'ten_lop' => ['required', 'string', 'max:50'],
                'nam_hoc' => ['required', 'regex:/^\d{4}-\d{4}$/'],
                'cccd' => ['nullable', 'digits_between:9,12', 'unique:hoc_sinh,cccd'],
                'so_dien_thoai' => ['nullable', 'digits_between:9,12'],
                'email' => ['nullable', 'email', 'max:100'],
                'dan_toc' => ['nullable', 'string', 'max:50'],
                'dia_chi_thuong_tru' => ['nullable', 'string', 'max:255'],
                'ho_ten_cha' => ['nullable', 'string', 'max:100'],
                'so_dien_thoai_cha' => ['nullable', 'digits_between:9,12'],
                'ho_ten_me' => ['nullable', 'string', 'max:100'],
                'so_dien_thoai_me' => ['nullable', 'digits_between:9,12'],
                'email_phu_huynh' => ['nullable', 'email', 'max:100'],
            ], [
                'ngay_sinh.date_format' => 'Ngày sinh phải có dạng dd/mm/yyyy.',
                'gioi_tinh.in' => 'Giới tính phải là Nam, Nữ hoặc Khác.',
                'cccd.unique' => 'CCCD đã tồn tại trong hệ thống.',
            ]);

            if ($validator->fails()) {
                $errors[] = ['line' => $line, 'name' => $row['ho_ten'] ?? '—', 'message' => implode(' ', $validator->errors()->all())];
                continue;
            }

            $lop = LopHoc::where('ten_lop', $row['ten_lop'])->where('nam_hoc', $row['nam_hoc'])->first();
            if (! $lop) {
                $errors[] = ['line' => $line, 'name' => $row['ho_ten'], 'message' => "Không tìm thấy lớp {$row['ten_lop']} thuộc năm học {$row['nam_hoc']}." ];
                continue;
            }

            // Bỏ qua học sinh đã có (cùng họ tên, ngày sinh, lớp) để nhập lại tệp không bị trùng
            $daTonTai = HocSinh::where('ho_ten', $row['ho_ten'])
                ->whereDate('ngay_sinh', $row['ngay_sinh'])
                ->where('lop_hoc_id', $lop->id)
                ->exists();
            if ($daTonTai) {
                $skipped++;
                continue;
            }

            HocSinh::create([
                'ho_ten' => $row['ho_ten'],
                'ngay_sinh' => $row['ngay_sinh'],
                'gioi_tinh' => $row['gioi_tinh'],
                'lop_hoc_id' => $lop->id,
                'cccd' => $row['cccd'] ?? null,
                'so_dien_thoai' => $row['so_dien_thoai'] ?? null,
                'email' => $row['email'] ?? null,
                'dan_toc' => $row['dan_toc'] ?? 'Kinh',
                'dia_chi_thuong_tru' => $row['dia_chi_thuong_tru'] ?? null,
                'ho_ten_cha' => $row['ho_ten_cha'] ?? null,
                'so_dien_thoai_cha' => $row['so_dien_thoai_cha'] ?? null,
                'ho_ten_me' => $row['ho_ten_me'] ?? null,
                'so_dien_thoai_me' => $row['so_dien_thoai_me'] ?? null,
                'email_phu_huynh' => $row['email_phu_huynh'] ?? null,
                'trang_thai' => 'Đang học',
            ]);
            $imported++;
            $affectedClassIds[$lop->id] = true;
        }

        foreach (array_keys($affectedClassIds) as $classId) {
            $this->capNhatSiSo($classId);
        }

        return redirect()->route('hoc-sinh.import.form')->with('import_result', compact('imported', 'skipped', 'errors'));
    }

    private function normalizeGioiTinh(?string $value): ?string
    {
        return match (mb_strtolower((string) $value)) {
            'nam', 'm', 'male' => 'Nam',
            'nữ', 'nu', 'f', 'female' => 'Nữ',
            'khác', 'khac' => 'Khác',
            default => $value,
        };
    }

    private function normalizeSpreadsheetDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->format('Y-m-d');
        }
        foreach (['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        return $value;
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'ho_ten'             => 'required|string|max:100',
        'ngay_sinh'          => 'required|date|before:today',
        'gioi_tinh'          => 'required|in:Nam,Nữ,Khác',
        'dan_toc'            => 'nullable|string|max:50',
        'ton_giao'           => 'nullable|string|max:50',
        'cccd'               => 'nullable|digits_between:9,12|unique:hoc_sinh,cccd',
        'dia_chi_thuong_tru' => 'nullable|string|max:255',
        'dia_chi_tam_tru'    => 'nullable|string|max:255',
        'so_dien_thoai'      => 'nullable|digits_between:9,12',
        'email'              => 'nullable|email|max:100',
        'ho_ten_cha'         => 'nullable|string|max:100',
        'nghe_nghiep_cha'    => 'nullable|string|max:100',
        'so_dien_thoai_cha'  => 'nullable|digits_between:9,12',
        'ho_ten_me'          => 'nullable|string|max:100',
        'nghe_nghiep_me'     => 'nullable|string|max:100',
        'so_dien_thoai_me'   => 'nullable|digits_between:9,12',
        'email_phu_huynh'    => 'nullable|email|max:100',
        'lop_hoc_id'         => 'nullable|exists:lop_hoc,id',
        'trang_thai'         => 'required|in:Đang học,Đã tốt nghiệp,Thôi học,Chuyển trường',
        'anh_the'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'ho_ten.required'   => 'Vui lòng nhập họ tên',
        'ngay_sinh.required'=> 'Vui lòng nhập ngày sinh',
        'ngay_sinh.before'  => 'Ngày sinh phải trước ngày hôm nay',
        'cccd.unique'       => 'Số CCCD này đã tồn tại trong hệ thống',
        'gioi_tinh.required'=> 'Vui lòng chọn giới tính',
        'cccd.digits_between' => 'CCCD phải từ 9 đến 12 chữ số',
        'so_dien_thoai.digits_between' => 'Số điện thoại phải từ 9 đến 12 chữ số',
        'so_dien_thoai_cha.digits_between' => 'Số điện thoại của cha phải từ 9 đến 12 chữ số',
        'so_dien_thoai_me.digits_between' => 'Số điện thoại của mẹ phải từ 9 đến 12 chữ số',
        
        
    ]);

    // Upload ảnh thẻ
    if ($request->hasFile('anh_the')) {
        $validated['anh_the'] = $request->file('anh_the')
            ->store('hoc-sinh/anh-the', 'public');
    }

    // KHÔNG cần set ma_hoc_sinh — Model tự tạo trong booted()
    $hocSinh = HocSinh::create($validated);

    // Cập nhật sĩ số lớp
    if ($hocSinh->lop_hoc_id) {
        $this->capNhatSiSo($hocSinh->lop_hoc_id);
    }

    return redirect()->route('hoc-sinh.show', $hocSinh)
        ->with('success', "Đã thêm học sinh {$hocSinh->ho_ten} (Mã: {$hocSinh->ma_hoc_sinh}) thành công!");
}

    public function show(HocSinh $hocSinh)
    {
        $hocSinh->load(['lopHoc', 'diemSo.monHoc', 'hanhKiem', 'khenThuongKyLuat']);
        return view('hoc-sinh.show', compact('hocSinh'));
    }

    public function edit(HocSinh $hocSinh)
    {
        $lopHoc = LopHoc::where('is_active', true)->orderBy('ten_lop')->get();
        return view('hoc-sinh.edit', compact('hocSinh', 'lopHoc'));
    }

    public function update(Request $request, HocSinh $hocSinh)
    {
        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'ngay_sinh' => 'required|date|before:today',
            'gioi_tinh' => 'required|in:Nam,Nữ,Khác',
            'dan_toc' => 'nullable|string|max:50',
            'ton_giao' => 'nullable|string|max:50',
            'dia_chi_thuong_tru' => 'nullable|string|max:255',
            'dia_chi_tam_tru' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|digits_between:9,12|max:12',
            'email' => 'nullable|email|max:100',
            'cccd' => "nullable|digits_between:9,12|unique:hoc_sinh,cccd,{$hocSinh->id}",
            'ho_ten_cha' => 'nullable|string|max:100',
            'nghe_nghiep_cha' => 'nullable|string|max:100',
            'so_dien_thoai_cha' => 'nullable|digits_between:9,12|max:12',
            'ho_ten_me' => 'nullable|string|max:100',
            'nghe_nghiep_me' => 'nullable|string|max:100',
            'so_dien_thoai_me' => 'nullable|digits_between:9,12|max:12',
            'email_phu_huynh' => 'nullable|email|max:100',
            'trang_thai' => 'required|in:Đang học,Đã tốt nghiệp,Thôi học,Chuyển trường',
            'lop_hoc_id' => 'nullable|exists:lop_hoc,id',
            'anh_the' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $lopCu = $hocSinh->lop_hoc_id;

        if ($request->hasFile('anh_the')) {
            // Xóa ảnh cũ
            if ($hocSinh->anh_the) {
                Storage::disk('public')->delete($hocSinh->anh_the);
            }
            $validated['anh_the'] = $request->file('anh_the')
                ->store('hoc-sinh/anh-the', 'public');
        }

        $hocSinh->update($validated);

        // Cập nhật sĩ số
        if ($lopCu != ($validated['lop_hoc_id'] ?? null)) {
            if ($lopCu) $this->capNhatSiSo($lopCu);
            if ($validated['lop_hoc_id'] ?? null) $this->capNhatSiSo($validated['lop_hoc_id']);
        }

        return redirect()->route('hoc-sinh.show', $hocSinh)
            ->with('success', 'Cập nhật thông tin học sinh thành công!');
    }

    public function destroy(HocSinh $hocSinh)
    {
        $lopId = $hocSinh->lop_hoc_id;
        $tenHocSinh = $hocSinh->ho_ten;
        
        $hocSinh->delete(); // Soft delete
        
        if ($lopId) $this->capNhatSiSo($lopId);

        return redirect()->route('hoc-sinh.index')
            ->with('success', "Đã xóa học sinh {$tenHocSinh} khỏi hệ thống!");
    }

    private function capNhatSiSo(int $lopId): void
    {
        $lop = LopHoc::find($lopId);
        if ($lop) {
            $lop->update(['si_so' => $lop->hocSinh()->count()]);
        }
    }
}
