@extends('layouts.app')
@section('title', 'Học phí - ' . $hocSinh->ho_ten)

@section('content')
@use('App\Support\Tien')
@php $conNo = $congNo->where('con_no', '>', 0); @endphp
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">{{ $hocSinh->ho_ten }}</h4>
        <div class="text-muted small">
            {{ $hocSinh->ma_hoc_sinh }} · Lớp {{ $lop?->ten_lop ?? '—' }} ({{ $lop?->nam_hoc }})
            @if($mienGiam) · <span class="badge bg-info">Miễn giảm {{ $mienGiam->phan_tram }}% — {{ $mienGiam->doi_tuong }}</span> @endif
        </div>
    </div>
    <a href="{{ route('hoc-phi.index', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Công nợ lớp</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif
@if(session('phieu_moi'))
<div class="alert alert-success d-flex justify-content-between align-items-center">
    <span>Đã lập phiếu thu.</span>
    <a href="{{ route('hoc-phi.in-phieu', session('phieu_moi')) }}" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-printer me-1"></i>In phiếu thu</a>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card table-card mb-4">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Các khoản phải nộp</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead><tr><th>Khoản thu</th><th>Hạn nộp</th><th class="text-end">Phải nộp</th><th class="text-end">Đã nộp</th><th class="text-end">Còn nợ</th></tr></thead>
                    <tbody>
                        @forelse($congNo as $d)
                        <tr>
                            <td>{{ $d['khoan_thu']->ten }} <div class="small text-muted">{{ $d['khoan_thu']->loai }}{{ $d['khoan_thu']->hoc_ky !== 'Ca năm' ? ' · HK' . $d['khoan_thu']->hoc_ky : '' }}</div></td>
                            <td class="small {{ $d['con_no'] > 0 && $d['khoan_thu']->han_nop?->isPast() ? 'text-danger fw-semibold' : '' }}">{{ $d['khoan_thu']->han_nop?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-end">{{ Tien::dinhDang($d['phai_nop']) }}</td>
                            <td class="text-end text-success">{{ Tien::dinhDang($d['da_nop']) }}</td>
                            <td class="text-end fw-semibold {{ $d['con_no'] > 0 ? 'text-danger' : 'text-success' }}">{{ Tien::dinhDang($d['con_no']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Chưa có khoản thu áp dụng</td></tr>
                        @endforelse
                    </tbody>
                    @if($congNo->isNotEmpty())
                    <tfoot class="fw-bold">
                        <tr><td colspan="2">Tổng</td>
                            <td class="text-end">{{ Tien::dinhDang($congNo->sum('phai_nop')) }}</td>
                            <td class="text-end">{{ Tien::dinhDang($congNo->sum('da_nop')) }}</td>
                            <td class="text-end text-danger">{{ Tien::dinhDang($congNo->sum('con_no')) }}</td></tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Lịch sử phiếu thu</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle small">
                    <thead><tr><th>Số phiếu</th><th>Ngày</th><th>Khoản thu</th><th class="text-end">Số tiền</th><th>Hình thức</th><th></th></tr></thead>
                    <tbody>
                        @forelse($phieuThu as $p)
                        <tr class="{{ $p->da_huy ? 'text-decoration-line-through text-muted' : '' }}">
                            <td class="fw-semibold">{{ $p->so_phieu }}</td>
                            <td>{{ $p->ngay_thu->format('d/m/Y') }}</td>
                            <td>{{ $p->khoanThu->ten }}</td>
                            <td class="text-end">{{ Tien::dinhDang($p->so_tien) }}</td>
                            <td>{{ $p->hinh_thuc }}</td>
                            <td class="text-end text-nowrap" style="text-decoration:none">
                                @if($p->da_huy)
                                    <span class="badge bg-secondary" title="{{ $p->ly_do_huy }}">Đã hủy</span>
                                @else
                                    <a href="{{ route('hoc-phi.in-phieu', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="In phiếu"><i class="bi bi-printer"></i></a>
                                    @can('huy-phieu-thu')
                                    <form method="POST" action="{{ route('hoc-phi.huy-phieu', $p) }}" class="d-inline"
                                          onsubmit="const l = prompt('Lý do hủy phiếu {{ $p->so_phieu }}:'); if (!l) return false; this.ly_do_huy.value = l;">
                                        @csrf <input type="hidden" name="ly_do_huy">
                                        <button class="btn btn-sm btn-outline-danger" title="Hủy phiếu"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Chưa có phiếu thu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('thu-hoc-phi')
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-cash-stack me-2 text-success"></i>Lập phiếu thu</h6></div>
            @if($conNo->isEmpty())
                <div class="card-body text-center text-success py-4"><i class="bi bi-check-circle d-block fs-2 mb-2"></i>Học sinh đã nộp đủ các khoản</div>
            @else
            <form method="POST" action="{{ route('hoc-phi.thu', $hocSinh) }}" class="card-body">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Khoản thu</label>
                    <select name="khoan_thu_id" id="khoanThu" class="form-select" required onchange="document.getElementById('soTien').value = this.selectedOptions[0].dataset.conNo">
                        @foreach($conNo as $d)
                            <option value="{{ $d['khoan_thu']->id }}" data-con-no="{{ $d['con_no'] }}" {{ old('khoan_thu_id') == $d['khoan_thu']->id ? 'selected' : '' }}>
                                {{ $d['khoan_thu']->ten }} — còn {{ Tien::dinhDang($d['con_no']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Số tiền (đồng)</label>
                    <input type="number" name="so_tien" id="soTien" class="form-control" min="1000" step="1000" required
                           value="{{ old('so_tien', $conNo->first()['con_no']) }}">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Ngày thu</label>
                        <input type="date" name="ngay_thu" class="form-control" value="{{ old('ngay_thu', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Hình thức</label>
                        <select name="hinh_thuc" class="form-select">
                            @foreach(['Tiền mặt', 'Chuyển khoản'] as $ht)
                                <option {{ old('hinh_thuc') == $ht ? 'selected' : '' }}>{{ $ht }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Người nộp</label>
                    <input type="text" name="nguoi_nop" class="form-control" value="{{ old('nguoi_nop', $hocSinh->ho_ten_me ?? $hocSinh->ho_ten_cha) }}" maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <input type="text" name="ghi_chu" class="form-control" value="{{ old('ghi_chu') }}" maxlength="255" placeholder="VD: Mã giao dịch chuyển khoản">
                </div>
                <button class="btn btn-success w-100"><i class="bi bi-save me-2"></i>Lập phiếu thu</button>
            </form>
            @endif
        </div>
    </div>
    @endcan
</div>
@endsection
