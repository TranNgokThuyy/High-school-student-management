@extends('layouts.app')
@section('title', 'Xét lên lớp')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-up-circle text-success me-2"></i>Xét lên lớp & chuyển năm học</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('tong-ket.he', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-outline-warning">
            <i class="bi bi-sun me-2"></i>Kiểm tra lại & rèn luyện hè
        </a>
        <a href="{{ route('tong-ket.index', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Tổng kết
        </a>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Lớp cần xét (năm học cũ)</label>
                <select name="lop_hoc_id" class="form-select" required>
                    <option value="">-- Chọn lớp --</option>
                    @foreach($lopHoc as $l)
                        <option value="{{ $l->id }}" {{ $lop?->id == $l->id ? 'selected' : '' }}>{{ $l->ten_lop }} ({{ $l->nam_hoc }}) — {{ $l->si_so }} HS</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xét</button>
            </div>
        </form>
    </div>
</div>

@if($lop)
<form method="POST" action="{{ route('tong-ket.luu-len-lop') }}"
      onsubmit="return confirm('Xác nhận chuyển học sinh? Thao tác này cập nhật lớp hiện tại của học sinh.')">
    @csrf
    <input type="hidden" name="lop_hoc_id" value="{{ $lop->id }}">

    <div class="card table-card mb-4">
        <div class="card-body row g-3">
            @if($lop->khoi === '12')
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    Lớp khối 12: học sinh được chọn sẽ được chuyển trạng thái <b>Đã tốt nghiệp</b>.
                </div>
            </div>
            @else
            <div class="col-md-6">
                <label class="form-label fw-semibold">Lớp lên (khối {{ (int) $lop->khoi + 1 }}, năm học {{ $namHocMoi }}) <span class="text-danger">*</span></label>
                <select name="lop_dich_id" class="form-select" required>
                    <option value="">-- Chọn lớp --</option>
                    @foreach($lopLenLop as $l)
                        <option value="{{ $l->id }}">{{ $l->ten_lop }} ({{ $l->si_so }} HS)</option>
                    @endforeach
                </select>
                @if($lopLenLop->isEmpty())
                    <div class="form-text text-danger">
                        Chưa có lớp khối {{ (int) $lop->khoi + 1 }} cho năm học {{ $namHocMoi }}.
                        @can('them-lop-hoc')<a href="{{ route('lop-hoc.create') }}">Tạo lớp mới</a>@endcan
                    </div>
                @endif
            </div>
            @endif
            <div class="col-md-6">
                <label class="form-label fw-semibold">Lớp cho học sinh ở lại (khối {{ $lop->khoi }}, năm học {{ $namHocMoi }})</label>
                <select name="lop_o_lai_id" class="form-select">
                    <option value="">-- Giữ nguyên lớp cũ --</option>
                    @foreach($lopOLai as $l)
                        <option value="{{ $l->id }}">{{ $l->ten_lop }} ({{ $l->si_so }} HS)</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0">Học sinh lớp {{ $lop->ten_lop }} ({{ $ketQua->count() }})</h6>
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="chonTatCa" onchange="document.querySelectorAll('.chon-hs').forEach(c => c.checked = this.checked)">
                <label class="form-check-label small" for="chonTatCa">Chọn tất cả</label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width:50px" class="text-center">Lên</th>
                        <th>Họ và tên</th>
                        <th class="text-center">Học tập</th>
                        <th class="text-center">Rèn luyện</th>
                        <th class="text-center">Nghỉ</th>
                        <th>Kết quả xét</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ketQua as $r)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input chon-hs" name="hoc_sinh_ids[]"
                                   value="{{ $r['hoc_sinh']->id }}" {{ $r['len_lop']['dat'] ? 'checked' : '' }}>
                        </td>
                        <td class="fw-semibold">{{ $r['hoc_sinh']->ho_ten }} <small class="text-muted">{{ $r['hoc_sinh']->ma_hoc_sinh }}</small></td>
                        <td class="text-center">{{ $r['hoc_tap'] ?? 'Chưa đủ điểm' }}</td>
                        <td class="text-center">{{ $r['ren_luyen'] ?? '—' }}</td>
                        <td class="text-center">{{ $r['so_buoi_nghi'] }}</td>
                        <td>
                            <span class="badge bg-{{ $r['len_lop']['dat'] ? 'success' : 'warning text-dark' }}">{{ $r['len_lop']['ket_qua'] }}</span>
                            @foreach($r['len_lop']['ly_do'] as $lyDo)
                                <div class="small text-muted">{{ $lyDo }}</div>
                            @endforeach
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Lớp không còn học sinh đang học (có thể đã được xét lên lớp).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Học sinh đủ điều kiện được chọn sẵn; có thể chọn thêm học sinh đạt sau kiểm tra lại / rèn luyện hè.</small>
            <button class="btn btn-success" {{ $ketQua->isEmpty() ? 'disabled' : '' }}>
                <i class="bi bi-check2-circle me-2"></i>{{ $lop->khoi === '12' ? 'Công nhận tốt nghiệp' : 'Chuyển lên lớp' }}
            </button>
        </div>
    </div>
</form>
@endif
@endsection
