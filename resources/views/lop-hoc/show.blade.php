@extends('layouts.app')
@section('title', 'Chi tiết lớp ' . $lopHoc->ten_lop)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-building text-primary me-2"></i>Lớp {{ $lopHoc->ten_lop }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('lop-hoc.index') }}">Quản lý lớp học</a></li>
                <li class="breadcrumb-item active">{{ $lopHoc->ten_lop }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @can('sua-lop-hoc')
        <a href="{{ route('lop-hoc.edit', $lopHoc) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-2"></i>Chỉnh sửa
        </a>
        @endcan
        <a href="{{ route('lop-hoc.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Thông tin lớp --}}
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle text-primary me-2"></i>Thông tin lớp</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:40%">Tên lớp</td>
                        <td class="fw-bold fs-5">{{ $lopHoc->ten_lop }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Khối</td>
                        <td><span class="badge bg-primary">Khối {{ $lopHoc->khoi }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Năm học</td>
                        <td>{{ $lopHoc->nam_hoc }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Sĩ số</td>
                        <td>
                            <span class="badge bg-light text-dark border fs-6">
                                {{ $lopHoc->hocSinh->count() }} học sinh
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">GVCN</td>
                        <td>{{ $lopHoc->giaoVienChuNhiem?->name ?? 'Chưa phân công' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Trạng thái</td>
                        <td>
                            @if($lopHoc->is_active)
                                <span class="badge bg-success">Đang hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Ngừng hoạt động</span>
                            @endif
                        </td>
                    </tr>
                    @if($lopHoc->ghi_chu)
                    <tr>
                        <td class="text-muted fw-semibold">Ghi chú</td>
                        <td>{{ $lopHoc->ghi_chu }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Danh sách học sinh --}}
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-people text-success me-2"></i>
                    Danh sách học sinh ({{ $lopHoc->hocSinh->count() }})
                </h6>
                @can('xem-diem')
                <a href="{{ route('diem-so.bang-diem', ['lop_hoc_id' => $lopHoc->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-journal-text me-1"></i>Xem điểm
                </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">STT</th>
                            <th>Họ và tên</th>
                            <th>Ngày sinh</th>
                            <th class="text-center">Giới tính</th>
                            <th>Số điện thoại</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lopHoc->hocSinh->sortBy('ho_ten') as $i => $hs)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $hs->ho_ten }}</td>
                            <td>{{ $hs->ngay_sinh->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $hs->gioi_tinh == 'Nam' ? 'info' : 'pink' }} bg-opacity-10 text-{{ $hs->gioi_tinh == 'Nam' ? 'info' : 'danger' }}">
                                    {{ $hs->gioi_tinh }}
                                </span>
                            </td>
                            <td>{{ $hs->so_dien_thoai ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('hoc-sinh.show', $hs) }}"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                                Lớp chưa có học sinh nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection