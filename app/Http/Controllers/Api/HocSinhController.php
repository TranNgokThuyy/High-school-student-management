<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HocSinh;
use App\Models\LopHoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class HocSinhController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:xem-hoc-sinh', only: ['index', 'show', 'classes']),
            new Middleware('permission:them-hoc-sinh', only: ['store']),
            new Middleware('permission:sua-hoc-sinh', only: ['update']),
            new Middleware('permission:xoa-hoc-sinh', only: ['destroy']),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $students = HocSinh::with('lopHoc:id,ten_lop,nam_hoc')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($search) => $search
                ->where('ho_ten', 'like', '%'.$request->string('search').'%')
                ->orWhere('ma_hoc_sinh', 'like', '%'.$request->string('search').'%')
                ->orWhere('cccd', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('lop_hoc_id'), fn ($query) => $query->where('lop_hoc_id', $request->integer('lop_hoc_id')))
            ->when($request->filled('trang_thai'), fn ($query) => $query->where('trang_thai', $request->string('trang_thai')))
            ->orderBy('ho_ten')
            ->paginate(min(max($request->integer('per_page', 15), 5), 100));

        return response()->json($students);
    }

    public function classes(): JsonResponse
    {
        return response()->json(['data' => LopHoc::where('is_active', true)->orderBy('ten_lop')->get(['id', 'ten_lop', 'nam_hoc'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $student = HocSinh::create($this->validated($request));
        $this->syncClassSize($student->lop_hoc_id);

        return response()->json(['message' => 'Đã thêm học sinh.', 'data' => $student->load('lopHoc')], 201);
    }

    public function show(HocSinh $hocSinh): JsonResponse
    {
        return response()->json(['data' => $hocSinh->load('lopHoc')]);
    }

    public function update(Request $request, HocSinh $hocSinh): JsonResponse
    {
        $oldClassId = $hocSinh->lop_hoc_id;
        $hocSinh->update($this->validated($request, $hocSinh));
        $this->syncClassSize($oldClassId);
        $this->syncClassSize($hocSinh->lop_hoc_id);

        return response()->json(['message' => 'Đã cập nhật học sinh.', 'data' => $hocSinh->load('lopHoc')]);
    }

    public function destroy(HocSinh $hocSinh): JsonResponse
    {
        $classId = $hocSinh->lop_hoc_id;
        $hocSinh->delete();
        $this->syncClassSize($classId);

        return response()->json(['message' => 'Đã xóa học sinh.']);
    }

    private function validated(Request $request, ?HocSinh $student = null): array
    {
        return $request->validate([
            'ho_ten' => ['required', 'string', 'max:100'],
            'ngay_sinh' => ['required', 'date', 'before:today'],
            'gioi_tinh' => ['required', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'lop_hoc_id' => ['nullable', 'exists:lop_hoc,id'],
            'cccd' => ['nullable', 'digits_between:9,12', Rule::unique('hoc_sinh', 'cccd')->ignore($student?->id)],
            'so_dien_thoai' => ['nullable', 'digits_between:9,12'],
            'email' => ['nullable', 'email', 'max:100'],
            'dan_toc' => ['nullable', 'string', 'max:50'],
            'dia_chi_thuong_tru' => ['nullable', 'string', 'max:255'],
            'trang_thai' => ['required', Rule::in(['Đang học', 'Đã tốt nghiệp', 'Thôi học', 'Chuyển trường'])],
        ]);
    }

    private function syncClassSize(?int $classId): void
    {
        if ($classId && ($class = LopHoc::find($classId))) {
            $class->update(['si_so' => $class->hocSinh()->count()]);
        }
    }
}
