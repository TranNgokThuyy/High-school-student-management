@extends('layouts.app')
@section('title', 'Phân công giảng dạy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 text-primary me-2"></i>Phân công giảng dạy</h4>
    @can('them-phan-cong')
    <a href="{{ route('phan-cong.create', ['nam_hoc' => $namHoc]) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Thêm phân công
    </a>
    @endcan
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Năm học</label>
                <select name="nam_hoc" class="form-select" onchange="this.form.submit()">
                    @foreach($dsNamHoc as $nh)
                        <option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>{{ $nh }}</option>
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
                <label class="form-label">Giáo viên</label>
                <select name="giao_vien_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($giaoVien as $gv)
                        <option value="{{ $gv->id }}" {{ request('giao_vien_id') == $gv->id ? 'selected' : '' }}>{{ $gv->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Môn</label>
                <select name="mon_hoc_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($monHoc as $mon)
                        <option value="{{ $mon->id }}" {{ request('mon_hoc_id') == $mon->id ? 'selected' : '' }}>{{ $mon->ten_mon }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">Danh sách phân công ({{ $phanCong->count() }})</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Lớp</th>
                            <th>Môn học</th>
                            <th>Giáo viên</th>
                            <th class="text-center">Học kỳ</th>
                            <th class="text-center">Tiết/tuần</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($phanCong as $pc)
                        <tr>
                            <td class="fw-semibold">{{ $pc->lopHoc?->ten_lop }}</td>
                            <td>{{ $pc->monHoc?->ten_mon }}</td>
                            <td>{{ $pc->giaoVien?->name }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $pc->hoc_ky }}</span></td>
                            <td class="text-center">{{ $pc->monHoc?->so_tiet_tuan }}</td>
                            <td class="text-end">
                                @can('xoa-phan-cong')
                                <form method="POST" action="{{ route('phan-cong.destroy', $pc) }}"
                                      onsubmit="return confirm('Xóa phân công này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>Chưa có phân công nào trong năm học {{ $namHoc }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0"><i class="bi bi-person-workspace me-2"></i>Tải giảng dạy (tiết/tuần)</h6>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($giaoVien->filter(fn($gv) => isset($taiGiangDay[$gv->id])) as $gv)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $gv->name }}</span>
                    <span class="badge {{ $taiGiangDay[$gv->id] > 19 ? 'bg-danger' : 'bg-primary' }}">{{ $taiGiangDay[$gv->id] }}</span>
                </li>
                @empty
                <li class="list-group-item text-muted small">Chưa có dữ liệu</li>
                @endforelse
            </ul>
            <div class="card-footer bg-white small text-muted">Định mức chuẩn GV THPT: 17 tiết/tuần</div>
        </div>
    </div>
</div>
@endsection
