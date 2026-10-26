<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\LopHoc;
use App\Services\StudentSpreadsheetReader;
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

        $headers = ['ho_ten', 'ngay_sinh', 'gioi_tinh', 'ten_lop', 'nam_hoc', 'cccd', 'so_dien_thoai', 'email', 'dan_toc', 'dia_chi_thuong_tru', 'ho_ten_cha', 'so_dien_thoai_cha', 'ho_ten_me', 'so_dien_thoai_me'];
        $example = ['Nguyễn Văn An', '15/08/2010', 'Nam', '10A1', '2026-2027', '012345678901', '0912345678', 'an@example.com', 'Kinh', 'Hà Nội', 'Nguyễn Văn Bình', '0987654321', 'Trần Thị Hoa', '0976543210'];

        return response()->streamDownload(function () use ($headers, $example) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);
            fputcsv($output, $example);
            fclose($output);
        }, 'mau-danh-sach-hoc-sinh.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, StudentSpreadsheetReader $reader)
    {
        abort_unless(auth()->user()->can('them-hoc-sinh'), 403);
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:5120'],
        ], [
            'file.required' => 'Vui lòng chọn tệp danh sách.',
            'file.mimes' => 'Chỉ chấp nhận tệp CSV hoặc XLSX.',
            'file.max' => 'Tệp không được lớn hơn 5 MB.',
        ]);

        try {
            $rows = $reader->read($request->file('file'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (count($rows) < 2) {
            return back()->with('error', 'Tệp không có dòng dữ liệu học sinh.');
        }

        $headers = array_map(fn ($header) => Str::slug(trim((string) $header), '_'), array_shift($rows));
        $requiredHeaders = ['ho_ten', 'ngay_sinh', 'gioi_tinh', 'ten_lop', 'nam_hoc'];
        if (array_diff($requiredHeaders, $headers)) {
            return back()->with('error', 'Tệp thiếu cột bắt buộc: '.implode(', ', array_diff($requiredHeaders, $headers)).'.');
        }

        $imported = 0;
        $errors = [];
        $affectedClassIds = [];
        foreach ($rows as $index => $values) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $values = array_pad($values, count($headers), '');
            $row = array_combine($headers, array_slice($values, 0, count($headers)));
            $row = array_map(fn ($value) => trim((string) $value) === '' ? null : trim((string) $value), $row);
            $row['ngay_sinh'] = $this->normalizeSpreadsheetDate($row['ngay_sinh'] ?? null);

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
            ]);

            $line = $index + 2;
            if ($validator->fails()) {
                $errors[] = ['line' => $line, 'name' => $row['ho_ten'] ?? '—', 'message' => implode(' ', $validator->errors()->all())];
                continue;
            }

            $lop = LopHoc::where('ten_lop', $row['ten_lop'])->where('nam_hoc', $row['nam_hoc'])->first();
            if (! $lop) {
                $errors[] = ['line' => $line, 'name' => $row['ho_ten'], 'message' => "Không tìm thấy lớp {$row['ten_lop']} thuộc năm học {$row['nam_hoc']}." ];
                continue;
            }

            DB::transaction(function () use ($row, $lop) {
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
                    'trang_thai' => 'Đang học',
                ]);
            });
            $imported++;
            $affectedClassIds[$lop->id] = true;
        }

        foreach (array_keys($affectedClassIds) as $classId) {
            $this->capNhatSiSo($classId);
        }

        return redirect()->route('hoc-sinh.import.form')->with('import_result', compact('imported', 'errors'));
    }

    private function normalizeSpreadsheetDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->format('Y-m-d');
        }
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
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
