@extends('layouts.app')
@section('title', 'Khen thưởng - Kỷ luật')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i>Khen thưởng - Kỷ luật</h4>
    @can('them-khen-thuong')
    <a href="{{ route('khen-thuong.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Thêm mới</a>
    @endcan
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Năm học</label>
                <select name="nam_hoc" class="form-select">
                    @foreach($dsNamHoc as $nh)
                        <option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>{{ $nh }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Loại</label>
                <select name="loai" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach(['Khen thưởng', 'Kỷ luật'] as $l)
                        <option value="{{ $l }}" {{ request('loai') == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lớp</label>
                <select name="lop_hoc_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($lopHoc as $lop)
                        <option value="{{ $lop->id }}" {{ request('lop_hoc_id') == $lop->id ? 'selected' : '' }}>{{ $lop->ten_lop }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tên học sinh</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tìm theo tên...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Học sinh</th>
                    <th>Lớp</th>
                    <th>Loại</th>
                    <th>Hình thức</th>
                    <th>Nội dung</th>
                    <th>Số QĐ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($danhSach as $kt)
                <tr>
                    <td class="text-nowrap">{{ $kt->ngay_quyet_dinh->format('d/m/Y') }}</td>
                    <td>
                        @if($kt->hocSinh)
                        <a href="{{ route('hoc-sinh.show', $kt->hocSinh) }}" class="fw-semibold text-decoration-none">{{ $kt->hocSinh->ho_ten }}</a>
                        @endif
                    </td>
                    <td>{{ $kt->hocSinh?->lopHoc?->ten_lop ?? '—' }}</td>
                    <td><span class="badge bg-{{ $kt->loai === 'Khen thưởng' ? 'success' : 'danger' }}">{{ $kt->loai }}</span></td>
                    <td>{{ $kt->hinh_thuc }}</td>
                    <td class="small">{{ $kt->noi_dung }}</td>
                    <td class="small text-muted">{{ $kt->so_quyet_dinh ?? '—' }}</td>
                    <td class="text-end">
                        @can('xoa-khen-thuong')
                        <form method="POST" action="{{ route('khen-thuong.destroy', $kt) }}" onsubmit="return confirm('Xóa bản ghi này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-2 mb-2"></i>Chưa có dữ liệu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($danhSach->hasPages())
    <div class="card-footer bg-white">{{ $danhSach->links() }}</div>
    @endif
</div>
@endsection
