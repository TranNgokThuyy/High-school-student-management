@extends('layouts.app')
@section('title', 'Học phí & Công nợ')

@section('content')
@use('App\Support\Tien')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-cash-coin text-success me-2"></i>Học phí & Công nợ</h4>
    <div class="d-flex gap-2 flex-wrap">
        @can('thu-hoc-phi')
        <a href="{{ route('hoc-phi.so-thu') }}" class="btn btn-outline-primary"><i class="bi bi-journal-bookmark me-1"></i>Sổ thu</a>
        @endcan
        @can('quan-ly-khoan-thu')
        <a href="{{ route('khoan-thu.index') }}" class="btn btn-outline-secondary"><i class="bi bi-list-check me-1"></i>Khoản thu</a>
        <a href="{{ route('hoc-phi.mien-giam') }}" class="btn btn-outline-secondary"><i class="bi bi-percent me-1"></i>Miễn giảm</a>
        @endcan
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Lớp</label>
                <select name="lop_hoc_id" class="form-select" onchange="this.form.khoan_thu_id.value=''; this.form.submit()">
                    @foreach($lopHoc as $l)
                        <option value="{{ $l->id }}" {{ $lop?->id == $l->id ? 'selected' : '' }}>{{ $l->ten_lop }} ({{ $l->nam_hoc }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Khoản thu</label>
                <select name="khoan_thu_id" class="form-select">
                    <option value="">-- Tất cả khoản thu --</option>
                    @foreach($dsKhoanThu as $kt)
                        <option value="{{ $kt->id }}" {{ request('khoan_thu_id') == $kt->id ? 'selected' : '' }}>{{ $kt->ten }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem</button>
            </div>
            @if($lop)
            <div class="col-md-2">
                <a href="{{ route('hoc-phi.xuat-excel', ['lop_hoc_id' => $lop->id, 'khoan_thu_id' => request('khoan_thu_id')]) }}" class="btn btn-success w-100">
                    <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

@if(!$lop)
    <div class="alert alert-warning">Không có lớp nào để xem.</div>
@elseif($congNo['khoan_thu']->isEmpty())
    <div class="alert alert-info">
        Chưa có khoản thu nào áp dụng cho lớp {{ $lop->ten_lop }} (năm học {{ $lop->nam_hoc }}).
        @can('quan-ly-khoan-thu') <a href="{{ route('khoan-thu.create') }}">Thêm khoản thu</a> @endcan
    </div>
@else
<div class="row g-3 mb-4">
    @foreach([['Phải nộp', 'phai_nop', 'primary'], ['Đã thu', 'da_nop', 'success'], ['Còn nợ', 'con_no', 'danger']] as [$nhan, $khoa, $mau])
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="text-muted small">{{ $nhan }}</div>
            <div class="fs-5 fw-bold text-{{ $mau }}">{{ Tien::dinhDang($congNo['tong'][$khoa]) }}</div>
        </div></div>
    </div>
    @endforeach
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="text-muted small">Học sinh còn nợ</div>
            <div class="fs-5 fw-bold">{{ $congNo['tong']['so_hs_no'] }} / {{ $congNo['hoc_sinh']->count() }}</div>
        </div></div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white py-3">
        <h6 class="fw-semibold mb-0">Công nợ lớp {{ $lop->ten_lop }} — năm học {{ $lop->nam_hoc }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle small">
            <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th>Họ và tên</th>
                    @foreach($congNo['khoan_thu'] as $kt)
                        <th class="text-end" title="{{ Tien::dinhDang($kt->so_tien) }}">{{ $kt->ten }}</th>
                    @endforeach
                    <th class="text-end">Đã nộp</th>
                    <th class="text-end">Còn nợ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($congNo['hoc_sinh'] as $i => $r)
                <tr>
                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                    <td>
                        <span class="fw-semibold">{{ $r['hoc_sinh']->ho_ten }}</span>
                        @if($r['mien_giam'])
                            <span class="badge bg-info-subtle text-info border" title="{{ $r['mien_giam']->doi_tuong }}">-{{ $r['mien_giam']->phan_tram }}%</span>
                        @endif
                    </td>
                    @foreach($congNo['khoan_thu'] as $kt)
                        @php $ct = $r['chi_tiet'][$kt->id]; @endphp
                        <td class="text-end">
                            @if($ct['phai_nop'] === 0)
                                <span class="text-muted">Miễn</span>
                            @elseif($ct['con_no'] === 0)
                                <i class="bi bi-check-circle-fill text-success" title="Đã nộp đủ"></i>
                            @else
                                <span class="{{ $ct['da_nop'] > 0 ? 'text-warning' : 'text-danger' }}">{{ number_format($ct['con_no'], 0, ',', '.') }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-end">{{ number_format($r['da_nop'], 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold {{ $r['con_no'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($r['con_no'], 0, ',', '.') }}</td>
                    <td class="text-end">
                        <a href="{{ route('hoc-phi.hoc-sinh', $r['hoc_sinh']) }}" class="btn btn-sm {{ $r['con_no'] > 0 ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $r['con_no'] > 0 && auth()->user()->can('thu-hoc-phi') ? 'Thu tiền' : 'Chi tiết' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $congNo['khoan_thu']->count() + 5 }}" class="text-center text-muted py-4">Lớp chưa có học sinh đang học</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        Các cột khoản thu hiển thị số <b>còn phải nộp</b> (đơn vị: đồng). <i class="bi bi-check-circle-fill text-success"></i> = đã nộp đủ.
    </div>
</div>
@endif
@endsection
