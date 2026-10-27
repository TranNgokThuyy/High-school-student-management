@extends('layouts.app')
@section('title', 'Tổng hợp chuyên cần')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart text-primary me-2"></i>Tổng hợp chuyên cần</h4>
    <a href="{{ route('diem-danh.index', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Điểm danh
    </a>
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Lớp</label>
                <select name="lop_hoc_id" class="form-select">
                    @foreach($lopHoc as $l)
                        <option value="{{ $l->id }}" {{ $lop?->id == $l->id ? 'selected' : '' }}>{{ $l->ten_lop }} ({{ $l->nam_hoc }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Học kỳ</label>
                <select name="hoc_ky" class="form-select">
                    @foreach(['1' => 'Học kỳ 1', '2' => 'Học kỳ 2', 'Ca năm' => 'Cả năm'] as $v => $l)
                        <option value="{{ $v }}" {{ $hocKy == $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem</button>
            </div>
        </form>
    </div>
</div>

@if($lop)
<div class="card table-card">
    <div class="card-header bg-white py-3">
        <h6 class="fw-semibold mb-0">
            Lớp {{ $lop->ten_lop }} — {{ $khoang[0]->format('d/m/Y') }} đến {{ $khoang[1]->format('d/m/Y') }}
            <span class="badge bg-primary ms-2">{{ $tongHop['so_buoi'] }} buổi đã điểm danh</span>
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width:50px">STT</th>
                    <th>Họ và tên</th>
                    <th class="text-center text-warning">Vắng có phép</th>
                    <th class="text-center text-danger">Vắng không phép</th>
                    <th class="text-center">Tổng vắng</th>
                    <th class="text-center text-info">Đi trễ</th>
                    <th class="text-center">Tỷ lệ chuyên cần</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lop->hocSinh as $i => $hs)
                @php
                    $th = $tongHop['hoc_sinh'][$hs->id] ?? ['vang_co_phep' => 0, 'vang_khong_phep' => 0, 'di_tre' => 0];
                    $tongVang = $th['vang_co_phep'] + $th['vang_khong_phep'];
                    $tyLe = $tongHop['so_buoi'] ? round(($tongHop['so_buoi'] - $tongVang) / $tongHop['so_buoi'] * 100, 1) : null;
                @endphp
                <tr class="{{ $tongVang > 45 ? 'table-danger' : '' }}">
                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                    <td><a href="{{ route('hoc-sinh.show', $hs) }}" class="fw-semibold text-decoration-none">{{ $hs->ho_ten }}</a></td>
                    <td class="text-center">{{ $th['vang_co_phep'] ?: '—' }}</td>
                    <td class="text-center">{{ $th['vang_khong_phep'] ?: '—' }}</td>
                    <td class="text-center fw-semibold">{{ $tongVang }}</td>
                    <td class="text-center">{{ $th['di_tre'] ?: '—' }}</td>
                    <td class="text-center">
                        @if(!is_null($tyLe))
                            <span class="fw-semibold {{ $tyLe >= 80 ? 'text-success' : 'text-danger' }}">{{ $tyLe }}%</span>
                        @else — @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Lớp chưa có học sinh</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        Học sinh nghỉ quá 45 buổi/năm học (tô đỏ) không đủ điều kiện lên lớp theo Thông tư 22/2021/TT-BGDĐT.
    </div>
</div>
@endif
@endsection
