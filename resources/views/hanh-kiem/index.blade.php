@extends('layouts.app')
@section('title', 'Hạnh kiểm & Chuyên cần')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Hạnh kiểm & Chuyên cần
        </h4>
        <p class="text-muted mb-0">Quản lý hạnh kiểm và tình trạng chuyên cần của học sinh</p>
    </div>
    @can('them-hanh-kiem')
    <a href="{{ route('hanh-kiem.nhap') }}" class="btn btn-primary">
        <i class="bi bi-pencil-square me-2"></i>Nhập hạnh kiểm
    </a>
    @endcan
</div>

{{-- Form lọc --}}
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Lớp học</label>
                <select name="lop_hoc_id" class="form-select">
                    <option value="">-- Tất cả lớp --</option>
                    @foreach($lopHoc as $lop)
                        <option value="{{ $lop->id }}" {{ request('lop_hoc_id') == $lop->id ? 'selected' : '' }}>
                            {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Học kỳ</label>
                <select name="hoc_ky" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="1"       {{ request('hoc_ky') == '1'       ? 'selected' : '' }}>Học kỳ 1</option>
                    <option value="2"       {{ request('hoc_ky') == '2'       ? 'selected' : '' }}>Học kỳ 2</option>
                    <option value="Ca năm" {{ request('hoc_ky') == 'Ca năm' ? 'selected' : '' }}>Cả năm</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Năm học</label>
                <input type="text" name="nam_hoc" class="form-control"
                       placeholder="2024-2025"
                       value="{{ request('nam_hoc') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Hạnh kiểm</label>
                <select name="xep_loai" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="Tốt"        {{ request('xep_loai') == 'Tốt'        ? 'selected' : '' }}>Tốt</option>
                    <option value="Khá"        {{ request('xep_loai') == 'Khá'        ? 'selected' : '' }}>Khá</option>
                    <option value="Trung bình" {{ request('xep_loai') == 'Trung bình' ? 'selected' : '' }}>Trung bình</option>
                    <option value="Yếu"        {{ request('xep_loai') == 'Yếu'        ? 'selected' : '' }}>Yếu</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('hanh-kiem.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Thống kê nhanh --}}
@if($hanhKiem->count() > 0)
<div class="row g-3 mb-4">
    @php
        $allItems  = $hanhKiem->getCollection();
        $thongKe   = [
            'Tốt'        => $allItems->where('xep_loai_hanh_kiem', 'Tốt')->count(),
            'Khá'        => $allItems->where('xep_loai_hanh_kiem', 'Khá')->count(),
            'Trung bình' => $allItems->where('xep_loai_hanh_kiem', 'Trung bình')->count(),
            'Yếu'        => $allItems->where('xep_loai_hanh_kiem', 'Yếu')->count(),
        ];
        $statColors = ['Tốt'=>'success','Khá'=>'primary','Trung bình'=>'warning','Yếu'=>'danger'];
    @endphp
    @foreach($thongKe as $loai => $soLuong)
    <div class="col-md-3">
        <div class="card border-0 bg-{{ $statColors[$loai] }} bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-3 fw-bold text-{{ $statColors[$loai] }}">{{ $soLuong }}</div>
                <div class="text-muted small">Hạnh kiểm {{ $loai }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Bảng danh sách --}}
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Học sinh</th>
                    <th>Lớp</th>
                    <th class="text-center">Học kỳ</th>
                    <th>Năm học</th>
                    <th class="text-center">Hạnh kiểm</th>
                    <th class="text-center">Tổng buổi</th>
                    <th class="text-center">Vắng CP</th>
                    <th class="text-center">Vắng KP</th>
                    <th class="text-center">Đi trễ</th>
                    <th class="text-center">Tỷ lệ CC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hanhKiem as $hk)
                <tr>
                    {{-- ✅ Kiểm tra null hocSinh --}}
                    <td>
                        @if($hk->hocSinh)
                            <a href="{{ route('hoc-sinh.show', $hk->hocSinh) }}"
                               class="fw-semibold text-decoration-none text-dark">
                                {{ $hk->hocSinh->ho_ten }}
                            </a>
                            <div class="text-muted small">{{ $hk->hocSinh->ma_hoc_sinh }}</div>
                        @else
                            <span class="text-muted fst-italic">Học sinh đã xóa</span>
                        @endif
                    </td>

                    {{-- ✅ Kiểm tra null lopHoc --}}
                    <td>
                        @if($hk->lopHoc)
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $hk->lopHoc->ten_lop }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($hk->hoc_ky === 'Ca năm')
                            <span class="badge bg-success text-white border">Cả năm</span>
                        @else
                            <span class="badge bg-light text-dark border">HK{{ $hk->hoc_ky }}</span>
                        @endif
                    </td>

                    <td>{{ $hk->nam_hoc }}</td>

                    <td class="text-center">
                        @if($hk->xep_loai_hanh_kiem)
                            @php
                                $hkColors = [
                                    'Tốt'        => 'success',
                                    'Khá'        => 'primary',
                                    'Trung bình' => 'warning',
                                    'Yếu'        => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $hkColors[$hk->xep_loai_hanh_kiem] ?? 'secondary' }}">
                                {{ $hk->xep_loai_hanh_kiem }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-center fw-semibold">
                        {{ $hk->so_buoi_hoc ?? '—' }}
                    </td>
                    <td class="text-center text-warning fw-semibold">
                        {{ ($hk->so_buoi_vang_co_phep ?? 0) > 0 ? $hk->so_buoi_vang_co_phep : '—' }}
                    </td>
                    <td class="text-center text-danger fw-semibold">
                        {{ ($hk->so_buoi_vang_khong_phep ?? 0) > 0 ? $hk->so_buoi_vang_khong_phep : '—' }}
                    </td>
                    <td class="text-center text-secondary">
                        {{ ($hk->so_buoi_di_tre ?? 0) > 0 ? $hk->so_buoi_di_tre : '—' }}
                    </td>

                    <td class="text-center">
                        @php $tyLe = $hk->ty_le_chuyen_can ?? 0; @endphp
                        <div class="d-flex align-items-center gap-1 justify-content-center">
                            <div class="progress flex-grow-1" style="height:6px;min-width:50px">
                                <div class="progress-bar bg-{{ $tyLe >= 90 ? 'success' : ($tyLe >= 70 ? 'warning' : 'danger') }}"
                                     style="width:{{ $tyLe }}%"></div>
                            </div>
                            <small class="fw-semibold text-{{ $tyLe >= 90 ? 'success' : ($tyLe >= 70 ? 'warning' : 'danger') }}">
                                {{ $tyLe }}%
                            </small>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">Chưa có dữ liệu hạnh kiểm</span>
                        @can('them-hanh-kiem')
                        <div class="mt-3">
                            <a href="{{ route('hanh-kiem.nhap') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square me-2"></i>Nhập hạnh kiểm ngay
                            </a>
                        </div>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($hanhKiem->hasPages())
    <div class="card-footer bg-white">
        {{ $hanhKiem->links() }}
    </div>
    @endif
</div>
@endsection