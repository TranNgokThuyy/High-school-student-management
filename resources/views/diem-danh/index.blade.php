@extends('layouts.app')
@section('title', 'Điểm danh')

@push('styles')
<style>
.dd-radio .btn { min-width: 44px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check text-primary me-2"></i>Điểm danh hằng ngày</h4>
    <a href="{{ route('diem-danh.tong-hop', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-outline-primary">
        <i class="bi bi-bar-chart me-2"></i>Tổng hợp chuyên cần
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

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
                <label class="form-label">Ngày</label>
                <input type="date" name="ngay" class="form-control" value="{{ $ngay->toDateString() }}" max="{{ today()->toDateString() }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem</button>
            </div>
        </form>
    </div>
</div>

@if(!$lop)
    <div class="alert alert-warning">Bạn chưa được phân công chủ nhiệm lớp nào nên không có lớp để điểm danh.</div>
@else
<div class="row g-3 mb-4">
    @php $mau = ['Có mặt' => 'success', 'Vắng có phép' => 'warning', 'Vắng không phép' => 'danger', 'Đi trễ' => 'info']; @endphp
    @foreach($thongKe as $tt => $sl)
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body py-3">
                <div class="text-muted small">{{ $tt }}</div>
                <div class="fs-4 fw-bold text-{{ $mau[$tt] }}">{{ $sl }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<form method="POST" action="{{ route('diem-danh.store') }}">
    @csrf
    <input type="hidden" name="lop_hoc_id" value="{{ $lop->id }}">
    <input type="hidden" name="ngay" value="{{ $ngay->toDateString() }}">

    <div class="card table-card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-semibold mb-0">
                Lớp {{ $lop->ten_lop }} — {{ $ngay->format('d/m/Y') }}
                @if($diemDanh->isEmpty())
                    <span class="badge bg-secondary ms-2">Chưa điểm danh</span>
                @else
                    <span class="badge bg-success ms-2">Đã điểm danh</span>
                @endif
            </h6>
            @can('diem-danh')
            <button type="button" class="btn btn-outline-success btn-sm" onclick="tatCaCoMat()">
                <i class="bi bi-check-all me-1"></i>Tất cả có mặt
            </button>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle bang-co-dinh">
                <thead>
                    <tr>
                        <th style="width:50px" class="text-center">STT</th>
                        <th>Họ và tên</th>
                        <th class="text-center">Trạng thái</th>
                        <th style="min-width:200px">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lop->hocSinh as $i => $hs)
                    @php $dd = $diemDanh[$hs->id] ?? null; $hienTai = $dd?->trang_thai ?? 'Có mặt'; @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $hs->ho_ten }}</div>
                            <small class="text-muted">{{ $hs->ma_hoc_sinh }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm dd-radio" role="group">
                                @foreach(['Có mặt' => 'CM', 'Vắng có phép' => 'P', 'Vắng không phép' => 'K', 'Đi trễ' => 'T'] as $tt => $kyHieu)
                                <input type="radio" class="btn-check" name="diem_danh[{{ $hs->id }}][trang_thai]"
                                       id="dd{{ $hs->id }}{{ $loop->index }}" value="{{ $tt }}"
                                       {{ $hienTai === $tt ? 'checked' : '' }} @cannot('diem-danh') disabled @endcannot>
                                <label class="btn btn-outline-{{ $mau[$tt] }}" for="dd{{ $hs->id }}{{ $loop->index }}" title="{{ $tt }}">{{ $kyHieu }}</label>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <input type="text" name="diem_danh[{{ $hs->id }}][ghi_chu]" class="form-control form-control-sm"
                                   value="{{ $dd?->ghi_chu }}" placeholder="Lý do vắng..." @cannot('diem-danh') disabled @endcannot>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Lớp chưa có học sinh đang học</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">CM: Có mặt · P: Vắng có phép · K: Vắng không phép · T: Đi trễ</small>
            @can('diem-danh')
            <div class="d-flex align-items-center gap-3">
            @can('gui-tin-phu-huynh')
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="bao_phu_huynh" value="1" id="baoPhuHuynh" checked>
                <label class="form-check-label small" for="baoPhuHuynh">Báo phụ huynh HS vắng không phép (email, SMS, Zalo)</label>
            </div>
            @endcan
            <button class="btn btn-primary" {{ $lop->hocSinh->isEmpty() ? 'disabled' : '' }}>
                <i class="bi bi-save me-2"></i>Lưu điểm danh
            </button>
            </div>
            @endcan
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
function tatCaCoMat() {
    document.querySelectorAll('input[type=radio][value="Có mặt"]').forEach(r => r.checked = true);
}
</script>
@endpush
